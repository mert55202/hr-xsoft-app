<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case ShortText = 'short_text';
    case Paragraph = 'paragraph';
    case MultipleChoice = 'multiple_choice';
    case Checkboxes = 'checkboxes';
    case Dropdown = 'dropdown';
    case LinearScale = 'linear_scale';
    case ChoiceGrid = 'choice_grid';
    case CheckboxGrid = 'checkbox_grid';
    case Date = 'date';
    case Time = 'time';
    case EmployeePick = 'employee';
    case File = 'file';

    public function label(): string
    {
        return match ($this) {
            self::ShortText => 'Kısa yanıt',
            self::Paragraph => 'Paragraf',
            self::MultipleChoice => 'Çoktan seçmeli',
            self::Checkboxes => 'Onay kutuları',
            self::Dropdown => 'Açılır menü',
            self::LinearScale => 'Doğrusal ölçek',
            self::ChoiceGrid => 'Çoktan seçmeli tablo',
            self::CheckboxGrid => 'Onay kutusu tablosu',
            self::Date => 'Tarih',
            self::Time => 'Saat',
            self::EmployeePick => 'Personel seçimi',
            self::File => 'Dosya',
        };
    }

    public function needsOptions(): bool
    {
        return in_array($this, [
            self::MultipleChoice,
            self::Checkboxes,
            self::Dropdown,
            self::ChoiceGrid,
            self::CheckboxGrid,
        ], true);
    }

    public function needsRows(): bool
    {
        return in_array($this, [self::ChoiceGrid, self::CheckboxGrid], true);
    }

    public function namedOnly(): bool
    {
        return $this === self::EmployeePick;
    }
}
