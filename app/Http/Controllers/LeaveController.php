<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Lookup;
use App\Support\EmployeeAccess;
use App\Support\LeaveDecider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(EmployeeAccess $access): View
    {
        $user = auth()->user();
        $visible = $access->visibleIds($user);
        $query = LeaveRequest::query()->with(['employee', 'lookup'])->latest();

        if ($visible !== null) {
            $query->whereIn('employee_id', $visible === [] ? [0] : $visible);
        }

        return view('leave.index', [
            'requests' => $query->get(),
            'types' => Lookup::query()->where('kind', 'leave_type')->orderBy('name')->get(),
            'balances' => $user->employee === null
                ? collect()
                : LeaveBalance::query()->where('employee_id', $user->employee->id)->with('lookup')->get(),
            'self' => $user->employee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee instanceof Employee && ! $employee->is_candidate, 404);

        $data = $request->validate([
            'lookup_id' => ['required', 'integer', Rule::exists('lookups', 'id')->where('kind', 'leave_type')],
            'mode' => ['required', Rule::in(['day', 'hour'])],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
        ]);

        $lookup = Lookup::query()->findOrFail($data['lookup_id']);
        $unit = $lookup->meta['unit'] ?? 'both';

        if ($unit !== 'both' && $unit !== $data['mode']) {
            throw ValidationException::withMessages(['mode' => 'Bu izin tipi seçilen birime kapalı.']);
        }

        $hours = null;

        if ($data['mode'] === 'hour') {
            if (empty($data['starts_at']) || empty($data['ends_at'])) {
                throw ValidationException::withMessages(['starts_at' => 'Saatlik izinde başlangıç ve bitiş saati gerekir.']);
            }

            $start = strtotime($data['starts_on'].' '.$data['starts_at']);
            $end = strtotime($data['starts_on'].' '.$data['ends_at']);
            $hours = round(($end - $start) / 3600, 2);

            if ($hours <= 0) {
                throw ValidationException::withMessages(['ends_at' => 'Bitiş saati başlangıçtan sonra olmalı.']);
            }
        }

        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'lookup_id' => $lookup->id,
            'mode' => $data['mode'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['mode'] === 'day' ? ($data['ends_on'] ?? $data['starts_on']) : null,
            'starts_at' => $data['mode'] === 'hour' ? $data['starts_at'] : null,
            'ends_at' => $data['mode'] === 'hour' ? $data['ends_at'] : null,
            'hours' => $hours,
            'status' => 'pending',
        ]);

        return back()->with('status', 'İzin talebi açıldı.');
    }

    public function update(Request $request, LeaveRequest $leave, LeaveDecider $decider): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ]);
        $decider->decide($leave, $request->user(), $data['status']);

        return back()->with('status', 'İzin kararı yazıldı.');
    }
}
