<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function hr(): View
    {
        $cards = [];

        foreach (AppModule::cases() as $module) {
            if (! auth()->user()->sees($module)) {
                continue;
            }

            $cards[] = [
                'icon' => $module->icon(),
                'title' => $module->label(),
                'text' => $module->description(),
                'href' => match ($module) {
                    AppModule::OrgChart => route('positions.chart'),
                    AppModule::Survey => route('forms.manage'),
                    default => route('modules.show', $module->value),
                },
            ];
        }

        return view('sections.pending', [
            'title' => 'İK',
            'text' => 'Açık modüller burada durur. Kapalı modül menüde görünmez.',
            'cards' => $cards,
        ]);
    }
}
