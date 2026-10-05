<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Enums\FormStatus;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Form;
use App\Support\EmployeeAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->sees(AppModule::Training), 403);
        $visible = app(EmployeeAccess::class)->visibleIds($user);
        $sessions = CourseSession::query()->with(['course', 'preForm', 'postForm', 'enrollments.employee'])->orderByDesc('held_on')->get();

        if (! $user->isAdmin()) {
            $sessions = $sessions->filter(function (CourseSession $session) use ($user): bool {
                $departmentId = $session->course?->department_id;

                return $departmentId === null || (int) $departmentId === (int) $user->department_id
                    || $session->enrollments->contains(fn (Enrollment $enrollment): bool => (int) $enrollment->employee?->user_id === (int) $user->id);
            })->values();
        }

        $people = Employee::query()->where('is_candidate', false)->orderBy('last_name');

        if ($visible !== null) {
            $people->whereIn('id', $visible === [] ? [0] : $visible);
        }

        return view('training.index', [
            'sessions' => $sessions,
            'courses' => Course::query()->orderBy('name')->get(),
            'forms' => Form::query()->where('status', FormStatus::Published)->orderBy('name')->get(),
            'people' => $people->get(),
        ]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        abort_unless($request->user()->sees(AppModule::Training), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:160']]);
        Course::query()->create([
            'name' => $data['name'],
            'department_id' => $request->user()->department_id,
        ]);

        return back()->with('status', 'Eğitim eklendi.');
    }

    public function storeSession(Request $request): RedirectResponse
    {
        abort_unless($request->user()->sees(AppModule::Training), 403);
        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'held_on' => ['required', 'date'],
            'pre_form_id' => ['nullable', 'integer', 'exists:forms,id'],
            'post_form_id' => ['nullable', 'integer', 'exists:forms,id'],
        ]);
        CourseSession::query()->create($data);

        return back()->with('status', 'Etkinlik açıldı.');
    }

    public function enroll(Request $request, CourseSession $session): RedirectResponse
    {
        abort_unless($request->user()->sees(AppModule::Training), 403);
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id']]);
        $employee = Employee::query()->findOrFail($data['employee_id']);
        Gate::authorize('view', $employee);
        Enrollment::query()->firstOrCreate([
            'course_session_id' => $session->id,
            'employee_id' => $data['employee_id'],
        ]);

        return back()->with('status', 'Katılımcı eklendi.');
    }

    public function attend(CourseSession $session, Employee $employee): RedirectResponse
    {
        $enrollment = Enrollment::query()
            ->where('course_session_id', $session->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();
        abort_unless((int) auth()->id() === (int) $employee->user_id || auth()->user()->isAdmin(), 403);
        $enrollment->update(['attended' => true]);

        return back()->with('status', 'Yoklama alındı.');
    }
}
