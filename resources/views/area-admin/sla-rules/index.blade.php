@extends('layouts.area-admin')

@section('title', 'Reglas de escalamiento SLA')

@section('content')
@php
    $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta', 4 => 'Urgente'];
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 mb-0">Reglas de escalamiento SLA</h1>
        <a href="{{ route('area-admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver a tickets
        </a>
    </div>

    @if(session('sla_success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('sla_success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->rule->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->rule->all() as $ruleError)
                <li>{{ $ruleError }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <p class="small text-muted">
        Cuando un ticket abierto de un tópico llega al porcentaje de tiempo restante que definas aquí, se avisa una sola vez al responsable de escalamiento
        (si no eliges uno, a los Admin de Área del área) y al mediador del ticket. El cálculo respeta la jornada laboral y los festivos.
        Si un tópico no tiene reglas, no se envían avisos de escalamiento.
    </p>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Reglas configuradas</h6></div>
        <div class="card-body">
            @forelse($rules as $rule)
            <div class="border rounded p-3 mb-3">
                <div class="fw-bold mb-2">
                    {{ optional($rule->requestType)->type_name ?? 'Tópico' }}
                    <span class="badge bg-info text-dark ms-1">{{ $rule->priority ? 'Prioridad: ' . ($priorityNames[$rule->priority] ?? $rule->priority) : 'Todas las prioridades' }}</span>
                    @unless($rule->is_active)<span class="badge bg-warning text-dark">Inactiva</span>@endunless
                </div>
                <form action="{{ route('area-admin.sla-rules.update', $rule->rule_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1" for="rulePriority{{ $rule->rule_id }}">Prioridad</label>
                            <select id="rulePriority{{ $rule->rule_id }}" name="priority" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach($priorityNames as $priorityKey => $priorityName)
                                    <option value="{{ $priorityKey }}" {{ (int) $rule->priority === $priorityKey ? 'selected' : '' }}>{{ $priorityName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1" for="ruleThreshold{{ $rule->rule_id }}">Avisar al quedar (%)</label>
                            <input id="ruleThreshold{{ $rule->rule_id }}" type="number" name="threshold_percent" class="form-control form-control-sm" min="5" max="90" value="{{ $rule->threshold_percent }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1" for="ruleOwner{{ $rule->rule_id }}">Responsable de escalamiento</label>
                            <select id="ruleOwner{{ $rule->rule_id }}" name="escalate_user_id" class="form-select form-select-sm">
                                <option value="">Admin de Área del área</option>
                                @foreach(($ownersByTopic[$rule->request_type_id] ?? collect()) as $owner)
                                    <option value="{{ $owner->user_id }}" {{ (int) $rule->escalate_user_id === (int) $owner->user_id ? 'selected' : '' }}>{{ $owner->user_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ruleActive{{ $rule->rule_id }}" {{ $rule->is_active ? 'checked' : '' }}>
                                <label class="form-check-label small" for="ruleActive{{ $rule->rule_id }}">Activa</label>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Guardar</button>
                        </div>
                    </div>
                </form>
                <form action="{{ route('area-admin.sla-rules.destroy', $rule->rule_id) }}" method="POST" class="mt-2 text-end"
                      onsubmit="return confirm('¿Eliminar esta regla?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Eliminar</button>
                </form>
            </div>
            @empty
            <div class="text-center text-muted py-3">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">Aún no hay reglas de escalamiento.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Agregar una regla</h6></div>
        <div class="card-body">
            <form action="{{ route('area-admin.sla-rules.store') }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1" for="newRuleTopic">Tópico <span class="text-danger">*</span></label>
                        <select id="newRuleTopic" name="request_type_id" class="form-select" required>
                            <option value="">-- Selecciona --</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->type_id }}" {{ old('request_type_id') == $topic->type_id ? 'selected' : '' }}>{{ $topic->type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1" for="newRulePriority">Prioridad</label>
                        <select id="newRulePriority" name="priority" class="form-select">
                            <option value="">Todas</option>
                            @foreach($priorityNames as $priorityKey => $priorityName)
                                <option value="{{ $priorityKey }}" {{ old('priority') == $priorityKey ? 'selected' : '' }}>{{ $priorityName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1" for="newRuleThreshold">Avisar al quedar (%)</label>
                        <input id="newRuleThreshold" type="number" name="threshold_percent" class="form-control" min="5" max="90" value="{{ old('threshold_percent', 25) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1" for="newRuleOwner">Responsable de escalamiento</label>
                        <select id="newRuleOwner" name="escalate_user_id" class="form-select">
                            <option value="">Admin de Área del área</option>
                            @foreach($allOwners as $owner)
                                <option value="{{ $owner->user_id }}" {{ old('escalate_user_id') == $owner->user_id ? 'selected' : '' }}>{{ $owner->user_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-success"><i class="fas fa-plus me-1"></i>Agregar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection