<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestType;
use App\Models\User;
use App\Models\Area;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class AdminRequestTypeController extends Controller
{
    public function index(Request $request)
    {
        $supportsCollaboratorAssignments = $this->supportsCollaboratorAssignments();

        $query = RequestType::with($supportsCollaboratorAssignments ? ['gestor', 'collaborators'] : ['gestor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type_name', 'like', "%{$search}%")
                  ->orWhere('type_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $requestTypes = $query->orderBy('type_name')->paginate(15)->withQueryString();
        return view('admin.request-types.index', compact('requestTypes', 'supportsCollaboratorAssignments'));
    }

    public function create()
    {
        $collaborators = User::whereHas('role', function($query) {
            $query->whereIn('role_name', ['Contributor', 'Operario']);
        })->where('is_active', true)->orderBy('user_name')->get();
        $regionalResponsibleUsers = User::with('role')
            ->whereHas('role', function($query) {
                $query->whereIn('role_name', ['Contributor', 'Operario', 'Admin Área']);
            })
            ->where('is_active', true)
            ->orderBy('user_name')
            ->get();
        $areas = Area::where('is_active', true)->get();
        $institutions = $this->supportsRegionalAssignments()
            ? Institution::where('is_active', true)->orderBy('institution_name')->get()
            : collect();

        return view('admin.request-types.create', compact('collaborators', 'regionalResponsibleUsers', 'areas', 'institutions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
            'type_description' => 'nullable|string',
            'collaborator_ids' => 'nullable|array|min:1',
            'collaborator_ids.*' => 'exists:users,user_id',
            'gestor_id' => 'nullable|exists:users,user_id',
            'area_id' => 'nullable|exists:areas,area_id',
            'type_icon' => 'nullable|string',
            'type_color' => 'nullable|string',
            'regional_assignments' => 'nullable|array',
            'regional_assignments.*' => 'nullable|array',
            'regional_assignments.*.*' => 'nullable|exists:users,user_id',
        ]);

        $regionalAssignments = $request->input('regional_assignments', []);
        $collaboratorUserIds = User::whereHas('role', function($query) {
            $query->whereIn('role_name', ['Contributor', 'Operario']);
        })->where('is_active', true)->pluck('user_id');
        $selectedCollaborators = $this->resolveCollaboratorIds($request, $collaboratorUserIds);

        if (empty($selectedCollaborators) && !$this->hasAnyRegionalResponsible($regionalAssignments) && !$request->filled('gestor_id')) {
            return back()
                ->withInput()
                ->withErrors([
                    'regional_assignments' => 'Debes asignar al menos un responsable global o por regional.',
                ]);
        }

        $regionalLeaderId = $this->resolveRegionalLeaderId($regionalAssignments);

        $data = $request->except(['collaborator_ids', 'regional_assignments']);
        $data['gestor_id'] = $selectedCollaborators[0] ?? ($request->filled('gestor_id') ? (int) $request->input('gestor_id') : $regionalLeaderId);
        $data['is_active'] = $request->has('is_active');

        $requestType = RequestType::create($data);
        if ($this->supportsCollaboratorAssignments()) {
            $requestType->collaborators()->sync($selectedCollaborators);
        }

        $this->syncRegionalAssignments($requestType, $regionalAssignments);

        return redirect()->route('admin.request-types.index')->with('success', 'Tópico de servicio creado exitosamente.');
    }

    public function edit($id)
    {
        $supportsCollaboratorAssignments = $this->supportsCollaboratorAssignments();
        $supportsRegionalAssignments = $this->supportsRegionalAssignments();
        $requestType = RequestType::with(array_filter([
            $supportsCollaboratorAssignments ? 'collaborators' : null,
            $supportsRegionalAssignments ? 'regionalAssignments' : null,
        ]))->findOrFail($id);
        $collaborators = User::whereHas('role', function($query) {
            $query->whereIn('role_name', ['Contributor', 'Operario']);
        })->where('is_active', true)->orderBy('user_name')->get();
        $regionalResponsibleUsers = User::with('role')
            ->whereHas('role', function($query) {
                $query->whereIn('role_name', ['Contributor', 'Operario', 'Admin Área']);
            })
            ->where('is_active', true)
            ->orderBy('user_name')
            ->get();
        $areas = Area::where('is_active', true)->get();
        $institutions = $supportsRegionalAssignments
            ? Institution::where('is_active', true)->orderBy('institution_name')->get()
            : collect();

        $regionalAssignments = $supportsRegionalAssignments
            ? $requestType->regionalAssignments
                ->groupBy('institution_id')
                ->map(fn ($items) => $items->pluck('user_id')->map(fn ($id) => (int) $id)->unique()->values()->all())
                ->all()
            : [];

        $selectedCollaboratorIds = $supportsCollaboratorAssignments
            ? $requestType->collaborators->pluck('user_id')->all()
            : [];
        if (empty($selectedCollaboratorIds) && $requestType->gestor_id) {
            $selectedCollaboratorIds = [(int) $requestType->gestor_id];
        }

        return view('admin.request-types.edit', compact('requestType', 'collaborators', 'regionalResponsibleUsers', 'areas', 'selectedCollaboratorIds', 'institutions', 'regionalAssignments'));
    }

    public function update(Request $request, $id)
    {
        $supportsCollaboratorAssignments = $this->supportsCollaboratorAssignments();
        $requestType = RequestType::with($supportsCollaboratorAssignments ? ['collaborators'] : [])->findOrFail($id);

        $request->validate([
            'type_name' => 'required|string|max:255',
            'type_description' => 'nullable|string',
            'collaborator_ids' => 'nullable|array|min:1',
            'collaborator_ids.*' => 'exists:users,user_id',
            'gestor_id' => 'nullable|exists:users,user_id',
            'area_id' => 'nullable|exists:areas,area_id',
            'type_icon' => 'nullable|string',
            'type_color' => 'nullable|string',
            'regional_assignments' => 'nullable|array',
            'regional_assignments.*' => 'nullable|array',
            'regional_assignments.*.*' => 'nullable|exists:users,user_id',
        ]);

        $regionalAssignments = $request->input('regional_assignments', []);
        $collaboratorUserIds = User::whereHas('role', function($query) {
            $query->whereIn('role_name', ['Contributor', 'Operario']);
        })->where('is_active', true)->pluck('user_id');
        $selectedCollaborators = $this->resolveCollaboratorIds($request, $collaboratorUserIds);

        if (empty($selectedCollaborators) && !$this->hasAnyRegionalResponsible($regionalAssignments) && !$request->filled('gestor_id')) {
            return back()
                ->withInput()
                ->withErrors([
                    'regional_assignments' => 'Debes asignar al menos un responsable global o por regional.',
                ]);
        }

        $regionalLeaderId = $this->resolveRegionalLeaderId($regionalAssignments);

        $data = $request->except(['collaborator_ids', 'regional_assignments']);
        $data['gestor_id'] = $selectedCollaborators[0] ?? ($request->filled('gestor_id') ? (int) $request->input('gestor_id') : $regionalLeaderId);
        $data['is_active'] = $request->has('is_active');

        $requestType->update($data);
        if ($supportsCollaboratorAssignments) {
            $requestType->collaborators()->sync($selectedCollaborators);
        }

        $this->syncRegionalAssignments($requestType, $regionalAssignments);

        return redirect()->route('admin.request-types.index')->with('success', 'Tópico de servicio actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $requestType = RequestType::findOrFail($id);
        
        if ($requestType->tickets()->count() > 0) {
            return redirect()->route('admin.request-types.index')->with('error', 'No se puede eliminar el tópico porque tiene tickets asociados.');
        }

        $requestType->delete();

        return redirect()->route('admin.request-types.index')->with('success', 'Tópico eliminado exitosamente.');
    }

    private function resolveCollaboratorIds(Request $request, Collection $availableCollaboratorIds): array
    {
        $allowedCollaboratorIds = $availableCollaboratorIds
            ->map(fn ($id) => (int) $id)
            ->values();

        $collaboratorIds = collect($request->input('collaborator_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $allowedCollaboratorIds->contains($id))
            ->unique()
            ->values();

        $regionalCollaboratorIds = collect($request->input('regional_assignments', []))
            ->flatMap(fn ($userIds) => (array) $userIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $allowedCollaboratorIds->contains($id))
            ->unique()
            ->values();

        $collaboratorIds = $collaboratorIds
            ->merge($regionalCollaboratorIds)
            ->unique()
            ->values();

        if ($collaboratorIds->isEmpty() && $request->filled('gestor_id')) {
            $collaboratorIds = collect([(int) $request->input('gestor_id')]);
        }

        return $collaboratorIds->all();
    }

    private function hasAnyRegionalResponsible(array $regionalAssignments): bool
    {
        return collect($regionalAssignments)
            ->flatMap(fn ($userIds) => (array) $userIds)
            ->filter()
            ->isNotEmpty();
    }

    private function resolveRegionalLeaderId(array $regionalAssignments): ?int
    {
        $firstUserId = collect($regionalAssignments)
            ->flatMap(fn ($userIds) => (array) $userIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->first();

        return $firstUserId ? (int) $firstUserId : null;
    }

    private function supportsCollaboratorAssignments(): bool
    {
        return Schema::hasTable('request_type_user');
    }

    private function supportsRegionalAssignments(): bool
    {
        return Schema::hasTable('request_type_regional_assignments');
    }

    private function syncRegionalAssignments(RequestType $requestType, array $regionalAssignments): void
    {
        if (!$this->supportsRegionalAssignments()) {
            return;
        }

        $rows = collect($regionalAssignments)
            ->flatMap(function ($userIds, $institutionId) use ($requestType) {
                return collect((array) $userIds)
                    ->filter()
                    ->map(fn ($userId) => [
                        'request_type_id' => (int) $requestType->type_id,
                        'institution_id' => (int) $institutionId,
                        'user_id' => (int) $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            })
            ->unique(fn ($row) => $row['request_type_id'] . '-' . $row['institution_id'] . '-' . $row['user_id'])
            ->values()
            ->all();

        DB::table('request_type_regional_assignments')
            ->where('request_type_id', $requestType->type_id)
            ->delete();

        if (!empty($rows)) {
            DB::table('request_type_regional_assignments')->insert($rows);
        }
    }
}

