@extends($kbLayout)

@section('title', $article->title)

@section('content')
@php
    [$validityLabel, $validityColor] = $article->validity();
    $screenUrl = $article->screenUrl();
@endphp
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
        <h1 class="h3 mb-0">{{ $article->title }}</h1>
        <a href="{{ route('kb.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver a la base de conocimiento
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless($article->isCurrent())
        <div class="alert alert-warning">
            <i class="fas fa-triangle-exclamation me-1"></i>
            <strong>{{ $validityLabel }}:</strong> este artículo no es una instrucción vigente y los Contributor no lo ven.
        </div>
    @endunless

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    @if($article->summary)
                        <p class="lead">{{ $article->summary }}</p>
                    @endif

                    <h2 class="h5">Pasos</h2>
                    <ol class="mb-3">
                        @foreach($article->stepsList() as $step)
                            <li class="mb-1">{{ $step }}</li>
                        @endforeach
                    </ol>

                    @if($article->body)
                        <h2 class="h5">Detalle</h2>
                        <div>{!! nl2br(e($article->body)) !!}</div>
                    @endif

                    @if($screenUrl)
                        <hr>
                        <a href="{{ $screenUrl }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-up-right-from-square me-1"></i>Ir a: {{ \App\Models\KnowledgeArticle::SCREENS[$article->screen_key]['label'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body small">
                    <p class="mb-2"><span class="badge bg-{{ $validityColor }}">{{ $validityLabel }}</span></p>
                    <p class="mb-1"><strong>Tópico:</strong> {{ optional($article->requestType)->type_name ?? 'General' }}</p>
                    <p class="mb-1"><strong>Tarea:</strong> {{ $tasks[$article->task] ?? '—' }}</p>
                    <p class="mb-1"><strong>Responsable:</strong> {{ optional($article->owner)->user_name ?? '—' }}</p>
                    <p class="mb-1"><strong>Última revisión:</strong> {{ $article->last_reviewed_at ? $article->last_reviewed_at->format('d/m/Y') : 'Sin revisar' }}</p>
                    <p class="mb-1"><strong>Vigente hasta:</strong> {{ $article->valid_until ? $article->valid_until->format('d/m/Y') : 'Sin fecha límite' }}</p>
                    @if($article->approved_at)
                        <p class="mb-1"><strong>Aprobado por:</strong> {{ optional($article->approver)->user_name ?? '—' }} ({{ $article->approved_at->format('d/m/Y') }})</p>
                    @endif
                    @if($article->keywords)
                        <p class="mb-0"><strong>Palabras clave:</strong> {{ $article->keywords }}</p>
                    @endif
                </div>
            </div>

            @if($canManage)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h6">Gestión del artículo</h2>
                        <a href="{{ route('kb.edit', $article->article_id) }}" class="btn btn-sm btn-outline-primary mb-2 w-100">
                            <i class="fas fa-pen me-1"></i>Editar
                        </a>

                        @if($article->status !== 'retired')
                            <form action="{{ route('kb.approve', $article->article_id) }}" method="POST" class="mb-2">
                                @csrf
                                <label for="validUntil" class="form-label small mb-1">Vigente hasta (opcional)</label>
                                <input id="validUntil" type="date" name="valid_until" class="form-control form-control-sm mb-2"
                                       value="{{ $article->valid_until ? $article->valid_until->format('Y-m-d') : '' }}">
                                <button type="submit" class="btn btn-sm btn-success w-100">
                                    <i class="fas fa-check me-1"></i>{{ $article->status === 'approved' ? 'Renovar aprobación' : 'Aprobar' }}
                                </button>
                            </form>
                            <form action="{{ route('kb.retire', $article->article_id) }}" method="POST"
                                  onsubmit="return confirm('¿Retirar este artículo? Dejará de mostrarse como instrucción vigente.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="fas fa-ban me-1"></i>Retirar
                                </button>
                            </form>
                        @else
                            <form action="{{ route('kb.approve', $article->article_id) }}" method="POST">
                                @csrf
                                <label for="validUntil" class="form-label small mb-1">Vigente hasta (opcional)</label>
                                <input id="validUntil" type="date" name="valid_until" class="form-control form-control-sm mb-2">
                                <button type="submit" class="btn btn-sm btn-success w-100">
                                    <i class="fas fa-rotate-left me-1"></i>Volver a aprobar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection