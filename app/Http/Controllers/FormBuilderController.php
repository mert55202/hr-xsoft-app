<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FormIdentity;
use App\Enums\FormStatus;
use App\Http\Requests\SaveFormRequest;
use App\Models\Employee;
use App\Models\Form;
use App\Models\FormLink;
use App\Support\EmployeeAccess;
use App\Support\FormTemplates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage', Form::class);
        FormTemplates::install();

        return view('forms.manage', [
            'forms' => Form::query()->withCount('submissions')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage', Form::class);

        return view('forms.create', ['form' => new Form]);
    }

    public function store(SaveFormRequest $request): RedirectResponse
    {
        $form = Form::query()->create($request->formAttributes());
        $form->sections()->create(['title' => 'Sorular', 'sort' => 1]);

        return redirect()->route('forms.edit', $form)->with('status', 'Form açıldı.');
    }

    public function edit(Form $form): View
    {
        Gate::authorize('manage', Form::class);
        $form->load(['sections.questions.options', 'sections.questions.rows', 'links.employee']);

        return view('forms.edit', ['form' => $form]);
    }

    public function update(SaveFormRequest $request, Form $form): RedirectResponse|JsonResponse
    {
        $attributes = $request->formAttributes();

        if (! $form->isDraft()) {
            unset($attributes['identity']);
        }

        $form->update($attributes);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('forms.edit', $form)->with('status', 'Form güncellendi.');
    }

    public function publish(Form $form): RedirectResponse
    {
        Gate::authorize('manage', Form::class);
        abort_unless($form->isDraft(), 404);

        $count = $form->sections()->withCount('questions')->get()->sum('questions_count');

        if ($count < 1) {
            return redirect()->route('forms.edit', $form)->withErrors(['form' => 'Yayın için en az bir soru gerekli.']);
        }

        $form->update(['status' => FormStatus::Published]);

        return redirect()->route('forms.edit', $form)->with('status', 'Form yayında.');
    }

    public function close(Form $form): RedirectResponse
    {
        Gate::authorize('manage', Form::class);
        abort_unless($form->status === FormStatus::Published, 404);
        $form->update(['status' => FormStatus::Closed]);

        return redirect()->route('forms.edit', $form)->with('status', 'Form kapandı.');
    }

    public function clone(Form $form): RedirectResponse
    {
        Gate::authorize('manage', Form::class);
        $form->load(['sections.questions.options', 'sections.questions.rows']);

        $copy = $form->replicate(['public_id']);
        $copy->public_id = null;
        $copy->name = $form->name.' kopya';
        $copy->status = FormStatus::Draft;
        $copy->save();

        foreach ($form->sections as $section) {
            $newSection = $section->replicate();
            $newSection->form_id = $copy->id;
            $newSection->save();

            foreach ($section->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->form_section_id = $newSection->id;
                $newQuestion->save();

                foreach ($question->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->form_question_id = $newQuestion->id;
                    $newOption->save();
                }

                foreach ($question->rows as $row) {
                    $newRow = $row->replicate();
                    $newRow->form_question_id = $newQuestion->id;
                    $newRow->save();
                }
            }
        }

        return redirect()->route('forms.edit', $copy)->with('status', 'Taslak kopya açıldı.');
    }

    public function links(Form $form): RedirectResponse
    {
        Gate::authorize('manage', Form::class);
        abort_unless($form->identity === FormIdentity::Link, 404);

        foreach (Employee::query()->get() as $employee) {
            FormLink::query()->firstOrCreate([
                'form_id' => $form->id,
                'employee_id' => $employee->id,
            ]);
        }

        return redirect()->route('forms.edit', $form)->with('status', 'Kişisel bağlantılar hazır.');
    }

    public function results(Form $form, EmployeeAccess $access): View
    {
        Gate::authorize('manage', Form::class);

        $query = $form->submissions()->with(['answers.option', 'answers.row'])->latest('submitted_at');
        $viewer = auth()->user();

        if (! $viewer->isAdmin()) {
            $query->where('department_id', $viewer->department_id);
        }

        $submissions = $query->get();
        $revealPeople = $form->identity !== FormIdentity::Anonymous;

        if ($revealPeople) {
            $submissions->load('employee');
            $visible = $access->visibleIds($viewer);
            $submissions = $submissions->filter(function ($submission) use ($viewer, $visible) {
                if ($submission->employee === null) {
                    return $viewer->isAdmin();
                }

                return $visible === null || in_array($submission->employee_id, $visible, true);
            })->values();
        }

        $form->load(['sections.questions.options', 'sections.questions.rows']);

        return view('forms.results', [
            'form' => $form,
            'submissions' => $submissions,
            'revealPeople' => $revealPeople,
        ]);
    }
}
