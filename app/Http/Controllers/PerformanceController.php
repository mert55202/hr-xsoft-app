<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Models\Competency;
use App\Models\Employee;
use App\Models\Goal;
use App\Models\Observation;
use App\Models\Position;
use App\Models\Review;
use App\Support\EmployeeAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function index(EmployeeAccess $access): View
    {
        $user = auth()->user();
        abort_unless($user->sees(AppModule::Competency), 403);
        $visible = $access->visibleIds($user);
        $reviews = Review::query()->with('employee')->latest();
        $goals = Goal::query()->with('employee');

        if ($visible !== null) {
            $ids = $visible === [] ? [0] : $visible;
            $reviews->whereIn('employee_id', $ids);
            $goals->whereIn('employee_id', $ids);
        }

        return view('performance.index', [
            'reviews' => $reviews->get(),
            'goals' => $goals->get(),
            'observations' => Observation::query()->when($visible !== null, fn ($query) => $query->whereIn('employee_id', $visible === [] ? [0] : $visible))->latest()->get(),
            'competencies' => Competency::query()->with('position')->orderBy('name')->get(),
            'people' => Employee::query()->where('is_candidate', false)->orderBy('last_name')->get()
                ->filter(fn (Employee $employee): bool => $user->can('update', $employee) || $access->canView($user, $employee))
                ->values(),
            'positions' => Position::query()->orderBy('name')->get(),
        ]);
    }

    public function storeReview(Request $request, EmployeeAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'period' => ['required', 'string', 'max:32'],
            'body' => ['required', 'string', 'max:4000'],
        ]);
        $employee = Employee::query()->with('position')->findOrFail($data['employee_id']);
        abort_unless($access->canManage($request->user(), $employee), 403);

        Review::query()->create([
            ...$data,
            'reviewer_id' => $request->user()->id,
            'collar' => $employee->position?->collar?->value ?? 'white',
            'status' => 'draft',
        ]);

        return back()->with('status', 'Değerlendirme açıldı.');
    }

    public function approve(Request $request, Review $review, EmployeeAccess $access): RedirectResponse
    {
        $review->load('employee');
        abort_unless($access->canManage($request->user(), $review->employee), 403);
        $review->update(['status' => 'approved']);

        return back()->with('status', 'Değerlendirme onaylandı.');
    }

    public function storeGoal(Request $request, EmployeeAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:160'],
        ]);
        $employee = Employee::query()->findOrFail($data['employee_id']);
        abort_unless($access->canView($request->user(), $employee), 403);
        Goal::query()->create($data);

        return back();
    }

    public function storeObservation(Request $request, EmployeeAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'body' => ['required', 'string', 'max:2000'],
            'observed_on' => ['required', 'date'],
        ]);
        $employee = Employee::query()->findOrFail($data['employee_id']);
        abort_unless($access->canManage($request->user(), $employee), 403);
        Observation::query()->create([...$data, 'author_id' => $request->user()->id]);

        return back();
    }

    public function storeCompetency(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
        ]);
        Competency::query()->create($data);

        return back();
    }
}
