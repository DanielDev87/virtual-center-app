<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\RequestType;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingTrendsController extends Controller
{
    private const MIN_TOTAL = 10;
    private const MIN_GROUP = 5;

    public function index(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'request_type_id' => 'nullable|integer',
            'area_id' => 'nullable|integer',
        ], [
            'start_date.date' => 'La fecha de inicio no es válida.',
            'end_date.date' => 'La fecha de fin no es válida.',
        ]);

        if (!empty($data['start_date']) && !empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            return redirect()->route('admin.reports.ratings')
                ->withErrors(['end_date' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.']);
        }

        $end = $data['end_date'] ?? now()->toDateString();
        $start = $data['start_date'] ?? Carbon::parse($end)->subMonths(6)->toDateString();
        $topicId = isset($data['request_type_id']) ? (int) $data['request_type_id'] : null;
        $areaId = isset($data['area_id']) ? (int) $data['area_id'] : null;

        $base = $this->baseQuery($start, $end, $topicId, $areaId);

        $total = (clone $base)->count();
        $average = $total > 0 ? (float) (clone $base)->avg('tickets.rating') : null;

        $counts = (clone $base)
            ->select('tickets.rating', DB::raw('COUNT(*) as total'))
            ->groupBy('tickets.rating')
            ->pluck('total', 'rating');

        $distribution = [];
        foreach ([5, 4, 3, 2, 1] as $stars) {
            $count = (int) ($counts[$stars] ?? 0);
            $distribution[] = [
                'stars' => $stars,
                'count' => $count,
                'percent' => $total > 0 ? round($count * 100 / $total, 1) : 0,
            ];
        }

        $trend = (clone $base)
            ->selectRaw("DATE_FORMAT(tickets.updated_at, '%Y-%m') as period")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(tickets.rating) as average')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                return [
                    'label' => Carbon::createFromFormat('!Y-m', $row->period)->locale('es')->isoFormat('MMMM YYYY'),
                    'total' => (int) $row->total,
                    'average' => (float) $row->average,
                    'enough' => (int) $row->total >= self::MIN_GROUP,
                ];
            })
            ->all();

        $change = null;
        if (count($trend) >= 2) {
            $first = $trend[0];
            $last = $trend[count($trend) - 1];
            $change = [
                'enough' => $first['enough'] && $last['enough'],
                'diff' => round($last['average'] - $first['average'], 2),
                'from' => $first['label'],
                'to' => $last['label'],
            ];
        }

        $byTopic = $this->groupRows(clone $base, "COALESCE(request_types.type_name, 'Sin tópico')");
        $byArea = $this->groupRows(clone $base, "COALESCE(areas.area_name, 'Sin área')");

        if ($request->query('export') === 'csv') {
            if ($total === 0) {
                return redirect()->route('admin.reports.ratings', $request->except('export'))
                    ->with('error', 'No hay datos para exportar con los filtros aplicados.');
            }

            $filterSummary = [
                'Periodo' => $start . ' a ' . $end,
                'Tópico' => $topicId ? (optional(RequestType::find($topicId))->type_name ?? $topicId) : 'Todos',
                'Área' => $areaId ? (optional(Area::find($areaId))->area_name ?? $areaId) : 'Todas',
            ];

            return $this->exportCsv($filterSummary, $total, $average, $distribution, $trend, $byTopic, $byArea);
        }

        // Los comentarios solo se muestran al Admin; el Monitor ve únicamente datos agregados.
        $isAdmin = (auth()->user()->role->role_name ?? '') === 'Admin';
        $comments = $isAdmin
            ? (clone $base)
                ->whereNotNull('tickets.feedback')
                ->where('tickets.feedback', '!=', '')
                ->orderByDesc('tickets.updated_at')
                ->limit(10)
                ->get(['tickets.ticket_id', 'tickets.ticket_number', 'tickets.rating', 'tickets.feedback', 'tickets.updated_at'])
            : collect();

        return view('admin.reports.ratings', [
            'filters' => ['start_date' => $start, 'end_date' => $end, 'request_type_id' => $topicId, 'area_id' => $areaId],
            'defaultPeriod' => empty($data['start_date']) && empty($data['end_date']),
            'total' => $total,
            'average' => $average,
            'insufficient' => $total < self::MIN_TOTAL,
            'minTotal' => self::MIN_TOTAL,
            'minGroup' => self::MIN_GROUP,
            'distribution' => $distribution,
            'trend' => $trend,
            'change' => $change,
            'byTopic' => $byTopic,
            'byArea' => $byArea,
            'comments' => $comments,
            'isAdmin' => $isAdmin,
            'topics' => RequestType::orderBy('type_name')->get(['type_id', 'type_name']),
            'areas' => Area::orderBy('area_name')->get(['area_id', 'area_name']),
        ]);
    }

    private function baseQuery(string $start, string $end, ?int $topicId, ?int $areaId)
    {
        $query = Ticket::query()
            ->leftJoin('request_types', 'tickets.request_type_id', '=', 'request_types.type_id')
            ->leftJoin('areas', 'request_types.area_id', '=', 'areas.area_id')
            ->whereNotNull('tickets.rating')
            ->whereDate('tickets.updated_at', '>=', $start)
            ->whereDate('tickets.updated_at', '<=', $end);

        if ($topicId) {
            $query->where('tickets.request_type_id', $topicId);
        }

        if ($areaId) {
            $query->where('request_types.area_id', $areaId);
        }

        return $query;
    }

    private function groupRows($query, string $labelSql): array
    {
        return $query
            ->selectRaw($labelSql . ' as label')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(tickets.rating) as average')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'label' => $row->label,
                    'total' => (int) $row->total,
                    'average' => (float) $row->average,
                    'enough' => (int) $row->total >= self::MIN_GROUP,
                ];
            })
            ->all();
    }

    private function safeCell($value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    private function averageCell(array $row): string
    {
        return $row['enough'] ? number_format($row['average'], 2) : 'Datos insuficientes';
    }

    private function exportCsv(array $filterSummary, int $total, ?float $average, array $distribution, array $trend, array $byTopic, array $byArea)
    {
        $filename = 'tendencias_calificacion_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filterSummary, $total, $average, $distribution, $trend, $byTopic, $byArea) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Tendencias de calificación del servicio']);
            fputcsv($out, ['Generado', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Filtros aplicados']);
            foreach ($filterSummary as $label => $value) {
                fputcsv($out, [$label, $this->safeCell($value)]);
            }
            fputcsv($out, ['Nota', 'Datos agregados: no incluye comentarios ni datos de solicitantes. La fecha de cada calificación es la de la última actualización del ticket.']);
            fputcsv($out, []);

            fputcsv($out, ['Respuestas incluidas', $total]);
            if ($average !== null) {
                $averageText = number_format($average, 2);
                if ($total < self::MIN_TOTAL) {
                    $averageText .= ' (orientativo: datos insuficientes)';
                }
                fputcsv($out, ['Promedio general', $averageText]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Distribución de calificaciones']);
            fputcsv($out, ['Estrellas', 'Respuestas', 'Porcentaje']);
            foreach ($distribution as $row) {
                fputcsv($out, [$row['stars'], $row['count'], $row['percent'] . '%']);
            }
            fputcsv($out, []);

            fputcsv($out, ['Evolución por mes']);
            fputcsv($out, ['Mes', 'Respuestas', 'Promedio']);
            foreach ($trend as $row) {
                fputcsv($out, [$this->safeCell($row['label']), $row['total'], $this->averageCell($row)]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Por tópico']);
            fputcsv($out, ['Tópico', 'Respuestas', 'Promedio']);
            foreach ($byTopic as $row) {
                fputcsv($out, [$this->safeCell($row['label']), $row['total'], $this->averageCell($row)]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Por área']);
            fputcsv($out, ['Área', 'Respuestas', 'Promedio']);
            foreach ($byArea as $row) {
                fputcsv($out, [$this->safeCell($row['label']), $row['total'], $this->averageCell($row)]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}