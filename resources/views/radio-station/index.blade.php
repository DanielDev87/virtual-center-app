@extends('layouts.app')

@section('title', 'Emisora Virtual - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Emisora Virtual</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshPlaylist()">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showPlaylist()">
                    <i class="fas fa-list me-1"></i>Playlist
                </button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-success" onclick="startStream()">
                    <i class="fas fa-play me-1"></i>Iniciar Transmisión
                </button>
            </div>
        </div>
    </div>

    <!-- Radio Player -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reproductor de Radio</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="radio-station-logo mb-3">
                                <i class="fas fa-radio fa-5x text-primary"></i>
                            </div>
                            <h4 class="text-primary">Virtual Center Radio</h4>
                            <p class="text-muted">Transmisión en vivo</p>
                        </div>
                        <div class="col-md-8">
                            <div class="audio-player">
                                <audio id="radioPlayer" controls class="w-100 mb-3">
                                    <source src="{{ $currentStream->stream_url ?? '#' }}" type="audio/mpeg">
                                    Tu navegador no soporta el elemento de audio.
                                </audio>
                                
                                <div class="player-controls">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="previousTrack()">
                                            <i class="fas fa-step-backward"></i>
                                        </button>
                                        <button type="button" class="btn btn-primary" id="playPauseBtn" onclick="togglePlayPause()">
                                            <i class="fas fa-play" id="playPauseIcon"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="nextTrack()">
                                            <i class="fas fa-step-forward"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="toggleMute()">
                                            <i class="fas fa-volume-up" id="muteIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="volume-control mt-3">
                                    <label for="volumeSlider" class="form-label">Volumen</label>
                                    <input type="range" class="form-range" id="volumeSlider" min="0" max="100" value="50" onchange="setVolume(this.value)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de la Transmisión</h5>
                </div>
                <div class="card-body">
                    <div class="stream-info">
                        <div class="mb-3">
                            <h6 class="fw-bold">Programa Actual:</h6>
                            <p class="text-primary" id="currentProgram">{{ $currentProgram->program_name ?? 'Programa Musical' }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Canción Actual:</h6>
                            <p class="text-muted" id="currentTrack">{{ $currentTrack->track_name ?? 'Seleccionando música...' }}</p>
                            <small class="text-muted" id="currentArtist">{{ $currentTrack->artist ?? 'Artista' }}</small>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Próxima Canción:</h6>
                            <p class="text-muted" id="nextTrack">{{ $nextTrack->track_name ?? 'Siguiente en playlist...' }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Estado:</h6>
                            <span class="badge bg-success" id="streamStatus">En Vivo</span>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Oyentes:</h6>
                            <p class="text-info" id="listenerCount">{{ $listenerCount ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Schedule -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Programación</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Hora</th>
                                    <th>Programa</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programSchedule as $program)
                                <tr class="{{ $program->is_current ? 'table-active' : '' }}">
                                    <td>{{ $program->start_time }} - {{ $program->end_time }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $program->program_name }}</div>
                                        <small class="text-muted">{{ $program->host_name }}</small>
                                    </td>
                                    <td>{{ $program->description }}</td>
                                    <td>
                                        @if($program->is_current)
                                            <span class="badge bg-success">En Vivo</span>
                                        @elseif($program->is_upcoming)
                                            <span class="badge bg-warning">Próximo</span>
                                        @else
                                            <span class="badge bg-secondary">Finalizado</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay programación disponible</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tracks -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Canciones Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($recentTracks as $track)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $track->track_name }}</div>
                                <small class="text-muted">{{ $track->artist }} - {{ $track->album }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">{{ $track->played_at->format('H:i') }}</small>
                                <div>
                                    <span class="badge bg-primary">{{ $track->duration }}</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-music fa-3x mb-3"></i>
                            <p>No hay canciones recientes</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Canciones</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($topTracks as $index => $track)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $track->track_name }}</div>
                                    <small class="text-muted">{{ $track->artist }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">{{ $track->play_count }}</div>
                                <small class="text-muted">reproducciones</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-trophy fa-3x mb-3"></i>
                            <p>No hay estadísticas disponibles</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Playlist Modal -->
<div class="modal fade" id="playlistModal" tabindex="-1" aria-labelledby="playlistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playlistModalLabel">Playlist Actual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Canción</th>
                                <th>Artista</th>
                                <th>Duración</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="playlistTable">
                            @forelse($playlist as $index => $track)
                            <tr class="{{ $track->is_current ? 'table-active' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $track->track_name }}</td>
                                <td>{{ $track->artist }}</td>
                                <td>{{ $track->duration }}</td>
                                <td>
                                    @if($track->is_current)
                                        <span class="badge bg-success">Reproduciendo</span>
                                    @elseif($track->is_played)
                                        <span class="badge bg-secondary">Reproducida</span>
                                    @else
                                        <span class="badge bg-warning">En Cola</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Playlist vacía</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="refreshPlaylist()">Actualizar Playlist</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let isPlaying = false;
let isMuted = false;
let currentVolume = 50;

$(document).ready(function() {
    const player = document.getElementById('radioPlayer');
    
    // Set initial volume
    player.volume = currentVolume / 100;
    
    // Player event listeners
    player.addEventListener('play', function() {
        isPlaying = true;
        updatePlayPauseButton();
    });
    
    player.addEventListener('pause', function() {
        isPlaying = false;
        updatePlayPauseButton();
    });
    
    player.addEventListener('ended', function() {
        nextTrack();
    });
    
    // Auto-refresh every 30 seconds
    setInterval(refreshStreamInfo, 30000);
});

function togglePlayPause() {
    const player = document.getElementById('radioPlayer');
    
    if (isPlaying) {
        player.pause();
    } else {
        player.play();
    }
}

function updatePlayPauseButton() {
    const icon = document.getElementById('playPauseIcon');
    const btn = document.getElementById('playPauseBtn');
    
    if (isPlaying) {
        icon.className = 'fas fa-pause';
        btn.className = 'btn btn-warning';
    } else {
        icon.className = 'fas fa-play';
        btn.className = 'btn btn-primary';
    }
}

function previousTrack() {
    VirtualCenter.showAlert('Canción anterior', 'info', 2000);
    // Here you would implement previous track logic
}

function nextTrack() {
    VirtualCenter.showAlert('Siguiente canción', 'info', 2000);
    // Here you would implement next track logic
}

function toggleMute() {
    const player = document.getElementById('radioPlayer');
    const icon = document.getElementById('muteIcon');
    const slider = document.getElementById('volumeSlider');
    
    if (isMuted) {
        player.volume = currentVolume / 100;
        slider.value = currentVolume;
        icon.className = 'fas fa-volume-up';
        isMuted = false;
    } else {
        currentVolume = player.volume * 100;
        player.volume = 0;
        slider.value = 0;
        icon.className = 'fas fa-volume-mute';
        isMuted = true;
    }
}

function setVolume(value) {
    const player = document.getElementById('radioPlayer');
    const icon = document.getElementById('muteIcon');
    
    player.volume = value / 100;
    currentVolume = value;
    
    if (value == 0) {
        icon.className = 'fas fa-volume-mute';
        isMuted = true;
    } else {
        icon.className = 'fas fa-volume-up';
        isMuted = false;
    }
}

function startStream() {
    VirtualCenter.showAlert('Iniciando transmisión...', 'info', 2000);
    // Here you would implement stream start logic
}

function refreshPlaylist() {
    VirtualCenter.showAlert('Actualizando playlist...', 'info', 2000);
    // Here you would implement playlist refresh logic
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function showPlaylist() {
    $('#playlistModal').modal('show');
}

function refreshStreamInfo() {
    // Here you would make an AJAX call to get updated stream info
    // For now, we'll just show a subtle notification
    console.log('Refreshing stream info...');
}
</script>
@endpush


