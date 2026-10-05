<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Form;
use App\Models\FormLink;
use App\Support\FormAnswerRules;
use Illuminate\Foundation\Http\FormRequest;

class SubmitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        $form = $this->form();

        return $form instanceof Form && $form->isOpen();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $form = $this->form();

        return $form instanceof Form ? app(FormAnswerRules::class)->for($form) : [];
    }

    private function form(): ?Form
    {
        $form = $this->route('form');

        if ($form instanceof Form) {
            return $form;
        }

        $token = $this->route('token');

        if (! is_string($token)) {
            return null;
        }

        return FormLink::query()->where('token', $token)->first()?->form;
    }
}
