<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBulkImportController;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class AdminBulkImportContractTest extends TestCase
{
    /** @test */
    public function bulk_import_entity_headers_match_database_schema_or_allowed_lookup_fields()
    {
        $definitions = $this->getEntityDefinitions();

        $allowedLookupFields = [
            'users' => ['role_name', 'area_name', 'job_positions'],
            'roles' => [],
            'request_types' => ['collaborator_emails', 'area_name'],
            'job_positions' => [],
            'institutions' => [],
            'faculties' => ['institution_name'],
            'areas' => ['faculty_name', 'institution_name'],
            'programs' => ['faculty_name', 'institution_name'],
            'courses' => [
                'program_code',
                'program_codes',
                'program_name',
                'program_names',
                'program_ids',
                'faculty_name',
                'institution_name',
            ],
        ];

        $tableByEntity = [
            'users' => 'users',
            'roles' => 'user_roles',
            'request_types' => 'request_types',
            'job_positions' => 'job_positions',
            'institutions' => 'institutions',
            'faculties' => 'faculties',
            'areas' => 'areas',
            'programs' => 'programs',
            'courses' => 'courses',
        ];

        foreach ($definitions as $entity => $definition) {
            $this->assertArrayHasKey($entity, $tableByEntity, "No table mapping found for entity {$entity}.");
            $this->assertArrayHasKey($entity, $allowedLookupFields, "No allowed lookup mapping found for entity {$entity}.");
            $this->assertArrayHasKey('sample_headers', $definition, "Missing sample_headers for entity {$entity}.");
            $this->assertArrayHasKey('sample_row', $definition, "Missing sample_row for entity {$entity}.");
            $this->assertArrayHasKey('template_file', $definition, "Missing template_file for entity {$entity}.");

            $headers = array_map('trim', explode(',', $definition['sample_headers']));
            $this->assertNotEmpty($headers, "Entity {$entity} must define at least one header.");
            $this->assertCount(
                count($headers),
                $definition['sample_row'],
                "Entity {$entity} has mismatched sample_headers and sample_row lengths."
            );
            $this->assertStringEndsWith('.csv', $definition['template_file'], "Entity {$entity} must expose a CSV template file.");

            $tableColumns = Schema::getColumnListing($tableByEntity[$entity]);
            $allowedFields = array_merge($tableColumns, $allowedLookupFields[$entity]);

            foreach ($headers as $header) {
                $this->assertContains(
                    $header,
                    $allowedFields,
                    "Header {$header} is not a DB column or allowed lookup field for entity {$entity}."
                );
            }
        }
    }

    private function getEntityDefinitions(): array
    {
        $controller = new AdminBulkImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('entityDefinitions');
        $method->setAccessible(true);

        /** @var array<string, array<string, mixed>> $definitions */
        $definitions = $method->invoke($controller);

        return $definitions;
    }
}
