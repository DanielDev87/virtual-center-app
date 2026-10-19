<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestType;
use App\Models\RequestTypeQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RequestTypeQuestionController extends Controller
{
    private const MAX_QUESTIONS_PER_TOPIC = 30;

    public function store(Request $request, $id)
    {
        $requestType = RequestType::findOrFail($id);

        $currentCount = RequestTypeQuestion::where('request_type_id', $requestType->type_id)->count();
        if ($currentCount >= self::MAX_QUESTIONS_PER_TOPIC) {
            return $this->backToTopic(
                $requestType->type_id,
                'question_error',
                'Se alcanzó el máximo de ' . self::MAX_QUESTIONS_PER_TOPIC . ' preguntas por tópico.'
            );
        }

        $data = $this->validatedData($request);
        $data['request_type_id'] = $requestType->type_id;
        $data['is_active'] = true;

        if ($data['sort_order'] === null) {
            $data['sort_order'] = (int) RequestTypeQuestion::where('request_type_id', $requestType->type_id)->max('sort_order') + 1;
        }

        RequestTypeQuestion::create($data);

        return $this->backToTopic($requestType->type_id, 'question_success', 'Pregunta agregada correctamente.');
    }

    public function update(Request $request, $id, $questionId)
    {
        $question = RequestTypeQuestion::where('request_type_id', $id)
            ->where('question_id', $questionId)
            ->firstOrFail();

        $data = $this->validatedData($request);
        $data['is_active'] = $request->boolean('is_active');

        if ($data['sort_order'] === null) {
            unset($data['sort_order']);
        }

        $question->update($data);

        return $this->backToTopic($question->request_type_id, 'question_success', 'Pregunta actualizada correctamente.');
    }

    public function destroy($id, $questionId)
    {
        $question = RequestTypeQuestion::where('request_type_id', $id)
            ->where('question_id', $questionId)
            ->firstOrFail();

        $question->delete();

        return $this->backToTopic($id, 'question_success', 'Pregunta eliminada. Las respuestas de tickets anteriores se conservan.');
    }

    /**
     * Valida y normaliza los datos de una pregunta.
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validateWithBag('question', [
            'label' => 'required|string|max:255',
            'field_type' => 'required|in:' . implode(',', array_keys(RequestTypeQuestion::FIELD_TYPES)),
            'options' => 'nullable|string|max:3000',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ], [
            'label.required' => 'Escribe el texto de la pregunta.',
            'label.max' => 'La pregunta no puede superar 255 caracteres.',
            'field_type.required' => 'Selecciona el tipo de respuesta.',
            'field_type.in' => 'El tipo de respuesta no es válido.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
        ]);

        $options = null;

        if ($validated['field_type'] === 'select') {
            $options = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['options'] ?? '')))
                ->map(fn ($option) => trim($option))
                ->filter()
                ->unique()
                ->values();

            if ($options->count() < 2
                || $options->count() > 50
                || $options->contains(fn ($option) => mb_strlen($option) > 100)) {
                throw ValidationException::withMessages([
                    'options' => 'Para una lista de opciones escribe entre 2 y 50 opciones, una por línea, de máximo 100 caracteres cada una.',
                ])->errorBag('question');
            }

            $options = $options->all();
        }

        return [
            'label' => trim($validated['label']),
            'field_type' => $validated['field_type'],
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
        ];
    }

    private function backToTopic($requestTypeId, string $flashKey, string $message)
    {
        return redirect(route('admin.request-types.edit', $requestTypeId) . '#preguntas')
            ->with($flashKey, $message);
    }
}