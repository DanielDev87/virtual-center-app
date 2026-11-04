<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    public const TASKS = [
        'tomar_ticket' => 'Tomar un ticket',
        'unirse_equipo' => 'Solicitar unirme a un equipo',
        'actualizar_progreso' => 'Actualizar el progreso',
        'transferir_ticket' => 'Transferir un ticket',
        'flujo_addie' => 'Completar el flujo ADDIE',
        'otra' => 'Otra tarea',
    ];

    public const SCREENS = [
        'dashboard' => ['label' => 'Mis tickets asignados', 'route' => 'contributors.dashboard'],
        'topic_queue' => ['label' => 'Cola de tickets de mi tópico', 'route' => 'contributors.topic-tickets.index'],
        'new_request' => ['label' => 'Nueva solicitud', 'route' => 'service-management.create'],
        'profile' => ['label' => 'Mi perfil', 'route' => 'profile.edit'],
    ];

    protected $table = 'knowledge_articles';
    protected $primaryKey = 'article_id';

    protected $fillable = [
        'title',
        'summary',
        'steps',
        'body',
        'request_type_id',
        'task',
        'keywords',
        'screen_key',
        'owner_id',
        'status',
        'approved_by',
        'approved_at',
        'last_reviewed_at',
        'valid_until',
        'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id', 'type_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Artículos aprobados y vigentes (no vencidos ni retirados).
     */
    public function scopeCurrent($query)
    {
        return $query->where('status', 'approved')
            ->where(function ($w) {
                $w->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            });
    }

    /**
     * Etiqueta y color del estado de vigencia.
     */
    public function validity(): array
    {
        if ($this->status === 'retired') {
            return ['Retirado', 'secondary'];
        }

        if ($this->status !== 'approved') {
            return ['Borrador', 'warning'];
        }

        if ($this->valid_until && $this->valid_until->lt(now()->startOfDay())) {
            return ['Vencido', 'danger'];
        }

        return ['Vigente', 'success'];
    }

    public function isCurrent(): bool
    {
        return $this->validity()[0] === 'Vigente';
    }

    public function stepsList(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $this->steps) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }

    public function screenUrl(): ?string
    {
        $screen = self::SCREENS[$this->screen_key] ?? null;

        if (!$screen) {
            return null;
        }

        try {
            return route($screen['route']);
        } catch (\Throwable $e) {
            return null;
        }
    }
}