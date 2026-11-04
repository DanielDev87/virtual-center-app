<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\RequestType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KnowledgeBaseController extends Controller
{
    private function roleName(): string
    {
        return Auth::user()->role->role_name ?? '';
    }

    private function isAdmin(): bool
    {
        return $this->roleName() === 'Admin';
    }

    private function isAreaAdmin(): bool
    {
        return $this->roleName() === 'Admin Área';
    }

    private function canManageAny(): bool
    {
        return $this->isAdmin() || $this->isAreaAdmin();
    }

    private function layoutFor(): string
    {
        return match ($this->roleName()) {
            'Admin' => 'layouts.admin',
            'Admin Área' => 'layouts.area-admin',
            default => 'layouts.contributor',
        };
    }

    /**
     * Tópicos que la persona puede usar al crear o editar artículos.
     */
    private function allowedTopics()
    {
        $query = RequestType::where('is_active', true)->orderBy('type_name');

        if ($this->isAreaAdmin()) {
            if (empty(Auth::user()->area_id)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('area_id', Auth::user()->area_id);
            }
        }

        return $query->get(['type_id', 'type_name', 'area_id']);
    }

    private function canManage(?KnowledgeArticle $article): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isAreaAdmin() || empty(Auth::user()->area_id)) {
            return false;
        }

        if ($article === null) {
            return true;
        }

        return $article->request_type_id
            && RequestType::where('type_id', $article->request_type_id)
                ->where('area_id', Auth::user()->area_id)
                ->exists();
    }

    public function index(Request $request)
    {
        $manager = $this->canManageAny();
        $query = KnowledgeArticle::with(['requestType', 'owner']);

        if (!$manager) {
            $query->current();
        } else {
            $status = $request->input('status');
            if ($status === 'current') {
                $query->current();
            } elseif ($status === 'expired') {
                $query->where('status', 'approved')->whereDate('valid_until', '<', now()->toDateString());
            } elseif (in_array($status, ['draft', 'retired'], true)) {
                $query->where('status', $status);
            }
        }

        $search = mb_substr(trim((string) $request->input('q', '')), 0, 100);
        if ($search !== '') {
            $terms = collect(preg_split('/\s+/u', mb_strtolower($search)) ?: [])
                ->filter(fn ($term) => mb_strlen($term) >= 3)
                ->unique()
                ->take(6)
                ->values();

            if ($terms->isEmpty()) {
                $terms = collect([$search]);
            }

            $query->where(function ($w) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%' . addcslashes($term, '%_\\') . '%';
                    $w->orWhere('title', 'like', $like)
                        ->orWhere('summary', 'like', $like)
                        ->orWhere('keywords', 'like', $like)
                        ->orWhere('steps', 'like', $like)
                        ->orWhere('body', 'like', $like);
                }
            });
        }

        if ($request->filled('request_type_id')) {
            $topicId = (int) $request->input('request_type_id');
            $query->where(function ($w) use ($topicId) {
                $w->where('request_type_id', $topicId)->orWhereNull('request_type_id');
            });
        }

        if ($request->filled('task') && array_key_exists($request->input('task'), KnowledgeArticle::TASKS)) {
            $query->where('task', $request->input('task'));
        }

        $articles = $query->orderByDesc('updated_at')->paginate(10)->withQueryString();

        return view('kb.index', [
            'kbLayout' => $this->layoutFor(),
            'articles' => $articles,
            'manager' => $manager,
            'topics' => RequestType::where('is_active', true)->orderBy('type_name')->get(['type_id', 'type_name']),
            'tasks' => KnowledgeArticle::TASKS,
        ]);
    }

    public function show($id)
    {
        $article = KnowledgeArticle::with(['requestType', 'owner', 'approver'])->findOrFail($id);

        if (!$this->canManageAny() && !$article->isCurrent()) {
            abort(404);
        }

        return view('kb.show', [
            'kbLayout' => $this->layoutFor(),
            'article' => $article,
            'canManage' => $this->canManage($article),
            'tasks' => KnowledgeArticle::TASKS,
        ]);
    }

    public function create()
    {
        abort_unless($this->canManageAny(), 403);

        return view('kb.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageAny(), 403);

        $data = $request->validate($this->rules(), $this->messages());
        $data['owner_id'] = $data['owner_id'] ?? Auth::id();

        $article = KnowledgeArticle::create($data + [
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('kb.show', $article->article_id)
            ->with('success', 'Artículo creado como borrador. Apruébalo para que los Contributor puedan consultarlo.');
    }

    public function edit($id)
    {
        $article = KnowledgeArticle::findOrFail($id);
        abort_unless($this->canManage($article), 403);

        return view('kb.form', $this->formData($article));
    }

    public function update(Request $request, $id)
    {
        $article = KnowledgeArticle::findOrFail($id);
        abort_unless($this->canManage($article), 403);

        $data = $request->validate($this->rules(), $this->messages());
        $data['owner_id'] = $data['owner_id'] ?? $article->owner_id ?? Auth::id();

        $wasApproved = $article->status === 'approved';
        $article->fill($data);

        if ($wasApproved) {
            // Un artículo editado necesita una nueva aprobación.
            $article->status = 'draft';
            $article->approved_by = null;
            $article->approved_at = null;
        }

        $article->save();

        return redirect()->route('kb.show', $article->article_id)
            ->with('success', $wasApproved
                ? 'Artículo actualizado. Volvió a borrador y requiere una nueva aprobación.'
                : 'Artículo actualizado.');
    }

    public function approve(Request $request, $id)
    {
        $article = KnowledgeArticle::findOrFail($id);
        abort_unless($this->canManage($article), 403);

        $data = $request->validate(['valid_until' => 'nullable|date'], [
            'valid_until.date' => 'La fecha de vigencia no es válida.',
        ]);

        $validUntil = !empty($data['valid_until']) ? $data['valid_until'] : $article->valid_until;

        if ($validUntil && \Carbon\Carbon::parse($validUntil)->lt(now()->startOfDay())) {
            return back()->withErrors(['valid_until' => 'Indica una fecha de vigencia igual o posterior a hoy.']);
        }

        $article->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'last_reviewed_at' => now(),
            'valid_until' => $validUntil,
        ]);

        return back()->with('success', 'Artículo aprobado. Ya está disponible para los Contributor.');
    }

    public function retire($id)
    {
        $article = KnowledgeArticle::findOrFail($id);
        abort_unless($this->canManage($article), 403);

        $article->update(['status' => 'retired']);

        return back()->with('success', 'Artículo retirado. Ya no se muestra como instrucción vigente.');
    }

    private function formData(?KnowledgeArticle $article): array
    {
        return [
            'kbLayout' => $this->layoutFor(),
            'article' => $article,
            'topics' => $this->allowedTopics(),
            'tasks' => KnowledgeArticle::TASKS,
            'screens' => KnowledgeArticle::SCREENS,
            'isAreaAdmin' => $this->isAreaAdmin(),
            'owners' => User::where('is_active', true)
                ->whereHas('role', fn ($q) => $q->whereIn('role_name', ['Admin', 'Admin Área', 'Contributor']))
                ->orderBy('user_name')
                ->get(['user_id', 'user_name']),
        ];
    }

    private function rules(): array
    {
        $topicIds = $this->allowedTopics()->pluck('type_id')->all();

        return [
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'steps' => 'required|string|max:5000',
            'body' => 'nullable|string|max:8000',
            'request_type_id' => $this->isAreaAdmin()
                ? ['required', Rule::in($topicIds)]
                : ['nullable', Rule::in($topicIds)],
            'task' => ['nullable', Rule::in(array_keys(KnowledgeArticle::TASKS))],
            'keywords' => 'nullable|string|max:500',
            'screen_key' => ['nullable', Rule::in(array_keys(KnowledgeArticle::SCREENS))],
            'owner_id' => 'nullable|integer|exists:users,user_id',
            'valid_until' => 'nullable|date',
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Escribe el título del artículo.',
            'steps.required' => 'Escribe los pasos del procedimiento, uno por línea.',
            'steps.max' => 'Los pasos no pueden superar 5000 caracteres.',
            'request_type_id.required' => 'Selecciona el tópico del artículo.',
            'request_type_id.in' => 'No puedes crear artículos para ese tópico.',
            'task.in' => 'La tarea seleccionada no es válida.',
            'screen_key.in' => 'La pantalla seleccionada no es válida.',
            'valid_until.date' => 'La fecha de vigencia no es válida.',
        ];
    }
}