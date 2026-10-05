<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\AppModule;
use App\Models\User;

class Navigation
{
    /**
     * @return list<array{route: string, params: array<string, string>, label: string, icon: string, desktop: bool, mobile: bool, active: bool}>
     */
    public static function for(User $user): array
    {
        $items = [
            self::item('today', 'Bugün', 'bi-house'),
            self::item('personnel', 'Personel', 'bi-people'),
            self::item('hr', 'İK', 'bi-briefcase'),
            self::item('forms.index', 'Formlar', 'bi-ui-checks'),
        ];

        foreach (AppModule::cases() as $module) {
            if (! $user->sees($module)) {
                continue;
            }

            if ($module === AppModule::OrgChart) {
                $items[] = self::item('positions.chart', $module->label(), $module->icon(), desktop: true, mobile: false);

                continue;
            }

            if ($module === AppModule::Survey) {
                $items[] = self::item('forms.manage', $module->label(), $module->icon(), desktop: true, mobile: false);

                continue;
            }

            $items[] = self::item(
                'modules.show',
                $module->label(),
                $module->icon(),
                ['module' => $module->value],
                desktop: true,
                mobile: false,
            );
        }

        if ($user->isAdmin()) {
            $items[] = self::item('company.edit', 'Firma', 'bi-building');
            $items[] = self::item('departments.index', 'Departmanlar', 'bi-sliders', desktop: true, mobile: false);
            $items[] = self::item('positions.index', 'Pozisyonlar', 'bi-diagram-2', desktop: true, mobile: false);
        }

        $items[] = self::item('account', 'Hesabım', 'bi-person', desktop: false, mobile: true);

        return $items;
    }

    /**
     * @param  array<string, string>  $params
     * @return array{route: string, params: array<string, string>, label: string, icon: string, desktop: bool, mobile: bool, active: bool}
     */
    private static function item(
        string $route,
        string $label,
        string $icon,
        array $params = [],
        bool $desktop = true,
        bool $mobile = true,
    ): array {
        $active = request()->routeIs($route);

        if ($route === 'personnel') {
            $active = request()->routeIs('personnel', 'personnel.*');
        }

        if ($route === 'forms.index') {
            $active = request()->routeIs('forms.index', 'forms.show', 'forms.thanks');
        }

        if ($route === 'forms.manage') {
            $active = request()->routeIs('forms.manage', 'forms.create', 'forms.edit', 'forms.preview', 'forms.results');
        }

        if ($route === 'positions.index') {
            $active = request()->routeIs('positions.*') && ! request()->routeIs('positions.chart');
        }

        if ($active && isset($params['module'])) {
            $active = request()->route('module') === $params['module'];
        }

        return [
            'route' => $route,
            'params' => $params,
            'label' => $label,
            'icon' => $icon,
            'desktop' => $desktop,
            'mobile' => $mobile,
            'active' => $active,
        ];
    }
}
