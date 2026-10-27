<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use App\Models\SlaEscalationRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AreaAdminSlaRuleController extends Controller
{
    private function areaId(): ?int
    {
        return Auth::user()->area_id ? (int) Auth::user()->area_id : null;
    }

    /**
     * Tópicos que este Admin de Área puede configurar (mismo alcance que usa en el resto del sistema).
     */
    private function topicsQuery()
    {
        $userId = (int) Auth::id();
        $query = RequestType::where('area_id', $this->areaId())->where('is_active', true);

        if (!Schema::hasTable('request_type_regional_assignments')) {
            return $query;
        }

        return $query->where(function ($topicQuery) use ($userId) {
            $topicQuery->whereDoesntHave('regionalAssignments')
                ->orWhereHas('regionalAssignments', fn ($regional) => $regional->where('user_id', $userId));
        });
    }

    /**
     * Quiénes pueden ser responsables de escalamiento de un tópico:
     * Admin de Área del área, colaboradores del tópico y su gestor.
     */
    private function ownerCandidates(RequestType $topic)
    {
        $ids = User::where('area_id', $topic->area_id)
            ->whereHas('role', fn ($query) => $query->where('role_name', 'Admin Área'))
            ->pluck('user_id');

        if (Schema::hasTable('request_type_user')) {
            $ids = $ids->merge($topic->collaborators()->pluck('users.user_id'));
        }

        $ids = $ids->merge([$topic->gestor_id])->filter()->map(fn ($id) => (int) $id)->unique()->values();

        return User::whereIn('user_id', $ids)->where('is_active', true)->orderBy('user_name')->get(['user_id', 'user_name']);
    }

    public function index()
    {
        if (!$this->areaId()) {
            return redirect()->route('area-admin.dashboard')
                ->with('error', 'Tu usuario no tiene un área asignada.');
        }

        $topics = $this->topicsQuery()->orderBy('type_name')->get(['type_id', 'type_name', 'area_id', 'gestor_id']);
        $ownersByTopic = [];
        $allOwners = collect();

        foreach ($topics as $topic) {
            $owners = $this->ownerCandidates($topic);
            $ownersByTopic[$topic->type_id] = $owners;
            $allOwners = $allOwners->merge($owners);
        }

        $allOwners = $allOwners->unique('user_id')->sortBy('user_name')->values();

        $rules = Schema::hasTable('sla_escalation_rules')
            ? SlaEscalationRule::whereIn('request_type_id', $topics->pluck('type_id'))
                ->with('requestType')
                ->orderBy('request_type_id')
                ->orderBy('priority')
                ->get()
            : collect();

        return view('area-admin.sla-rules.index', compact('topics', 'rules', 'ownersByTopic', 'allOwners'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request, null);
        $data['is_active'] = true;

        SlaEscalationRule::create($data);

        return redirect()->route('area-admin.sla-rules.index')->with('sla_success', 'Regla creada correctamente.');
    }

    public function update(Request $request, $ruleId)
    {
        $rule = $this->findVisibleRule($ruleId);

        $data = $this->validatedData($request, $rule);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['request_type_id']);

        $rule->update($data);

        return redirect()->route('area-admin.sla-rules.index')->with('sla_success', 'Regla actualizada correctamente.');
    }

    public function destroy($ruleId)
    {
        $this->findVisibleRule($ruleId)->delete();

        return redirect()->route('area-admin.sla-rules.index')->with('sla_success', 'Regla eliminada. Los escalamientos ya enviados se conservan en el registro.');
    }

    private function findVisibleRule($ruleId): SlaEscalationRule
    {
        return SlaEscalationRule::whereIn('request_type_id', $this->topicsQuery()->pluck('type_id'))
            ->where('rule_id', $ruleId)
            ->firstOrFail();
    }

    private function validatedData(Request $request, ?SlaEscalationRule $rule): array
    {
        $validated = $request->validateWithBag('rule', [
            'request_type_id' => $rule ? 'nullable' : 'required|integer',
            'priority' => 'nullable|in:1,2,3,4',
            'threshold_percent' => 'required|integer|min:5|max:90',
            'escalate_user_id' => 'nullable|integer',
        ], [
            'request_type_id.required' => 'Selecciona un tópico.',
            'priority.in' => 'La prioridad no es válida.',
            'threshold_percent.required' => 'Indica el porcentaje de aviso.',
            'threshold_percent.integer' => 'El porcentaje debe ser un número entero.',
            'threshold_percent.min' => 'El porcentaje mínimo es 5.',
            'threshold_percent.max' => 'El porcentaje máximo es 90.',
        ]);

        $topicId = $rule ? (int) $rule->request_type_id : (int) $validated['request_type_id'];
        $topic = $this->topicsQuery()->where('type_id', $topicId)->first();

        if (!$topic) {
            throw ValidationException::withMessages([
                'request_type_id' => 'No puedes configurar reglas para ese tópico.',
            ])->errorBag('rule');
        }

        $ownerId = !empty($validated['escalate_user_id']) ? (int) $validated['escalate_user_id'] : null;
        if ($ownerId !== null && !$this->ownerCandidates($topic)->contains('user_id', $ownerId)) {
            throw ValidationException::withMessages([
                'escalate_user_id' => 'El responsable elegido no pertenece al equipo de ese tópico.',
            ])->errorBag('rule');
        }

        $priority = ($validated['priority'] ?? '') !== '' ? (int) $validated['priority'] : null;

        $duplicate = SlaEscalationRule::where('request_type_id', $topicId)
            ->when($priority === null, fn ($query) => $query->whereNull('priority'), fn ($query) => $query->where('priority', $priority))
            ->when($rule, fn ($query) => $query->where('rule_id', '!=', $rule->rule_id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'priority' => 'Ya existe una regla para ese tópico y esa prioridad.',
            ])->errorBag('rule');
        }

        return [
            'request_type_id' => $topicId,
            'priority' => $priority,
            'threshold_percent' => (int) $validated['threshold_percent'],
            'escalate_user_id' => $ownerId,
        ];
    }
}