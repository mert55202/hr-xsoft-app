<?php

declare(strict_types=1);

namespace App\Enums;

enum Collar: string
{
    case White = 'white';
    case Blue = 'blue';

    public function label(): string
    {
        return match ($this) {
            self::White => 'Beyaz yaka',
            self::Blue => 'Mavi yaka',
        };
    }
}
