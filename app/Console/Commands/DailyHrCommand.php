<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Notice;
use Illuminate\Console\Command;

class DailyHrCommand extends Command
{
    protected $signature = 'hr:gunluk';

    protected $description = 'Doğum günü ve deneme süresi bildirimlerini yazar.';

    public function handle(): int
    {
        $today = now()->startOfDay();

        Employee::query()
            ->where('is_candidate', false)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->each(function (Employee $employee): void {
                if ($employee->user_id === null) {
                    return;
                }

                Notice::query()->create([
                    'user_id' => $employee->user_id,
                    'body' => 'Doğum günün kutlu olsun.',
                ]);
            });

        foreach ([6 => '6. hafta', 22 => '22. hafta'] as $weeks => $label) {
            $this->trial($today->copy()->subWeeks($weeks)->toDateString(), $label);
        }

        $this->trial($today->copy()->subYear()->toDateString(), '1. yıl');

        return self::SUCCESS;
    }

    private function trial(string $date, string $label): void
    {
        Employee::query()
            ->where('is_candidate', false)
            ->whereDate('started_on', $date)
            ->with('position')
            ->each(function (Employee $employee) use ($label): void {
                $manager = $employee->manager();

                if ($manager?->user_id === null) {
                    return;
                }

                Notice::query()->create([
                    'user_id' => $manager->user_id,
                    'body' => 'Deneme süresi '.$label.': '.$employee->fullName(),
                ]);
            });
    }
}
