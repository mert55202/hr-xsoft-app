<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\FormIdentity;
use App\Enums\FormStatus;
use App\Enums\QuestionType;
use App\Models\Form;

class FormTemplates
{
    public static function install(): void
    {
        foreach (self::definitions() as $definition) {
            if (Form::query()->where('template', $definition['key'])->exists()) {
                continue;
            }

            $form = Form::query()->create([
                'template' => $definition['key'],
                'name' => $definition['name'],
                'description' => 'Sabit İK şablonu.',
                'thank_you' => 'Kaydın alındı.',
                'identity' => $definition['identity'],
                'status' => FormStatus::Published,
            ]);
            $section = $form->sections()->create(['title' => 'Sorular', 'sort' => 1]);
            $question = $section->questions()->create([
                'type' => $definition['type'],
                'prompt' => $definition['prompt'],
                'required' => true,
                'sort' => 1,
                'config' => $definition['type'] === QuestionType::LinearScale ? ['scale_min' => 1, 'scale_max' => 5] : null,
            ]);

            if ($definition['type']->needsOptions()) {
                $question->options()->createMany([
                    ['label' => 'Evet', 'sort' => 1],
                    ['label' => 'Hayır', 'sort' => 2],
                ]);
            }
        }
    }

    /**
     * @return list<array{key: string, name: string, identity: FormIdentity, type: QuestionType, prompt: string}>
     */
    public static function definitions(): array
    {
        return [
            ['key' => 'training_eval', 'name' => 'Eğitim değerlendirme', 'identity' => FormIdentity::Named, 'type' => QuestionType::LinearScale, 'prompt' => 'Eğitim beklentini karşıladı mı?'],
            ['key' => 'aptitude', 'name' => 'Kişisel genel yetenek', 'identity' => FormIdentity::Named, 'type' => QuestionType::ShortText, 'prompt' => 'Öne çıkan yeteneğin nedir?'],
            ['key' => 'orientation_satisfaction', 'name' => 'Oryantasyon memnuniyet', 'identity' => FormIdentity::Named, 'type' => QuestionType::LinearScale, 'prompt' => 'Oryantasyon yeterli miydi?'],
            ['key' => 'orientation_check', 'name' => 'Oryantasyon kontrol', 'identity' => FormIdentity::Named, 'type' => QuestionType::Checkboxes, 'prompt' => 'Tamamlanan başlıklar'],
            ['key' => 'probation', 'name' => 'Deneme süresi bilgi', 'identity' => FormIdentity::Named, 'type' => QuestionType::Paragraph, 'prompt' => 'Deneme süresi gözlemin'],
            ['key' => 'interview', 'name' => 'İş görüşmesi', 'identity' => FormIdentity::Named, 'type' => QuestionType::Paragraph, 'prompt' => 'Görüşme notu'],
            ['key' => 'language_exam', 'name' => 'Dil sınavı', 'identity' => FormIdentity::Named, 'type' => QuestionType::ShortText, 'prompt' => 'Kısa çeviri'],
            ['key' => 'personality', 'name' => 'Kişilik envanteri', 'identity' => FormIdentity::Named, 'type' => QuestionType::LinearScale, 'prompt' => 'Takım çalışması'],
            ['key' => 'reference_check', 'name' => 'Referans araştırma', 'identity' => FormIdentity::Named, 'type' => QuestionType::Paragraph, 'prompt' => 'Referans özeti'],
            ['key' => 'exit_employee', 'name' => 'Personel çıkış görüşmesi', 'identity' => FormIdentity::Link, 'type' => QuestionType::Paragraph, 'prompt' => 'Ayrılma gerekçen nedir?'],
            ['key' => 'exit_company', 'name' => 'Firma çıkış görüşmesi', 'identity' => FormIdentity::Link, 'type' => QuestionType::Paragraph, 'prompt' => 'Ayrılış sürecini nasıl değerlendirirsin?'],
            ['key' => 'monthly_performance', 'name' => 'Aylık performans', 'identity' => FormIdentity::Named, 'type' => QuestionType::LinearScale, 'prompt' => 'Bu ayın iş sonucu'],
        ];
    }
}
