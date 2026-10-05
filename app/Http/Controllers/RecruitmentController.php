<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Models\CandidateReference;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\Position;
use App\Models\StaffingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->sees(AppModule::Recruitment), 403);
        $requests = StaffingRequest::query()->with('department')->latest()->get();

        if (! $user->isAdmin()) {
            $requests = $requests->where('department_id', $user->department_id)->values();
        }

        return view('recruitment.index', [
            'requests' => $requests,
            'candidates' => Employee::query()->where('is_candidate', true)->with(['interviews', 'references', 'offers'])->orderBy('last_name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:160'],
            'headcount' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        abort_unless($request->user()->isAdmin() || (int) $request->user()->department_id === (int) $data['department_id'], 403);

        StaffingRequest::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Eleman isteği açıldı.');
    }

    public function storeCandidate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:160'],
        ]);

        Employee::query()->create([
            ...$data,
            'is_candidate' => true,
        ]);

        return back()->with('status', 'Aday eklendi.');
    }

    public function interview(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->is_candidate, 404);
        $data = $request->validate([
            'held_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        Interview::query()->create([
            ...$data,
            'employee_id' => $employee->id,
            'interviewer_id' => $request->user()->id,
        ]);

        return back();
    }

    public function reference(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->is_candidate, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        CandidateReference::query()->create([...$data, 'employee_id' => $employee->id]);

        return back();
    }

    public function offer(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->is_candidate, 404);
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0']]);
        Offer::query()->create([
            'employee_id' => $employee->id,
            'amount' => $data['amount'],
            'status' => 'sent',
        ]);

        return back();
    }

    public function hire(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($request->user()->sees(AppModule::Recruitment), 403);
        abort_unless($employee->is_candidate, 404);
        $data = $request->validate(['position_id' => ['required', 'integer', 'exists:positions,id']]);
        $position = Position::query()->with('department')->findOrFail($data['position_id']);
        $onSeat = Employee::query()->where('position_id', $position->id)->where('is_candidate', false)->count();

        if ($onSeat >= $position->headcount) {
            throw ValidationException::withMessages(['position_id' => "{$position->name} kadrosu dolu."]);
        }

        $limit = $position->department?->headcount;

        if ($limit !== null) {
            $inDepartment = Employee::query()
                ->where('is_candidate', false)
                ->whereHas('position', fn ($query) => $query->where('department_id', $position->department_id))
                ->count();

            if ($inDepartment >= $limit) {
                throw ValidationException::withMessages(['position_id' => "{$position->department->name} en fazla {$limit} kişi."]);
            }
        }

        $employee->update([
            'is_candidate' => false,
            'position_id' => $position->id,
            'started_on' => $employee->started_on ?? now()->toDateString(),
        ]);
        Offer::query()->where('employee_id', $employee->id)->where('status', 'sent')->update(['status' => 'accepted']);

        return redirect()->route('personnel.show', $employee)->with('status', 'Aday personel oldu.');
    }
}
