<?php

declare(strict_types=1);

namespace App\Enums;

enum PayType: string
{
    case Hourly = 'hourly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Hourly => 'Saatlik',
            self::Monthly => 'Aylık',
            self::Yearly => 'Yıllık',
        };
    }
}
