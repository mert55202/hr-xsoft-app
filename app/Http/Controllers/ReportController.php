<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Enums\FormIdentity;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeMove;
use App\Models\Enrollment;
use App\Models\Form;
use App\Models\LeaveRequest;
use App\Models\Review;
use App\Models\SafetyRecord;
use App\Models\User;
use App\Support\EmployeeAccess;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(EmployeeAccess $access): View
    {
        Gate::authorize('viewAny', Employee::class);
        abort_unless(auth()->user()->sees(AppModule::Report), 403);

        $user = auth()->user();
        $people = Employee::query()
            ->where('is_candidate', false)
            ->whereHas('position', fn ($query) => $query->where('department_id', $user->department_id))
            ->get();
        $ids = $people->pluck('id');
        $founded = Company::current()->founded_on;
        $headcount = max($people->count(), 1);
        $leavers = $people->filter(fn (Employee $employee): bool => $employee->left_on !== null
            && ($founded === null || $employee->left_on->greaterThanOrEqualTo($founded)))->count();
        $moves = EmployeeMove::query()
            ->whereIn('employee_id', $ids === [] ? [0] : $ids)
            ->when($founded !== null, fn ($query) => $query->whereDate('moved_on', '>=', $founded))
            ->count();
        $years = $people->filter(fn (Employee $employee): bool => $employee->started_on !== null)
            ->map(fn (Employee $employee): float => $employee->started_on->diffInYears(now()))
            ->avg() ?? 0;

        $reports = [
            ['title' => 'Genel firma', 'value' => $people->count(), 'hint' => 'Departman kadrosu'],
            ['title' => 'İş güvenliği', 'value' => SafetyRecord::query()->whereIn('employee_id', $ids->all() ?: [0])->count(), 'hint' => 'Kayıt adedi'],
            ['title' => 'Ast izin', 'value' => LeaveRequest::query()->whereIn('employee_id', $this->below($access, $user))->where('status', 'approved')->count(), 'hint' => 'Onaylı talep'],
            ['title' => 'Personel izin', 'value' => LeaveRequest::query()->where('employee_id', $user->employee?->id ?? 0)->count(), 'hint' => 'Kendi talebin'],
            ['title' => 'Genel yetenek', 'value' => $this->submissions('aptitude', $ids->all()), 'hint' => 'Gönderim'],
            ['title' => 'Çalışan çıkış', 'value' => $this->submissions('exit_employee', $ids->all()), 'hint' => 'Gönderim'],
            ['title' => 'Firma çıkış', 'value' => $this->submissions('exit_company', $ids->all()), 'hint' => 'Gönderim'],
            ['title' => 'İş gücü devir oranı', 'value' => round($leavers / $headcount * 100, 1), 'hint' => 'Yüzde'],
            ['title' => 'İç terfi oranı', 'value' => round($moves / $headcount * 100, 1), 'hint' => 'Yüzde'],
            ['title' => 'Kıdem yılı', 'value' => round((float) $years, 1), 'hint' => 'Ortalama yıl. Tutar bordro kartında.'],
            ['title' => 'Performans değerlendirme', 'value' => Review::query()->whereIn('employee_id', $ids->all() ?: [0])->where('status', 'approved')->count(), 'hint' => 'Onaylı form'],
            ['title' => 'Eğitim test', 'value' => Enrollment::query()->whereIn('employee_id', $ids->all() ?: [0])->where('attended', true)->count(), 'hint' => 'Yoklama'],
        ];

        return view('reports.index', [
            'reports' => $reports,
            'chart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($reports, 'title'),
                    'datasets' => [[
                        'label' => 'Değer',
                        'data' => array_column($reports, 'value'),
                        'backgroundColor' => '#0d6efd',
                    ]],
                ],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'maintainAspectRatio' => false],
            ],
        ]);
    }

    /**
     * @param  list<int>  $employeeIds
     */
    private function submissions(string $template, array $employeeIds): int
    {
        $form = Form::query()->where('template', $template)->first();

        if ($form === null) {
            return 0;
        }

        $query = $form->submissions();

        if ($form->identity === FormIdentity::Anonymous) {
            return $query->count();
        }

        return $query->whereIn('employee_id', $employeeIds === [] ? [0] : $employeeIds)->count();
    }

    /**
     * @return list<int>
     */
    private function below(EmployeeAccess $access, User $user): array
    {
        $ids = $access->visibleIds($user) ?? Employee::query()->pluck('id')->all();
        $self = $user->employee?->id;

        return array_values(array_filter($ids, fn (int $id): bool => $id !== $self));
    }
}
