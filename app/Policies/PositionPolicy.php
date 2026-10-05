<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\User;

class PositionPolicy
{
    public function viewChart(User $user): bool
    {
        return $user->isAdmin() || $user->sees(AppModule::OrgChart);
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
