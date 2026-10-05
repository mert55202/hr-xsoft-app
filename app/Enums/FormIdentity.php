<?php

declare(strict_types=1);

namespace App\Enums;

enum FormIdentity: string
{
    case Named = 'named';
    case Link = 'link';
    case Anonymous = 'anonymous';

    public function label(): string
    {
        return match ($this) {
            self::Named => 'İsimli',
            self::Link => 'Kişisel bağlantı',
            self::Anonymous => 'Anonim',
        };
    }
}
