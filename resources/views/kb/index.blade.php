@extends($kbLayout)

@section('title', 'Base de conocimiento')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
        <h1 class="h3 mb-0">Base de conocimiento</h1>
        @if($manager)
            <a href="{{ route('kb.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Nuevo artículo
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="GET" action="{{ route('kb.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="kbSearch" class="form-label small fw-semibold mb-1"><i class="fas fa-search me-1"></i>Buscar</label>
                <input id="kbSearch" type="text" name="q" class="form-control" maxlength="100" value="{{ request('q') }}" placeholder="Palabras clave">
            </div>
            <div class="col-md-3">
                <label for="kbTopic" class="form-label small fw-semibold mb-1"><i class="fas fa-tags me-1"></i>Tópico</label>
                <select id="kbTopic" name="request_type_id" class="form-select">
                    <option value="">Todos los tópicos</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->type_id }}" {{ (string) request('request_type_id') === (string) $topic->type_id ? 'selected' : '' }}>{{ $topic->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="kbTask" class="form-label small fw-semibold mb-1"><i class="fas fa-list-check me-1"></i>Tarea</label>
                <select id="kbTask" name="task" class="form-select">
                    <option value="">Todas las tareas</option>
                    @foreach($tasks as $taskKey => $taskLabel)
                        <option value="{{ $taskKey }}" {{ request('task') === $taskKey ? 'selected' : '' }}>{{ $taskLabel }}</option>
                    @endforeach
                </select>
            </div>
            @if($manager)
                <div class="col-md-2">
                    <label for="kbStatus" class="form-label small fw-semibold mb-1">Estado</label>
                    <select id="kbStatus" name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="current" {{ request('status') === 'current' ? 'selected' : '' }}>Vigentes</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borradores</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Vencidos</option>
                        <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retirados</option>
                    </select>
                </div>
            @endif
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Buscar</button>
                <a href="{{ route('kb.index') }}" class="btn btn-outline-secondary"><i class="fas fa-eraser me-1"></i>Limpiar filtros</a>
            </div>
        </div>
        <div class="form-text mt-2">Al filtrar por tópico también se muestran los artículos generales.</div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Artículo</th>
                        <th>Tópico</th>
                        <th>Tarea</th>
                        <th>Responsable</th>
                        <th>Última revisión</th>
                        <th>Vigencia</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $article)
                        @php [$validityLabel, $validityColor] = $article->validity(); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $article->title }}</div>
                                @if($article->summary)
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($article->summary, 110) }}</div>
                                @endif
                            </td>
                            <td>{{ optional($article->requestType)->type_name ?? 'General' }}</td>
                            <td>{{ $tasks[$article->task] ?? '—' }}</td>
                            <td>{{ optional($article->owner)->user_name ?? '—' }}</td>
                            <td>{{ $article->last_reviewed_at ? $article->last_reviewed_at->format('d/m/Y') : 'Sin revisar' }}</td>
                            <td>
                                <span class="badge bg-{{ $validityColor }}">{{ $validityLabel }}</span>
                                @if($article->valid_until)
                                    <div class="small text-muted">hasta {{ $article->valid_until->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kb.show', $article->article_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                @if(request()->hasAny(['q', 'request_type_id', 'task', 'status']))
                                    <i class="fas fa-search fa-2x mb-2"></i>
                                    <p class="mb-2">No se encontraron artículos con los filtros aplicados.</p>
                                    <a href="{{ route('kb.index') }}" class="btn btn-sm btn-outline-secondary">Quitar filtros</a>
                                @else
                                    <i class="fas fa-book-open fa-2x mb-2"></i>
                                    <p class="mb-0">Aún no hay artículos disponibles.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($articles->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $articles->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection