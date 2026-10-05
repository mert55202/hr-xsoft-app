<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Http\Requests\StoreFormQuestionRequest;
use App\Http\Requests\UpdateFormQuestionRequest;
use App\Models\Form;
use App\Models\FormOption;
use App\Models\FormQuestion;
use App\Models\FormRow;
use App\Models\FormSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FormStructureController extends Controller
{
    public function storeSection(Request $request, Form $form): RedirectResponse
    {
        $this->draft($form);
        $data = $request->validate(['title' => ['required', 'string', 'max:160']]);
        $form->sections()->create([
            'title' => $data['title'],
            'sort' => ((int) $form->sections()->max('sort')) + 1,
        ]);

        return back();
    }

    public function updateSection(Request $request, Form $form, FormSection $section): JsonResponse
    {
        $this->draft($form);
        abort_unless($section->form_id === $form->id, 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $section->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function storeQuestion(StoreFormQuestionRequest $request, Form $form, FormSection $section): RedirectResponse
    {
        abort_unless($section->form_id === $form->id, 404);
        $question = $section->questions()->create($request->questionAttributes($section));
        $this->seedShape($question);

        return redirect()->route('forms.edit', $form)->with('focus', $question->id);
    }

    public function updateQuestion(UpdateFormQuestionRequest $request, Form $form, FormQuestion $question): JsonResponse
    {
        abort_unless($question->section?->form_id === $form->id, 404);
        $type = QuestionType::from($request->string('type')->toString());
        $typeChanged = $question->type !== $type;
        $config = $question->config ?? [];

        if ($type === QuestionType::LinearScale) {
            $config['scale_min'] = $request->filled('scale_min') ? $request->integer('scale_min') : ($config['scale_min'] ?? 1);
            $config['scale_max'] = $request->filled('scale_max') ? $request->integer('scale_max') : ($config['scale_max'] ?? 5);
        }

        $question->update([
            'prompt' => $request->string('prompt')->toString(),
            'help' => $request->filled('help') ? $request->string('help')->toString() : null,
            'type' => $type,
            'required' => $request->boolean('required'),
            'config' => $config === [] ? null : $config,
        ]);
        $this->seedShape($question->fresh());

        return response()->json(['ok' => true, 'reload' => $typeChanged]);
    }

    public function duplicateQuestion(Form $form, FormQuestion $question): RedirectResponse
    {
        $this->draft($form);
        abort_unless($question->section?->form_id === $form->id, 404);
        $question->load(['options', 'rows']);

        $copy = $question->replicate();
        $copy->prompt = $question->prompt;
        $copy->sort = ((int) $question->section->questions()->max('sort')) + 1;
        $copy->save();

        foreach ($question->options as $option) {
            $newOption = $option->replicate();
            $newOption->form_question_id = $copy->id;
            $newOption->save();
        }

        foreach ($question->rows as $row) {
            $newRow = $row->replicate();
            $newRow->form_question_id = $copy->id;
            $newRow->save();
        }

        return redirect()->route('forms.edit', $form)->with('focus', $copy->id);
    }

    public function destroyQuestion(Form $form, FormQuestion $question): RedirectResponse
    {
        $this->draft($form);
        abort_unless($question->section?->form_id === $form->id, 404);
        $question->delete();

        return back();
    }

    public function storeOption(Request $request, Form $form, FormQuestion $question): RedirectResponse
    {
        $this->draft($form);
        abort_unless($question->section?->form_id === $form->id && $question->type->needsOptions(), 404);
        $data = $request->validate([
            'label' => ['required', 'string', 'max:200'],
            'points' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
        $question->options()->create([
            'label' => $data['label'],
            'points' => $data['points'] ?? null,
            'sort' => ((int) $question->options()->max('sort')) + 1,
        ]);

        return back();
    }

    public function updateOption(Request $request, Form $form, FormOption $option): JsonResponse
    {
        $this->draft($form);
        abort_unless($option->question?->section?->form_id === $form->id, 404);
        $data = $request->validate(['label' => ['required', 'string', 'max:200']]);
        $option->update(['label' => $data['label']]);

        return response()->json(['ok' => true]);
    }

    public function destroyOption(Form $form, FormOption $option): RedirectResponse
    {
        $this->draft($form);
        abort_unless($option->question?->section?->form_id === $form->id, 404);
        $option->delete();

        return back();
    }

    public function storeRow(Request $request, Form $form, FormQuestion $question): RedirectResponse
    {
        $this->draft($form);
        abort_unless($question->section?->form_id === $form->id && $question->type->needsRows(), 404);
        $data = $request->validate(['label' => ['required', 'string', 'max:200']]);
        $question->rows()->create([
            'label' => $data['label'],
            'sort' => ((int) $question->rows()->max('sort')) + 1,
        ]);

        return back();
    }

    public function updateRow(Request $request, Form $form, FormRow $row): JsonResponse
    {
        $this->draft($form);
        abort_unless($row->question?->section?->form_id === $form->id, 404);
        $data = $request->validate(['label' => ['required', 'string', 'max:200']]);
        $row->update(['label' => $data['label']]);

        return response()->json(['ok' => true]);
    }

    public function destroyRow(Form $form, FormRow $row): RedirectResponse
    {
        $this->draft($form);
        abort_unless($row->question?->section?->form_id === $form->id, 404);
        $row->delete();

        return back();
    }

    public function move(Request $request, Form $form, FormQuestion $question): RedirectResponse
    {
        $this->draft($form);
        abort_unless($question->section?->form_id === $form->id, 404);
        $direction = $request->string('direction')->toString();
        $siblings = $question->section->questions()->orderBy('sort')->get()->values();
        $index = $siblings->search(fn (FormQuestion $item): bool => $item->is($question));
        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        $neighbor = $siblings->get($swap);

        if ($neighbor instanceof FormQuestion) {
            $current = $question->sort;
            $question->update(['sort' => $neighbor->sort]);
            $neighbor->update(['sort' => $current]);
        }

        return back();
    }

    public function reorder(Request $request, Form $form, FormSection $section): JsonResponse
    {
        $this->draft($form);
        abort_unless($section->form_id === $form->id, 404);
        $data = $request->validate([
            'question_ids' => ['present', 'array'],
            'question_ids.*' => ['integer'],
        ]);

        foreach ($data['question_ids'] as $index => $id) {
            FormQuestion::query()
                ->whereKey($id)
                ->whereHas('section', fn ($query) => $query->where('form_id', $form->id))
                ->update([
                    'form_section_id' => $section->id,
                    'sort' => $index + 1,
                ]);
        }

        return response()->json(['ok' => true]);
    }

    public function reorderOptions(Request $request, Form $form, FormQuestion $question): JsonResponse
    {
        $this->draft($form);
        abort_unless($question->section?->form_id === $form->id, 404);
        $data = $request->validate([
            'option_ids' => ['present', 'array'],
            'option_ids.*' => ['integer'],
        ]);

        foreach ($data['option_ids'] as $index => $id) {
            FormOption::query()
                ->where('form_question_id', $question->id)
                ->whereKey($id)
                ->update(['sort' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }

    private function seedShape(FormQuestion $question): void
    {
        if ($question->type->needsOptions() && $question->options()->count() === 0) {
            $question->options()->createMany([
                ['label' => 'Seçenek 1', 'sort' => 1],
                ['label' => 'Seçenek 2', 'sort' => 2],
            ]);
        }

        if ($question->type->needsRows() && $question->rows()->count() === 0) {
            $question->rows()->create(['label' => 'Satır 1', 'sort' => 1]);
        }
    }

    private function draft(Form $form): void
    {
        Gate::authorize('manage', Form::class);
        abort_unless($form->isDraft(), 404);
    }
}
