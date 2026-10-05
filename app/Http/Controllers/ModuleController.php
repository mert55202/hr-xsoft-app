<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Support\EmployeeAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function show(string $module): View|RedirectResponse
    {
        $appModule = AppModule::tryFrom($module) ?? abort(404);

        abort_unless(auth()->user()->sees($appModule), 403);

        if ($appModule === AppModule::OrgChart) {
            return redirect()->route('positions.chart');
        }

        if ($appModule === AppModule::Survey) {
            return app(FormBuilderController::class)->index();
        }

        return match ($appModule) {
            AppModule::Leave => app(LeaveController::class)->index(app(EmployeeAccess::class)),
            AppModule::Payroll => app(PayslipController::class)->index(app(EmployeeAccess::class)),
            AppModule::Training => app(TrainingController::class)->index(),
            AppModule::Event => app(DeskController::class)->events(),
            AppModule::Request => app(DeskController::class)->requests(app(EmployeeAccess::class)),
            AppModule::Report => app(ReportController::class)->index(app(EmployeeAccess::class)),
            AppModule::Announcement => app(DeskController::class)->announcements(),
            AppModule::Meal => app(DeskController::class)->meals(),
            AppModule::Competency => app(PerformanceController::class)->index(app(EmployeeAccess::class)),
            AppModule::Task => app(DeskController::class)->tasks(),
            AppModule::JobProfile => app(DeskController::class)->jobs(),
            AppModule::Recruitment => app(RecruitmentController::class)->index(),
            default => view('sections.pending', [
                'title' => $appModule->label(),
                'text' => $appModule->description(),
                'icon' => $appModule->icon(),
            ]),
        };
    }
}
