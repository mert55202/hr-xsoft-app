<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FormIdentity;
use App\Http\Requests\SubmitFormRequest;
use App\Models\Form;
use App\Models\FormLink;
use App\Support\EmployeeAccess;
use App\Support\StoreFormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FormFillController extends Controller
{
    public function index(): View
    {
        $forms = Form::query()->orderBy('name')->get()->filter(function (Form $form): bool {
            return $form->isOpen() && $form->identity !== FormIdentity::Link;
        });

        return view('forms.index', ['forms' => $forms]);
    }

    public function show(Form $form): View
    {
        abort_unless($form->isOpen() && $form->identity !== FormIdentity::Link, 404);

        return $this->fill($form, route('forms.submit', $form));
    }

    public function store(SubmitFormRequest $request, Form $form, StoreFormSubmission $store): RedirectResponse
    {
        abort_unless($form->identity !== FormIdentity::Link, 404);

        $store->store($form, $request->user(), $request);

        return redirect()->route('forms.thanks', $form);
    }

    public function thanks(Form $form): View
    {
        return view('forms.thanks', ['form' => $form]);
    }

    public function publicShow(Form $form): View
    {
        abort_unless($form->isOpen() && $form->identity === FormIdentity::Anonymous, 404);

        return $this->fill($form, route('forms.public.store', $form));
    }

    public function publicStore(SubmitFormRequest $request, Form $form, StoreFormSubmission $store): RedirectResponse
    {
        abort_unless($form->identity === FormIdentity::Anonymous, 404);

        $store->store($form, $request->user(), $request);

        return redirect()->route('forms.public.thanks', $form);
    }

    public function publicThanks(Form $form): View
    {
        abort_unless($form->identity === FormIdentity::Anonymous, 404);

        return view('forms.thanks', ['form' => $form]);
    }

    public function linkShow(string $token): View
    {
        $link = $this->openLink($token);

        return $this->fill($link->form, route('forms.link.store', $token), $link);
    }

    public function linkStore(SubmitFormRequest $request, string $token, StoreFormSubmission $store): RedirectResponse
    {
        $link = $this->openLink($token);
        $store->store($link->form, $link->employee->user, $request, $link);

        return redirect()->route('forms.link.thanks', $token);
    }

    public function linkThanks(string $token): View
    {
        $link = FormLink::query()->where('token', $token)->firstOrFail();

        return view('forms.thanks', ['form' => $link->form]);
    }

    public function preview(Form $form): View
    {
        abort_unless(auth()->user()->can('manage', Form::class), 403);

        return $this->fill($form, route('forms.submit', $form), preview: true);
    }

    private function fill(Form $form, string $action, ?FormLink $link = null, bool $preview = false): View
    {
        $form->load(['sections.questions.options', 'sections.questions.rows']);
        $viewer = $link?->employee?->user ?? auth()->user();

        return view('forms.fill', [
            'form' => $form,
            'action' => $action,
            'preview' => $preview,
            'employees' => $viewer === null
                ? collect()
                : app(EmployeeAccess::class)->visibleQuery($viewer)->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    private function openLink(string $token): FormLink
    {
        $link = FormLink::query()->with(['form', 'employee.user', 'employee.position'])->where('token', $token)->firstOrFail();

        abort_unless(
            $link->used_at === null && $link->form->isOpen() && $link->form->identity === FormIdentity::Link,
            404,
        );

        return $link;
    }
}
