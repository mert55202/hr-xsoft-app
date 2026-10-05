<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\User;

class FormPolicy
{
    public function manage(User $user): bool
    {
        return $user->sees(AppModule::Survey);
    }
}
