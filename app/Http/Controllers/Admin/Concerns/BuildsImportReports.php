<?php

namespace App\Http\Controllers\Admin\Concerns;

trait BuildsImportReports
{
    /**
     * Columnas (con alternativas) que identifican una fila, por entidad.
     */
    private function identifierColumns(string $entity): array
    {
        return match ($entity) {
            'users' => [['user_email']],
            'roles' => [['role_name', 'role_id']],
            'request_types' => [['type_name', 'type_id']],
            'job_positions' => [['position_name', 'job_position_id']],
            'institutions' => [['institution_name', 'institution_id']],
            'faculties' => [['faculty_name', 'faculty_id'], ['institution_name', 'institution_id']],
            'areas' => [['area_name', 'area_id'], ['faculty_name', 'faculty_id']],
            'programs' => [['program_code', 'program_name', 'program_id'], ['faculty_name', 'faculty_id']],
            'courses' => [['course_code', 'course_name', 'course_id']],
            default => [],
        };
    }

    /**
     * Texto que identifica una fila (por ejemplo su correo o su nombre). Null si no se puede determinar.
     */
    private function rowIdentifier(string $entity, array $row): ?string
    {
        $parts = [];

        foreach ($this->identifierColumns($entity) as $alternatives) {
            $value = null;

            foreach ($alternatives as $column) {
                $candidate = trim((string) ($row[$column] ?? ''));

                if ($candidate !== '') {
                    $value = $candidate;
                    break;
                }
            }

            $parts[] = $value;
        }

        if (empty($parts) || $parts[0] === null) {
            return null;
        }

        return implode(' | ', array_filter($parts, fn ($part) => $part !== null));
    }

    /**
     * Deduce a que columna se refiere un mensaje de error.
     */
    private function detectErrorField(string $entity, string $message, array $row): string
    {
        $candidates = array_keys($row);
        $definition = $this->entityDefinitions()[$entity] ?? null;

        if ($definition) {
            $text = implode(' ', array_merge([$definition['sample_headers']], $definition['required'], $definition['optional']));
            preg_match_all('/[a-z]+(?:_[a-z0-9]+)+/', $text, $matches);
            $candidates = array_merge($candidates, $matches[0], ['password', 'credits']);
        }

        $best = null;
        $bestPosition = PHP_INT_MAX;

        foreach (array_unique($candidates) as $column) {
            $position = mb_stripos($message, (string) $column);

            if ($position !== false && $position < $bestPosition) {
                $best = (string) $column;
                $bestPosition = $position;
            }
        }

        return $best ?? '(fila completa)';
    }

    /**
     * Guarda en sesion los errores de la ultima previsualizacion o importacion para poder descargarlos.
     */
    private function rememberErrorsReport(string $entity, string $fileName, string $source, array $errors): void
    {
        if (empty($errors)) {
            session()->forget('bulk_import_errors_report');
            return;
        }

        session()->put('bulk_import_errors_report', [
            'entity' => $entity,
            'file_name' => $fileName,
            'source' => $source,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'errors' => array_slice($errors, 0, 5000),
        ]);
    }

    /**
     * Evita que una celda se interprete como formula al abrir el CSV en Excel.
     */
    private function csvSafeCell($value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Descarga el reporte de errores: fila del archivo, campo y motivo. No incluye
     * contrasenas, telefonos, documentos ni otros datos de las filas.
     */
    public function downloadErrorsReport()
    {
        $this->ensureAdminRole();

        $report = session('bulk_import_errors_report');

        if (!$report || empty($report['errors'])) {
            return redirect()->route('admin.bulk-import.index')
                ->with('error', 'No hay un reporte de errores disponible. Ejecute primero una previsualizacion o una carga.');
        }

        $label = $this->entityDefinitions()[$report['entity']]['label'] ?? $report['entity'];
        $filename = 'reporte_errores_' . $report['entity'] . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($report, $label) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Reporte de errores de carga masiva']);
            fputcsv($output, ['Entidad', $this->csvSafeCell($label)]);
            fputcsv($output, ['Archivo', $this->csvSafeCell($report['file_name'])]);
            fputcsv($output, ['Origen', $report['source'] === 'preview' ? 'Previsualizacion' : 'Importacion']);
            fputcsv($output, ['Generado', $report['generated_at']]);
            fputcsv($output, ['Total de errores', count($report['errors'])]);
            fputcsv($output, []);
            fputcsv($output, ['Fila del archivo', 'Campo', 'Motivo']);

            foreach ($report['errors'] as $error) {
                fputcsv($output, [
                    $error['line'],
                    $this->csvSafeCell($error['field'] ?? ''),
                    $this->csvSafeCell($error['message']),
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}