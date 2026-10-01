<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\JobPosition;
use App\Models\Program;
use App\Models\RequestType;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBulkImportController extends Controller
{
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = '\\';

    private const ENTITY_USERS = 'users';
    private const ENTITY_ROLES = 'roles';
    private const ENTITY_REQUEST_TYPES = 'request_types';
    private const ENTITY_JOB_POSITIONS = 'job_positions';
    private const ENTITY_INSTITUTIONS = 'institutions';
    private const ENTITY_FACULTIES = 'faculties';
    private const ENTITY_AREAS = 'areas';
    private const ENTITY_PROGRAMS = 'programs';
    private const ENTITY_COURSES = 'courses';

    public function index()
    {
        $this->ensureAdminRole();

        return view('admin.bulk-import.index', [
            'entities' => $this->entityDefinitions(),
        ]);
    }

    public function downloadTemplate(string $entity): StreamedResponse
    {
        $this->ensureAdminRole();

        $entities = $this->entityDefinitions();

        if (!array_key_exists($entity, $entities)) {
            abort(404, 'Entidad no soportada para plantilla.');
        }

        $definition = $entities[$entity];
        $headers = explode(',', $definition['sample_headers']);
        $sampleRow = $definition['sample_row'];

        return response()->streamDownload(function () use ($headers, $sampleRow) {
            $output = fopen('php://output', 'wb');
            fputcsv($output, $headers);
            fputcsv($output, $sampleRow);
            fclose($output);
        }, $definition['template_file'], [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdminRole();

        $entities = $this->entityDefinitions();

        $validated = $request->validate([
            'entity' => ['required', Rule::in(array_keys($entities))],
            'action' => ['nullable', Rule::in(['preview', 'import'])],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $entity = $validated['entity'];
        $action = $validated['action'] ?? 'import';

        try {
            $parsed = $this->parseCsv($request->file('file'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        if (empty($parsed['rows'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El archivo no contiene filas de datos para importar.');
        }

        if ($action === 'preview') {
            $preview = $this->buildPreview($entity, $parsed['rows']);
            $errorCount = count($preview['errors']);
            $entityLabel = $entities[$entity]['label'];

            $message = "Previsualizacion de {$entityLabel}: {$preview['created']} se crearian, {$preview['updated']} se actualizarian";

            if ($preview['skipped'] > 0) {
                $message .= ", {$preview['skipped']} se omitirian";
            }

            if ($errorCount > 0) {
                $message .= ", {$errorCount} con error";
            }

            return redirect()->route('admin.bulk-import.index')
                ->with('success', $message . '.')
                ->with('import_preview', $preview);
        }

        $result = [
            'entity' => $entity,
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($parsed['rows'] as $index => $row) {
            $lineNumber = $index + 2;

            if ($this->isRowEmpty($row)) {
                continue;
            }

            $result['total']++;

            try {
                $action = DB::transaction(function () use ($entity, $row) {
                    return $this->importEntityRow($entity, $row);
                });

                if ($action === 'created') {
                    $result['created']++;
                } elseif ($action === 'updated') {
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }
            } catch (\Throwable $exception) {
                $result['errors'][] = [
                    'line' => $lineNumber,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $errorCount = count($result['errors']);

        if ($errorCount > 100) {
            $result['errors'] = array_slice($result['errors'], 0, 100);
        }

        $entityLabel = $entities[$entity]['label'];
        $message = "Carga masiva de {$entityLabel}: {$result['created']} creados, {$result['updated']} actualizados";

        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} omitidos";
        }

        if ($errorCount > 0) {
            $message .= ", {$errorCount} errores";
        }

        return redirect()->route('admin.bulk-import.index')
            ->with('success', $message . '.')
            ->with('import_summary', $result);
    }

    private function buildPreview(string $entity, array $rows): array
    {
        $result = [
            'entity' => $entity,
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'rows' => [],
        ];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;

            if ($this->isRowEmpty($row)) {
                continue;
            }

            $result['total']++;

            try {
                $operation = $this->previewEntityRow($entity, $row);

                if ($operation === 'created') {
                    $result['created']++;
                } elseif ($operation === 'updated') {
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }

                $result['rows'][] = [
                    'line' => $lineNumber,
                    'status' => 'ok',
                    'operation' => $operation,
                    'message' => 'Validacion correcta.',
                ];
            } catch (\Throwable $exception) {
                $result['errors'][] = [
                    'line' => $lineNumber,
                    'message' => $exception->getMessage(),
                ];

                $result['rows'][] = [
                    'line' => $lineNumber,
                    'status' => 'error',
                    'operation' => 'error',
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return $result;
    }

    private function previewEntityRow(string $entity, array $row): string
    {
        if ($entity === self::ENTITY_USERS) {
            return $this->previewUserRow($row);
        }

        if ($entity === self::ENTITY_ROLES) {
            return $this->previewRoleRow($row);
        }

        if ($entity === self::ENTITY_REQUEST_TYPES) {
            return $this->previewRequestTypeRow($row);
        }

        if ($entity === self::ENTITY_JOB_POSITIONS) {
            return $this->previewJobPositionRow($row);
        }

        if ($entity === self::ENTITY_INSTITUTIONS) {
            return $this->previewInstitutionRow($row);
        }

        if ($entity === self::ENTITY_FACULTIES) {
            return $this->previewFacultyRow($row);
        }

        if ($entity === self::ENTITY_AREAS) {
            return $this->previewAreaRow($row);
        }

        if ($entity === self::ENTITY_PROGRAMS) {
            return $this->previewProgramRow($row);
        }

        if ($entity === self::ENTITY_COURSES) {
            return $this->previewCourseRow($row);
        }

        throw new InvalidArgumentException('Entidad de importacion no soportada.');
    }

    private function importEntityRow(string $entity, array $row): string
    {
        if ($entity === self::ENTITY_USERS) {
            return $this->importUserRow($row);
        }

        if ($entity === self::ENTITY_ROLES) {
            return $this->importRoleRow($row);
        }

        if ($entity === self::ENTITY_REQUEST_TYPES) {
            return $this->importRequestTypeRow($row);
        }

        if ($entity === self::ENTITY_JOB_POSITIONS) {
            return $this->importJobPositionRow($row);
        }

        if ($entity === self::ENTITY_INSTITUTIONS) {
            return $this->importInstitutionRow($row);
        }

        if ($entity === self::ENTITY_FACULTIES) {
            return $this->importFacultyRow($row);
        }

        if ($entity === self::ENTITY_AREAS) {
            return $this->importAreaRow($row);
        }

        if ($entity === self::ENTITY_PROGRAMS) {
            return $this->importProgramRow($row);
        }

        if ($entity === self::ENTITY_COURSES) {
            return $this->importCourseRow($row);
        }

        throw new InvalidArgumentException('Entidad de importacion no soportada.');
    }

    private function importUserRow(array $row): string
    {
        $email = $this->requiredValue($row, 'user_email');
        $name = $this->requiredValue($row, 'user_name');
        $role = $this->resolveRole($row);

        $areaId = $this->resolveAreaId($row);

        if ($this->roleRequiresArea($role) && !$areaId) {
            throw new InvalidArgumentException('El rol seleccionado requiere area obligatoria (area_name o area_id).');
        }

        $isActive = $this->parseBoolean($row['is_active'] ?? null);
        $user = User::whereRaw('LOWER(user_email) = ?', [mb_strtolower($email, 'UTF-8')])->first();

        $payload = [
            'user_name' => $name,
            'user_email' => $email,
            'role_id' => $role->role_id,
            'area_id' => $areaId,
            'document_number' => $this->nullableValue($row, 'document_number'),
            'institution_link' => $this->nullableValue($row, 'institution_link'),
            'user_phone' => $this->nullableValue($row, 'user_phone'),
            'user_bio' => $this->nullableValue($row, 'user_bio'),
        ];

        if ($isActive !== null) {
            $payload['is_active'] = $isActive;
        }

        $password = $this->nullableValue($row, 'password');

        if (!$user && !$password) {
            throw new InvalidArgumentException('Para crear usuarios nuevos debe indicar password (minimo 8 caracteres).');
        }

        if ($password !== null) {
            if (mb_strlen($password, 'UTF-8') < 8) {
                throw new InvalidArgumentException('El password debe tener al menos 8 caracteres.');
            }

            $payload['password'] = Hash::make($password);
        }

        if ($user) {
            $user->update($payload);
            $this->syncUserJobPositions($user, $row);
            return 'updated';
        }

        if (!array_key_exists('is_active', $payload)) {
            $payload['is_active'] = true;
        }

        $user = User::create($payload);
        $this->syncUserJobPositions($user, $row);

        return 'created';
    }

    private function importRoleRow(array $row): string
    {
        $roleId = $this->nullableValue($row, 'role_id');
        $roleName = $this->nullableValue($row, 'role_name');

        if (!$roleId && !$roleName) {
            throw new InvalidArgumentException('Debe indicar role_id o role_name.');
        }

        $role = null;

        if ($roleId) {
            $role = UserRole::find((int) $roleId);
        }

        if (!$role && $roleName) {
            $role = UserRole::whereRaw('LOWER(role_name) = ?', [mb_strtolower($roleName, 'UTF-8')])->first();
        }

        $color = $this->nullableValue($row, 'role_color');

        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException('role_color debe tener formato hexadecimal #RRGGBB.');
        }

        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($role) {
            $payload = [];

            if ($roleName !== null) {
                $payload['role_name'] = $roleName;
            }

            if (array_key_exists('role_description', $row)) {
                $payload['role_description'] = $this->nullableValue($row, 'role_description');
            }

            if ($color !== null) {
                $payload['role_color'] = $color;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $role->update($payload);
            return 'updated';
        }

        if ($roleName === null) {
            throw new InvalidArgumentException('No se puede crear rol sin role_name.');
        }

        UserRole::create([
            'role_name' => $roleName,
            'role_description' => $this->nullableValue($row, 'role_description'),
            'role_color' => $color ?? '#6c757d',
            'is_active' => $isActive ?? true,
        ]);

        return 'created';
    }

    private function importRequestTypeRow(array $row): string
    {
        $typeId = $this->nullableValue($row, 'type_id');
        $typeName = $this->nullableValue($row, 'type_name');

        if (!$typeId && !$typeName) {
            throw new InvalidArgumentException('Debe indicar type_id o type_name.');
        }

        $requestType = null;

        if ($typeId) {
            $requestType = RequestType::find((int) $typeId);
        }

        if (!$requestType && $typeName) {
            $requestType = RequestType::whereRaw('LOWER(type_name) = ?', [mb_strtolower($typeName, 'UTF-8')])->first();
        }

        $collaboratorIds = $this->resolveRequestTypeCollaboratorIds($row);
        $areaId = $this->resolveAreaId($row);
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        $color = $this->nullableValue($row, 'type_color');
        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException('type_color debe tener formato hexadecimal #RRGGBB.');
        }

        if ($requestType) {
            $payload = [];

            if ($typeName !== null) {
                $payload['type_name'] = $typeName;
            }

            if (array_key_exists('type_description', $row)) {
                $payload['type_description'] = $this->nullableValue($row, 'type_description');
            }

            if (array_key_exists('type_icon', $row)) {
                $payload['type_icon'] = $this->nullableValue($row, 'type_icon');
            }

            if ($color !== null) {
                $payload['type_color'] = $color;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if ($areaId !== null || array_key_exists('area_id', $row) || array_key_exists('area_name', $row)) {
                $payload['area_id'] = $areaId;
            }

            if (!empty($collaboratorIds)) {
                $payload['gestor_id'] = $collaboratorIds[0];
            }

            if (!empty($payload)) {
                $requestType->update($payload);
            }

            if (!empty($collaboratorIds) && $this->supportsCollaboratorAssignments()) {
                $requestType->collaborators()->sync($collaboratorIds);
            }

            return empty($payload) && empty($collaboratorIds) ? 'skipped' : 'updated';
        }

        if ($typeName === null) {
            throw new InvalidArgumentException('No se puede crear topico sin type_name.');
        }

        if (empty($collaboratorIds)) {
            throw new InvalidArgumentException('Debe indicar collaborator_emails, collaborator_ids, gestor_email o gestor_id.');
        }

        $requestType = RequestType::create([
            'type_name' => $typeName,
            'type_description' => $this->nullableValue($row, 'type_description'),
            'type_icon' => $this->nullableValue($row, 'type_icon'),
            'type_color' => $color ?? '#6c757d',
            'is_active' => $isActive ?? true,
            'area_id' => $areaId,
            'gestor_id' => $collaboratorIds[0],
        ]);

        if ($this->supportsCollaboratorAssignments()) {
            $requestType->collaborators()->sync($collaboratorIds);
        }

        return 'created';
    }

    private function previewUserRow(array $row): string
    {
        $email = $this->requiredValue($row, 'user_email');
        $this->requiredValue($row, 'user_name');
        $role = $this->resolveRole($row);
        $areaId = $this->resolveAreaId($row);

        if ($this->roleRequiresArea($role) && !$areaId) {
            throw new InvalidArgumentException('El rol seleccionado requiere area obligatoria (area_name o area_id).');
        }

        $this->parseBoolean($row['is_active'] ?? null);

        $password = $this->nullableValue($row, 'password');
        $user = User::whereRaw('LOWER(user_email) = ?', [mb_strtolower($email, 'UTF-8')])->first();

        if (!$user && !$password) {
            throw new InvalidArgumentException('Para crear usuarios nuevos debe indicar password (minimo 8 caracteres).');
        }

        if ($password !== null && mb_strlen($password, 'UTF-8') < 8) {
            throw new InvalidArgumentException('El password debe tener al menos 8 caracteres.');
        }

        $this->validateUserJobPositions($row);

        return $user ? 'updated' : 'created';
    }

    private function previewRoleRow(array $row): string
    {
        $roleId = $this->nullableValue($row, 'role_id');
        $roleName = $this->nullableValue($row, 'role_name');

        if (!$roleId && !$roleName) {
            throw new InvalidArgumentException('Debe indicar role_id o role_name.');
        }

        $role = null;

        if ($roleId) {
            $role = UserRole::find((int) $roleId);
        }

        if (!$role && $roleName) {
            $role = UserRole::whereRaw('LOWER(role_name) = ?', [mb_strtolower($roleName, 'UTF-8')])->first();
        }

        $color = $this->nullableValue($row, 'role_color');

        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException('role_color debe tener formato hexadecimal #RRGGBB.');
        }

        $this->parseBoolean($row['is_active'] ?? null);

        if ($role) {
            $hasPayload =
                $roleName !== null ||
                array_key_exists('role_description', $row) ||
                $color !== null ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($roleName === null) {
            throw new InvalidArgumentException('No se puede crear rol sin role_name.');
        }

        return 'created';
    }

    private function previewRequestTypeRow(array $row): string
    {
        $typeId = $this->nullableValue($row, 'type_id');
        $typeName = $this->nullableValue($row, 'type_name');

        if (!$typeId && !$typeName) {
            throw new InvalidArgumentException('Debe indicar type_id o type_name.');
        }

        $requestType = null;

        if ($typeId) {
            $requestType = RequestType::find((int) $typeId);
        }

        if (!$requestType && $typeName) {
            $requestType = RequestType::whereRaw('LOWER(type_name) = ?', [mb_strtolower($typeName, 'UTF-8')])->first();
        }

        $collaboratorIds = $this->resolveRequestTypeCollaboratorIds($row);
        $areaId = $this->resolveAreaId($row);
        $this->parseBoolean($row['is_active'] ?? null);

        $color = $this->nullableValue($row, 'type_color');
        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException('type_color debe tener formato hexadecimal #RRGGBB.');
        }

        if ($requestType) {
            $hasPayload =
                $typeName !== null ||
                array_key_exists('type_description', $row) ||
                array_key_exists('type_icon', $row) ||
                $color !== null ||
                ($this->nullableValue($row, 'is_active') !== null) ||
                $areaId !== null ||
                array_key_exists('area_id', $row) ||
                array_key_exists('area_name', $row) ||
                !empty($collaboratorIds);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($typeName === null) {
            throw new InvalidArgumentException('No se puede crear topico sin type_name.');
        }

        if (empty($collaboratorIds)) {
            throw new InvalidArgumentException('Debe indicar collaborator_emails, collaborator_ids, gestor_email o gestor_id.');
        }

        return 'created';
    }

    private function importJobPositionRow(array $row): string
    {
        $entity = $this->resolveJobPositionEntity($row);
        $name = $this->nullableValue($row, 'position_name');
        $description = $this->nullableValue($row, 'position_description');
        $color = $this->nullableValue($row, 'position_color');
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException('position_color debe tener formato hexadecimal #RRGGBB.');
        }

        if ($entity) {
            $payload = [];

            if ($name !== null) {
                $payload['position_name'] = $name;
            }

            if (array_key_exists('position_description', $row)) {
                $payload['position_description'] = $description;
            }

            if ($color !== null) {
                $payload['position_color'] = $color;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $entity->update($payload);
            return 'updated';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar position_name para crear un puesto de trabajo.');
        }

        JobPosition::create([
            'position_name' => $name,
            'position_description' => $description,
            'position_color' => $color,
            'is_active' => $isActive ?? true,
        ]);

        return 'created';
    }

    private function importInstitutionRow(array $row): string
    {
        $entity = $this->resolveInstitutionEntity($row);
        $name = $this->nullableValue($row, 'institution_name');
        $description = $this->nullableValue($row, 'institution_description');
        $logo = $this->nullableValue($row, 'institution_logo');
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $payload = [];

            if ($name !== null) {
                $payload['institution_name'] = $name;
            }

            if (array_key_exists('institution_description', $row)) {
                $payload['institution_description'] = $description;
            }

            if (array_key_exists('institution_logo', $row)) {
                $payload['institution_logo'] = $logo;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $entity->update($payload);
            return 'updated';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar institution_name para crear una institucion.');
        }

        Institution::create([
            'institution_name' => $name,
            'institution_description' => $description,
            'institution_logo' => $logo,
            'is_active' => $isActive ?? true,
        ]);

        return 'created';
    }

    private function importFacultyRow(array $row): string
    {
        $entity = $this->resolveFacultyEntity($row);
        $name = $this->nullableValue($row, 'faculty_name');
        $description = $this->nullableValue($row, 'faculty_description');
        $institutionId = $this->resolveInstitutionId($row, true);
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $payload = [];

            if ($name !== null) {
                $payload['faculty_name'] = $name;
            }

            if (array_key_exists('faculty_description', $row)) {
                $payload['faculty_description'] = $description;
            }

            if ($institutionId !== null || array_key_exists('institution_id', $row) || array_key_exists('institution_name', $row)) {
                $payload['institution_id'] = $institutionId;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $entity->update($payload);
            return 'updated';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar faculty_name para crear una facultad.');
        }

        Faculty::create([
            'faculty_name' => $name,
            'faculty_description' => $description,
            'institution_id' => $institutionId,
            'is_active' => $isActive ?? true,
        ]);

        return 'created';
    }

    private function importAreaRow(array $row): string
    {
        $entity = $this->resolveAreaEntity($row);
        $name = $this->nullableValue($row, 'area_name');
        $description = $this->nullableValue($row, 'area_description');
        $facultyId = $this->resolveFacultyId($row, true);
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $payload = [];

            if ($name !== null) {
                $payload['area_name'] = $name;
            }

            if (array_key_exists('area_description', $row)) {
                $payload['area_description'] = $description;
            }

            if ($facultyId !== null || array_key_exists('faculty_id', $row) || array_key_exists('faculty_name', $row)) {
                $payload['faculty_id'] = $facultyId;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $entity->update($payload);
            return 'updated';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar area_name para crear un area.');
        }

        Area::create([
            'area_name' => $name,
            'area_description' => $description,
            'faculty_id' => $facultyId,
            'is_active' => $isActive ?? true,
        ]);

        return 'created';
    }

    private function importProgramRow(array $row): string
    {
        $entity = $this->resolveProgramEntity($row);
        $name = $this->nullableValue($row, 'program_name');
        $code = $this->nullableValue($row, 'program_code');
        $description = $this->nullableValue($row, 'program_description');
        $facultyId = $this->resolveFacultyId($row, false);
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $payload = [];

            if ($name !== null) {
                $payload['program_name'] = $name;
            }

            if (array_key_exists('program_code', $row)) {
                $payload['program_code'] = $code;
            }

            if (array_key_exists('program_description', $row)) {
                $payload['program_description'] = $description;
            }

            if ($facultyId !== null || array_key_exists('faculty_id', $row) || array_key_exists('faculty_name', $row)) {
                $payload['faculty_id'] = $facultyId;
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $entity->update($payload);
            return 'updated';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar program_name para crear un programa.');
        }

        if ($facultyId === null) {
            throw new InvalidArgumentException('Debe indicar faculty_id o faculty_name para crear un programa.');
        }

        Program::create([
            'program_name' => $name,
            'program_code' => $code,
            'program_description' => $description,
            'faculty_id' => $facultyId,
            'is_active' => $isActive ?? true,
        ]);

        return 'created';
    }

    private function importCourseRow(array $row): string
    {
        $entity = $this->resolveCourseEntity($row);
        $name = $this->nullableValue($row, 'course_name');
        $code = $this->nullableValue($row, 'course_code');
        $description = $this->nullableValue($row, 'course_description');
        $credits = $this->nullableInteger($row, 'credits');
        $hasProgramReference = $this->hasAnyProgramReference($row);
        $programIds = $this->resolveProgramIds($row, !$hasProgramReference);
        $isActive = $this->parseBoolean($row['is_active'] ?? null);

        if ($credits !== null && ($credits < 1 || $credits > 10)) {
            throw new InvalidArgumentException('credits debe estar entre 1 y 10.');
        }

        if ($entity) {
            $payload = [];

            if ($name !== null) {
                $payload['course_name'] = $name;
            }

            if (array_key_exists('course_code', $row)) {
                $payload['course_code'] = $code;
            }

            if (array_key_exists('course_description', $row)) {
                $payload['course_description'] = $description;
            }

            if (array_key_exists('credits', $row)) {
                $payload['credits'] = $credits;
            }

            if (!empty($programIds)) {
                $payload['program_id'] = $programIds[0];
            }

            if ($isActive !== null) {
                $payload['is_active'] = $isActive;
            }

            if (empty($payload)) {
                return 'skipped';
            }

            $entity->update($payload);

            if ($hasProgramReference) {
                $entity->syncPrograms($programIds);
            }

            return 'updated';
        }

        if ($name === null || $code === null) {
            throw new InvalidArgumentException('Para crear cursos debe indicar course_name y course_code.');
        }

        if (empty($programIds)) {
            throw new InvalidArgumentException('Debe indicar program_id/program_ids, program_code/program_codes o program_name/program_names para crear un curso.');
        }

        $course = Course::create([
            'course_name' => $name,
            'course_code' => $code,
            'course_description' => $description,
            'credits' => $credits,
            'program_id' => $programIds[0],
            'is_active' => $isActive ?? true,
        ]);

        $course->syncPrograms($programIds);

        return 'created';
    }

    private function previewJobPositionRow(array $row): string
    {
        $entity = $this->resolveJobPositionEntity($row);
        $name = $this->nullableValue($row, 'position_name');
        $color = $this->nullableValue($row, 'position_color');

        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException('position_color debe tener formato hexadecimal #RRGGBB.');
        }

        $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $hasPayload =
                $name !== null ||
                array_key_exists('position_description', $row) ||
                $color !== null ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar position_name para crear un puesto de trabajo.');
        }

        return 'created';
    }

    private function previewInstitutionRow(array $row): string
    {
        $entity = $this->resolveInstitutionEntity($row);
        $name = $this->nullableValue($row, 'institution_name');
        $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $hasPayload =
                $name !== null ||
                array_key_exists('institution_description', $row) ||
                array_key_exists('institution_logo', $row) ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar institution_name para crear una institucion.');
        }

        return 'created';
    }

    private function previewFacultyRow(array $row): string
    {
        $entity = $this->resolveFacultyEntity($row);
        $name = $this->nullableValue($row, 'faculty_name');
        $institutionId = $this->resolveInstitutionId($row, true);
        $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $hasPayload =
                $name !== null ||
                array_key_exists('faculty_description', $row) ||
                $institutionId !== null ||
                array_key_exists('institution_id', $row) ||
                array_key_exists('institution_name', $row) ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar faculty_name para crear una facultad.');
        }

        return 'created';
    }

    private function previewAreaRow(array $row): string
    {
        $entity = $this->resolveAreaEntity($row);
        $name = $this->nullableValue($row, 'area_name');
        $facultyId = $this->resolveFacultyId($row, true);
        $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $hasPayload =
                $name !== null ||
                array_key_exists('area_description', $row) ||
                $facultyId !== null ||
                array_key_exists('faculty_id', $row) ||
                array_key_exists('faculty_name', $row) ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar area_name para crear un area.');
        }

        return 'created';
    }

    private function previewProgramRow(array $row): string
    {
        $entity = $this->resolveProgramEntity($row);
        $name = $this->nullableValue($row, 'program_name');
        $facultyId = $this->resolveFacultyId($row, false);
        $this->parseBoolean($row['is_active'] ?? null);

        if ($entity) {
            $hasPayload =
                $name !== null ||
                array_key_exists('program_code', $row) ||
                array_key_exists('program_description', $row) ||
                $facultyId !== null ||
                array_key_exists('faculty_id', $row) ||
                array_key_exists('faculty_name', $row) ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($name === null) {
            throw new InvalidArgumentException('Debe indicar program_name para crear un programa.');
        }

        if ($facultyId === null) {
            throw new InvalidArgumentException('Debe indicar faculty_id o faculty_name para crear un programa.');
        }

        return 'created';
    }

    private function previewCourseRow(array $row): string
    {
        $entity = $this->resolveCourseEntity($row);
        $name = $this->nullableValue($row, 'course_name');
        $code = $this->nullableValue($row, 'course_code');
        $credits = $this->nullableInteger($row, 'credits');
        $hasProgramReference = $this->hasAnyProgramReference($row);
        $programIds = $this->resolveProgramIds($row, !$hasProgramReference);
        $this->parseBoolean($row['is_active'] ?? null);

        if ($credits !== null && ($credits < 1 || $credits > 10)) {
            throw new InvalidArgumentException('credits debe estar entre 1 y 10.');
        }

        if ($entity) {
            $hasPayload =
                $name !== null ||
                array_key_exists('course_code', $row) ||
                array_key_exists('course_description', $row) ||
                array_key_exists('credits', $row) ||
                !empty($programIds) ||
                $hasProgramReference ||
                ($this->nullableValue($row, 'is_active') !== null);

            return $hasPayload ? 'updated' : 'skipped';
        }

        if ($name === null || $code === null) {
            throw new InvalidArgumentException('Para crear cursos debe indicar course_name y course_code.');
        }

        if (empty($programIds)) {
            throw new InvalidArgumentException('Debe indicar program_id/program_ids, program_code/program_codes o program_name/program_names para crear un curso.');
        }

        return 'created';
    }

    private function parseCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();

        if (!$path || !is_readable($path)) {
            throw new InvalidArgumentException('No fue posible leer el archivo enviado.');
        }

        $handle = fopen($path, 'rb');

        if (!$handle) {
            throw new InvalidArgumentException('No fue posible abrir el archivo.');
        }

        $firstLine = fgets($handle);
        rewind($handle);

        if ($firstLine === false) {
            fclose($handle);
            throw new InvalidArgumentException('El archivo esta vacio.');
        }

        $delimiter = $this->detectDelimiter($firstLine);
        $headerRow = fgetcsv($handle, 0, $delimiter, self::CSV_ENCLOSURE, self::CSV_ESCAPE);

        if (!$headerRow) {
            fclose($handle);
            throw new InvalidArgumentException('No se encontraron encabezados en el archivo.');
        }

        $headers = [];
        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader($this->normalizeCsvValue((string) $header));

            if ($normalized === '') {
                fclose($handle);
                throw new InvalidArgumentException('Hay encabezados vacios en el archivo CSV.');
            }

            if (in_array($normalized, $headers, true)) {
                fclose($handle);
                throw new InvalidArgumentException('Hay encabezados repetidos en el archivo CSV.');
            }

            $headers[$index] = $normalized;
        }

        $rows = [];
        $rowIndex = 2;

        while (($values = fgetcsv($handle, 0, $delimiter, self::CSV_ENCLOSURE, self::CSV_ESCAPE)) !== false) {
            if ($values === [null] || $values === false) {
                $rowIndex++;
                continue;
            }

            if (count($values) !== count($headers)) {
                $recoveredValues = $this->tryRecoverCsvColumns($values, count($headers), $delimiter);

                if ($recoveredValues !== null) {
                    $values = $recoveredValues;
                }
            }

            if (count($values) !== count($headers)) {
                fclose($handle);

                $receivedColumns = count($values);
                $expectedColumns = count($headers);

                if ($receivedColumns > $expectedColumns) {
                    throw new InvalidArgumentException(
                        "La linea {$rowIndex} tiene {$receivedColumns} columnas y se esperaban {$expectedColumns}. " .
                        'Revise que los campos con comas (por ejemplo faculty_name o descripciones) esten entre comillas dobles (").'
                    );
                }

                throw new InvalidArgumentException(
                    "La linea {$rowIndex} tiene {$receivedColumns} columnas y se esperaban {$expectedColumns}. " .
                    '. Verifique que el archivo este en UTF-8 y que los textos con comas esten entre comillas dobles (").'
                );
            }

            $row = [];
            foreach ($headers as $index => $column) {
                $row[$column] = isset($values[$index])
                    ? $this->normalizeCsvValue((string) $values[$index])
                    : null;
            }

            $rows[] = $row;
            $rowIndex++;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function tryRecoverCsvColumns(array $values, int $expectedColumns, string $detectedDelimiter): ?array
    {
        if (count($values) !== 1) {
            return null;
        }

        $rawLine = (string) $values[0];
        $candidateDelimiters = array_values(array_unique([$detectedDelimiter, ',', ';', "\t"]));

        foreach ($candidateDelimiters as $candidateDelimiter) {
            $parsed = str_getcsv($rawLine, $candidateDelimiter, self::CSV_ENCLOSURE, self::CSV_ESCAPE);

            if (count($parsed) === $expectedColumns) {
                return $parsed;
            }
        }

        $trimmed = trim($rawLine);

        if (mb_strlen($trimmed, 'UTF-8') >= 2 && str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) {
            $unwrapped = mb_substr($trimmed, 1, mb_strlen($trimmed, 'UTF-8') - 2, 'UTF-8');
            $unwrapped = str_replace('""', '"', $unwrapped);

            foreach ($candidateDelimiters as $candidateDelimiter) {
                $parsed = str_getcsv($unwrapped, $candidateDelimiter, self::CSV_ENCLOSURE, self::CSV_ESCAPE);

                if (count($parsed) === $expectedColumns) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    private function detectDelimiter(string $firstLine): string
    {
        $candidates = [',', ';', "\t"];
        $selected = ',';
        $maxColumns = 0;

        foreach ($candidates as $candidate) {
            $columns = count(str_getcsv($firstLine, $candidate, self::CSV_ENCLOSURE, self::CSV_ESCAPE));

            if ($columns > $maxColumns) {
                $maxColumns = $columns;
                $selected = $candidate;
            }
        }

        return $selected;
    }

    private function normalizeCsvValue(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/u', '', $value) ?? $value;

        if (!mb_check_encoding($value, 'UTF-8')) {
            $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');

            if (is_string($converted)) {
                $value = $converted;
            }
        }

        return trim($value);
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = mb_strtolower(trim($header), 'UTF-8');
        $header = str_replace([' ', '-'], '_', $header);

        return preg_replace('/[^a-z0-9_]/', '', $header) ?? '';
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function requiredValue(array $row, string $key): string
    {
        $value = $this->nullableValue($row, $key);

        if ($value === null) {
            throw new InvalidArgumentException("Falta el campo obligatorio {$key}.");
        }

        return $value;
    }

    private function nullableValue(array $row, string $key): ?string
    {
        if (!array_key_exists($key, $row)) {
            return null;
        }

        $value = trim((string) $row[$key]);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function parseBoolean(?string $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(trim($value), 'UTF-8');

        if ($normalized === '') {
            return null;
        }

        $truthy = ['1', 'true', 'yes', 'si', 'activo', 'active'];
        $falsy = ['0', 'false', 'no', 'inactivo', 'inactive'];

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        throw new InvalidArgumentException('Valor booleano invalido en columna is_active.');
    }

    private function nullableInteger(array $row, string $key): ?int
    {
        $value = $this->nullableValue($row, $key);

        if ($value === null) {
            return null;
        }

        if (!preg_match('/^-?\d+$/', $value)) {
            throw new InvalidArgumentException("{$key} debe ser un numero entero.");
        }

        return (int) $value;
    }

    private function resolveJobPositionEntity(array $row): ?JobPosition
    {
        $id = $this->nullableInteger($row, 'job_position_id');
        if ($id !== null) {
            return JobPosition::find($id);
        }

        $name = $this->nullableValue($row, 'position_name');
        if ($name === null) {
            return null;
        }

        return JobPosition::whereRaw('LOWER(position_name) = ?', [mb_strtolower($name, 'UTF-8')])->first();
    }

    private function resolveInstitutionEntity(array $row): ?Institution
    {
        $id = $this->nullableInteger($row, 'institution_id');
        if ($id !== null) {
            return Institution::find($id);
        }

        $name = $this->nullableValue($row, 'institution_name');
        if ($name === null) {
            return null;
        }

        return Institution::whereRaw('LOWER(institution_name) = ?', [mb_strtolower($name, 'UTF-8')])->first();
    }

    private function resolveFacultyEntity(array $row): ?Faculty
    {
        $id = $this->nullableInteger($row, 'faculty_id');
        if ($id !== null) {
            return Faculty::find($id);
        }

        $name = $this->nullableValue($row, 'faculty_name');
        if ($name === null) {
            return null;
        }

        $query = Faculty::query()->whereRaw('LOWER(faculty_name) = ?', [mb_strtolower($name, 'UTF-8')]);
        $institutionId = $this->resolveInstitutionId($row, true);
        if ($institutionId !== null) {
            $query->where('institution_id', $institutionId);
        }

        return $this->resolveSingleOrNull($query->get(), 'Hay multiples facultades con el mismo nombre. Use faculty_id o institution_id/institution_name.');
    }

    private function resolveAreaEntity(array $row): ?Area
    {
        $id = $this->nullableInteger($row, 'area_id');
        if ($id !== null) {
            return Area::find($id);
        }

        $name = $this->nullableValue($row, 'area_name');
        if ($name === null) {
            return null;
        }

        $query = Area::query()->whereRaw('LOWER(area_name) = ?', [mb_strtolower($name, 'UTF-8')]);
        $facultyId = $this->resolveFacultyId($row, true);

        if ($facultyId !== null) {
            $query->where('faculty_id', $facultyId);
        }

        return $this->resolveSingleOrNull($query->get(), 'Hay multiples areas con el mismo nombre. Indique area_id o faculty_id/faculty_name para desambiguar.');
    }

    private function resolveProgramEntity(array $row): ?Program
    {
        $id = $this->nullableInteger($row, 'program_id');
        if ($id !== null) {
            return Program::find($id);
        }

        $facultyId = $this->resolveFacultyId($row, true);
        $institutionId = $this->resolveInstitutionId($row, true);

        $code = $this->nullableValue($row, 'program_code');
        if ($code !== null) {
            $query = Program::query()->whereRaw('LOWER(program_code) = ?', [mb_strtolower($code, 'UTF-8')]);

            if ($facultyId !== null) {
                $query->where('faculty_id', $facultyId);
            }

            if ($institutionId !== null) {
                $query->whereHas('faculty', function ($facultyQuery) use ($institutionId) {
                    $facultyQuery->where('institution_id', $institutionId);
                });
            }

            return $this->resolveSingleOrNull($query->get(), 'Hay multiples programas con el mismo codigo. Use program_id o agregue faculty_id/faculty_name/institution_id/institution_name para desambiguar.');
        }

        $name = $this->nullableValue($row, 'program_name');
        if ($name === null) {
            return null;
        }

        $query = Program::query()->whereRaw('LOWER(program_name) = ?', [mb_strtolower($name, 'UTF-8')]);

        if ($facultyId !== null) {
            $query->where('faculty_id', $facultyId);
        }

        if ($institutionId !== null) {
            $query->whereHas('faculty', function ($facultyQuery) use ($institutionId) {
                $facultyQuery->where('institution_id', $institutionId);
            });
        }

        return $this->resolveSingleOrNull($query->get(), 'Hay multiples programas con el mismo nombre. Use program_id o agregue faculty_id/faculty_name/institution_id/institution_name para desambiguar.');
    }

    private function resolveCourseEntity(array $row): ?Course
    {
        $id = $this->nullableInteger($row, 'course_id');
        if ($id !== null) {
            return Course::find($id);
        }

        $programIds = $this->resolveProgramIds($row, true);
        $hasPivotTable = Schema::hasTable('course_program');

        $code = $this->nullableValue($row, 'course_code');
        if ($code !== null) {
            $query = Course::query()->whereRaw('LOWER(course_code) = ?', [mb_strtolower($code, 'UTF-8')]);

            if (!empty($programIds)) {
                $query->where(function ($subQuery) use ($programIds, $hasPivotTable) {
                    $subQuery->whereIn('program_id', $programIds);

                    if ($hasPivotTable) {
                        $subQuery->orWhereHas('programs', function ($programQuery) use ($programIds) {
                            $programQuery->whereIn('programs.program_id', $programIds);
                        });
                    }
                });
            }

            return $this->resolveSingleOrNull($query->get(), 'Hay multiples cursos con el mismo codigo. Use course_id o program_id/program_ids/program_code/program_codes.');
        }

        $name = $this->nullableValue($row, 'course_name');
        if ($name === null) {
            return null;
        }

        $query = Course::query()->whereRaw('LOWER(course_name) = ?', [mb_strtolower($name, 'UTF-8')]);

        if (!empty($programIds)) {
            $query->where(function ($subQuery) use ($programIds, $hasPivotTable) {
                $subQuery->whereIn('program_id', $programIds);

                if ($hasPivotTable) {
                    $subQuery->orWhereHas('programs', function ($programQuery) use ($programIds) {
                        $programQuery->whereIn('programs.program_id', $programIds);
                    });
                }
            });
        }

        return $this->resolveSingleOrNull($query->get(), 'Hay multiples cursos con el mismo nombre. Use course_id o program_id/program_ids/program_code/program_codes.');
    }

    private function resolveFacultyId(array $row, bool $optional): ?int
    {
        $facultyId = $this->nullableInteger($row, 'faculty_id');

        if ($facultyId !== null) {
            if (!Faculty::where('faculty_id', $facultyId)->exists()) {
                throw new InvalidArgumentException('No existe faculty_id especificado.');
            }

            return $facultyId;
        }

        $facultyName = $this->nullableValue($row, 'faculty_name');
        if ($facultyName === null) {
            if ($optional) {
                return null;
            }

            throw new InvalidArgumentException('Debe indicar faculty_id o faculty_name.');
        }

        $query = Faculty::query()->whereRaw('LOWER(faculty_name) = ?', [mb_strtolower($facultyName, 'UTF-8')]);
        $institutionId = $this->resolveInstitutionId($row, true);

        if ($institutionId !== null) {
            $query->where('institution_id', $institutionId);
        }

        $faculty = $this->resolveSingleOrNull($query->get(), 'Hay multiples facultades con el mismo nombre. Use faculty_id o institution_id/institution_name.');

        if (!$faculty && !$optional) {
            throw new InvalidArgumentException('No se encontro la facultad indicada.');
        }

        return $faculty ? (int) $faculty->faculty_id : null;
    }

    private function resolveProgramId(array $row, bool $optional): ?int
    {
        $programIds = $this->resolveProgramIds($row, $optional);

        return !empty($programIds) ? $programIds[0] : null;
    }

    private function resolveProgramIds(array $row, bool $optional): array
    {
        $resolvedIds = [];
        $facultyId = $this->resolveFacultyId($row, true);
        $institutionId = $this->resolveInstitutionId($row, true);

        $singleProgramId = $this->nullableInteger($row, 'program_id');
        if ($singleProgramId !== null) {
            if (!Program::where('program_id', $singleProgramId)->exists()) {
                throw new InvalidArgumentException('No existe program_id especificado.');
            }

            $resolvedIds[] = $singleProgramId;
        }

        foreach ($this->parseIntegerList($row, 'program_ids') as $programId) {
            if (!Program::where('program_id', $programId)->exists()) {
                throw new InvalidArgumentException("No existe program_id {$programId} en program_ids.");
            }

            $resolvedIds[] = $programId;
        }

        $programCodes = [];
        $singleProgramCode = $this->nullableValue($row, 'program_code');
        if ($singleProgramCode !== null) {
            $programCodes[] = $singleProgramCode;
        }

        $programCodes = array_merge($programCodes, $this->parseStringList($row, 'program_codes'));

        foreach ($programCodes as $programCode) {
            $query = Program::query()->whereRaw('LOWER(program_code) = ?', [mb_strtolower($programCode, 'UTF-8')]);

            if ($facultyId !== null) {
                $query->where('faculty_id', $facultyId);
            }

            if ($institutionId !== null) {
                $query->whereHas('faculty', function ($facultyQuery) use ($institutionId) {
                    $facultyQuery->where('institution_id', $institutionId);
                });
            }

            $matches = $query->get();

            if ($matches->isEmpty()) {
                throw new InvalidArgumentException("No se encontro el programa con program_code {$programCode}.");
            }

            if ($matches->count() > 1 && $facultyId === null && $institutionId === null) {
                foreach ($matches as $match) {
                    $resolvedIds[] = (int) $match->program_id;
                }

                continue;
            }

            $program = $this->resolveSingleOrNull($matches, 'Hay multiples programas con el mismo codigo. Use program_id o agregue faculty_id/faculty_name/institution_id/institution_name para desambiguar.');

            if ($program) {
                $resolvedIds[] = (int) $program->program_id;
            }
        }

        $programNames = [];
        $singleProgramName = $this->nullableValue($row, 'program_name');
        if ($singleProgramName !== null) {
            $programNames[] = $singleProgramName;
        }

        $programNames = array_merge($programNames, $this->parseStringList($row, 'program_names'));

        foreach ($programNames as $programName) {
            $query = Program::query()->whereRaw('LOWER(program_name) = ?', [mb_strtolower($programName, 'UTF-8')]);

            if ($facultyId !== null) {
                $query->where('faculty_id', $facultyId);
            }

            if ($institutionId !== null) {
                $query->whereHas('faculty', function ($facultyQuery) use ($institutionId) {
                    $facultyQuery->where('institution_id', $institutionId);
                });
            }

            $matches = $query->get();

            if ($matches->isEmpty()) {
                throw new InvalidArgumentException("No se encontro el programa con program_name {$programName}.");
            }

            if ($matches->count() > 1 && $facultyId === null && $institutionId === null) {
                foreach ($matches as $match) {
                    $resolvedIds[] = (int) $match->program_id;
                }

                continue;
            }

            $program = $this->resolveSingleOrNull($matches, 'Hay multiples programas con el mismo nombre. Use program_id o agregue faculty_id/faculty_name/institution_id/institution_name para desambiguar.');

            if ($program) {
                $resolvedIds[] = (int) $program->program_id;
            }
        }

        $resolvedIds = array_values(array_unique($resolvedIds));

        if (empty($resolvedIds) && !$optional) {
            throw new InvalidArgumentException('Debe indicar program_id/program_ids, program_code/program_codes o program_name/program_names.');
        }

        return $resolvedIds;
    }

    private function hasAnyProgramReference(array $row): bool
    {
        return array_key_exists('program_id', $row)
            || array_key_exists('program_ids', $row)
            || array_key_exists('program_code', $row)
            || array_key_exists('program_codes', $row)
            || array_key_exists('program_name', $row)
            || array_key_exists('program_names', $row);
    }

    private function parseIntegerList(array $row, string $key): array
    {
        $values = $this->parseStringList($row, $key);
        $integers = [];

        foreach ($values as $value) {
            if (!ctype_digit($value)) {
                throw new InvalidArgumentException("El campo {$key} contiene un valor invalido: {$value}.");
            }

            $integers[] = (int) $value;
        }

        return $integers;
    }

    private function parseStringList(array $row, string $key): array
    {
        if (!array_key_exists($key, $row)) {
            return [];
        }

        $raw = $this->nullableValue($row, $key);
        if ($raw === null) {
            return [];
        }

        $parts = preg_split('/[|;,]+/u', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $parts), function ($part) {
            return $part !== '';
        }));
    }

    private function resolveInstitutionId(array $row, bool $optional): ?int
    {
        $institutionId = $this->nullableInteger($row, 'institution_id');
        if ($institutionId !== null) {
            if (!Institution::where('institution_id', $institutionId)->exists()) {
                throw new InvalidArgumentException('No existe institution_id especificado.');
            }

            return $institutionId;
        }

        $institutionName = $this->nullableValue($row, 'institution_name');
        if ($institutionName === null) {
            if ($optional) {
                return null;
            }

            throw new InvalidArgumentException('Debe indicar institution_id o institution_name.');
        }

        $institution = Institution::whereRaw('LOWER(institution_name) = ?', [mb_strtolower($institutionName, 'UTF-8')])->first();

        if (!$institution && !$optional) {
            throw new InvalidArgumentException('No se encontro la institucion indicada.');
        }

        return $institution ? (int) $institution->institution_id : null;
    }

    private function resolveSingleOrNull($collection, string $ambiguousMessage)
    {
        if ($collection->count() > 1) {
            throw new InvalidArgumentException($ambiguousMessage);
        }

        return $collection->first();
    }

    private function resolveRole(array $row): UserRole
    {
        $roleId = $this->nullableValue($row, 'role_id');
        $roleName = $this->nullableValue($row, 'role_name');

        $role = null;

        if ($roleId !== null) {
            $role = UserRole::find((int) $roleId);
        }

        if (!$role && $roleName !== null) {
            $role = UserRole::whereRaw('LOWER(role_name) = ?', [mb_strtolower($roleName, 'UTF-8')])->first();
        }

        if (!$role) {
            throw new InvalidArgumentException('No se encontro el rol indicado (role_id o role_name).');
        }

        return $role;
    }

    private function resolveAreaId(array $row): ?int
    {
        $areaId = $this->nullableValue($row, 'area_id');
        if ($areaId !== null) {
            $area = Area::find((int) $areaId);
            if (!$area) {
                throw new InvalidArgumentException('No existe area_id especificado.');
            }
            return (int) $area->area_id;
        }

        $areaName = $this->nullableValue($row, 'area_name');
        if ($areaName === null) {
            return null;
        }

        $area = $this->resolveSingleOrNull(
            Area::whereRaw('LOWER(area_name) = ?', [mb_strtolower($areaName, 'UTF-8')])->get(),
            'Hay multiples areas con el mismo nombre. Use area_id para desambiguar.'
        );

        if (!$area) {
            throw new InvalidArgumentException('No se encontro el area indicada en area_name.');
        }

        return (int) $area->area_id;
    }

    private function syncUserJobPositions(User $user, array $row): void
    {
        $positionIds = $this->validateUserJobPositions($row);

        if ($positionIds === null) {
            return;
        }

        $user->jobPositions()->sync($positionIds);
    }

    private function validateUserJobPositions(array $row): ?array
    {
        if (!array_key_exists('job_positions', $row)) {
            return null;
        }

        $raw = $this->nullableValue($row, 'job_positions');

        if ($raw === null) {
            return null;
        }

        $positionNames = $this->splitListValues($raw);
        $positionIds = [];

        foreach ($positionNames as $positionName) {
            $position = JobPosition::whereRaw('LOWER(position_name) = ?', [mb_strtolower($positionName, 'UTF-8')])->first();

            if (!$position) {
                throw new InvalidArgumentException("No se encontro el puesto de trabajo {$positionName}.");
            }

            $positionIds[] = (int) $position->job_position_id;
        }

        return array_values(array_unique($positionIds));
    }

    private function resolveRequestTypeCollaboratorIds(array $row): array
    {
        $collaboratorIds = [];

        $idsRaw = $this->nullableValue($row, 'collaborator_ids');
        if ($idsRaw !== null) {
            foreach ($this->splitListValues($idsRaw) as $idValue) {
                $user = User::find((int) $idValue);
                if (!$user) {
                    throw new InvalidArgumentException("No existe collaborator_id {$idValue}.");
                }
                $this->assertCollaboratorRole($user);
                $collaboratorIds[] = (int) $user->user_id;
            }
        }

        $emailsRaw = $this->nullableValue($row, 'collaborator_emails');
        if ($emailsRaw !== null) {
            foreach ($this->splitListValues($emailsRaw) as $email) {
                $user = User::whereRaw('LOWER(user_email) = ?', [mb_strtolower($email, 'UTF-8')])->first();
                if (!$user) {
                    throw new InvalidArgumentException("No existe collaborator_email {$email}.");
                }
                $this->assertCollaboratorRole($user);
                $collaboratorIds[] = (int) $user->user_id;
            }
        }

        $gestorId = $this->nullableValue($row, 'gestor_id');
        if ($gestorId !== null) {
            $user = User::find((int) $gestorId);
            if (!$user) {
                throw new InvalidArgumentException("No existe gestor_id {$gestorId}.");
            }
            $this->assertCollaboratorRole($user);
            $collaboratorIds[] = (int) $user->user_id;
        }

        $gestorEmail = $this->nullableValue($row, 'gestor_email');
        if ($gestorEmail !== null) {
            $user = User::whereRaw('LOWER(user_email) = ?', [mb_strtolower($gestorEmail, 'UTF-8')])->first();
            if (!$user) {
                throw new InvalidArgumentException("No existe gestor_email {$gestorEmail}.");
            }
            $this->assertCollaboratorRole($user);
            $collaboratorIds[] = (int) $user->user_id;
        }

        return array_values(array_unique($collaboratorIds));
    }

    private function assertCollaboratorRole(User $user): void
    {
        $roleName = mb_strtolower($user->role->role_name ?? '', 'UTF-8');

        if (!in_array($roleName, ['contributor', 'admin'], true)) {
            throw new InvalidArgumentException("El usuario {$user->user_email} no tiene rol Contributor o Admin.");
        }
    }

    private function splitListValues(string $raw): array
    {
        $values = preg_split('/[|;]/', $raw) ?: [];

        return array_values(array_filter(array_map(static fn ($value) => trim($value), $values), static fn ($value) => $value !== ''));
    }

    private function roleRequiresArea(UserRole $role): bool
    {
        $name = mb_strtolower(trim($role->role_name), 'UTF-8');

        return $name === 'admin area' || $name === 'admin área';
    }

    private function supportsCollaboratorAssignments(): bool
    {
        return Schema::hasTable('request_type_user');
    }

    private function entityDefinitions(): array
    {
        return [
            self::ENTITY_USERS => [
                'label' => 'Usuarios',
                'required' => ['user_name', 'user_email', 'role_name', 'password (solo para nuevos)'],
                'optional' => ['role_id', 'area_name', 'area_id', 'document_number', 'institution_link', 'user_phone', 'user_bio', 'is_active', 'job_positions'],
                'sample_headers' => 'user_name,user_email,password,role_name,area_name,document_number,institution_link,user_phone,user_bio,is_active,job_positions',
                'sample_row' => ['Ana Perez', 'ana.perez@ula.edu.ve', 'Secret1234', 'Requester', 'Soporte TI', 'V12345678', 'https://institution.example/ana', '+584121234567', 'Solicitante de ejemplo', 'true', 'Coordinador|Supervisor'],
                'template_file' => 'plantilla_usuarios.csv',
            ],
            self::ENTITY_ROLES => [
                'label' => 'Roles',
                'required' => ['role_name (o role_id para actualizar)'],
                'optional' => ['role_id', 'role_description', 'role_color', 'is_active'],
                'sample_headers' => 'role_name,role_description,role_color,is_active',
                'sample_row' => ['Analista de Mesa', 'Rol para mesa de servicio', '#0d6efd', 'true'],
                'template_file' => 'plantilla_roles.csv',
            ],
            self::ENTITY_REQUEST_TYPES => [
                'label' => 'Topicos',
                'required' => ['type_name (o type_id para actualizar)', 'collaborator_emails o collaborator_ids o gestor_email o gestor_id (al crear)'],
                'optional' => ['type_id', 'type_description', 'type_icon', 'type_color', 'is_active', 'area_name', 'area_id'],
                'sample_headers' => 'type_name,type_description,collaborator_emails,area_name,type_icon,type_color,is_active',
                'sample_row' => ['Soporte de Red', 'Incidentes y solicitudes de red', 'colab1@ula.edu.ve|colab2@ula.edu.ve', 'Soporte TI', 'fa-network-wired', '#198754', 'true'],
                'template_file' => 'plantilla_topicos.csv',
            ],
            self::ENTITY_JOB_POSITIONS => [
                'label' => 'Puestos de Trabajo',
                'required' => ['position_name (o job_position_id para actualizar)'],
                'optional' => ['job_position_id', 'position_description', 'position_color', 'is_active'],
                'sample_headers' => 'position_name,position_description,position_color,is_active',
                'sample_row' => ['Coordinador', 'Coordina operaciones de mesa', '#fd7e14', 'true'],
                'template_file' => 'plantilla_puestos_trabajo.csv',
            ],
            self::ENTITY_INSTITUTIONS => [
                'label' => 'Instituciones',
                'required' => ['institution_name (o institution_id para actualizar)'],
                'optional' => ['institution_id', 'institution_description', 'institution_logo', 'is_active'],
                'sample_headers' => 'institution_name,institution_description,institution_logo,is_active',
                'sample_row' => ['Universidad de Los Andes', 'Institucion principal', 'https://example.org/logo-ula.png', 'true'],
                'template_file' => 'plantilla_instituciones.csv',
            ],
            self::ENTITY_FACULTIES => [
                'label' => 'Facultades',
                'required' => ['faculty_name (o faculty_id para actualizar)'],
                'optional' => ['faculty_id', 'faculty_description', 'institution_id', 'institution_name', 'is_active'],
                'sample_headers' => 'faculty_name,faculty_description,institution_name,is_active',
                'sample_row' => ['Ingenieria', 'Facultad de Ingenieria', 'Universidad de Los Andes', 'true'],
                'template_file' => 'plantilla_facultades.csv',
            ],
            self::ENTITY_AREAS => [
                'label' => 'Areas',
                'required' => ['area_name (o area_id para actualizar)'],
                'optional' => ['area_id', 'area_description', 'faculty_id', 'faculty_name', 'institution_id', 'institution_name', 'is_active'],
                'sample_headers' => 'area_name,area_description,faculty_name,institution_name,is_active',
                'sample_row' => ['Soporte TI', 'Mesa de ayuda tecnologica', 'Ingenieria', 'Universidad de Los Andes', 'true'],
                'template_file' => 'plantilla_areas.csv',
            ],
            self::ENTITY_PROGRAMS => [
                'label' => 'Programas',
                'required' => ['program_name (o program_id/program_code para actualizar)', 'faculty_id o faculty_name para crear'],
                'optional' => ['program_id', 'program_code', 'program_description', 'faculty_id', 'faculty_name', 'institution_id', 'institution_name', 'is_active'],
                'sample_headers' => 'program_code,program_name,program_description,faculty_name,institution_name,is_active',
                'sample_row' => ['ING-SIS', 'Ingenieria de Sistemas', 'Programa de ingenieria', 'Ingenieria', 'Universidad de Los Andes', 'true'],
                'template_file' => 'plantilla_programas.csv',
            ],
            self::ENTITY_COURSES => [
                'label' => 'Cursos',
                'required' => ['course_name y course_code (o course_id para actualizar)', 'program_id/program_ids/program_code/program_codes/program_name/program_names para crear'],
                'optional' => ['course_id', 'course_description', 'credits', 'program_id', 'program_ids', 'program_code', 'program_codes', 'program_name', 'program_names', 'faculty_id', 'faculty_name', 'institution_id', 'institution_name', 'is_active'],
                'sample_headers' => 'course_code,course_name,course_description,credits,program_codes,institution_name,is_active',
                'sample_row' => ['SIS-101', 'Fundamentos de Sistemas', 'Curso introductorio', '3', 'ING-SIS|ADM-SIS', 'Universidad Catolica Luis Amigo', 'true'],
                'template_file' => 'plantilla_cursos.csv',
            ],
        ];
    }

    private function ensureAdminRole(): void
    {
        $roleName = auth()->user()->role->role_name ?? null;

        if ($roleName !== 'Admin') {
            abort(403, 'Solo los administradores pueden usar la carga masiva.');
        }
    }
}
