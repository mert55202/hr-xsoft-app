<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Form;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Notice;
use App\Models\Payslip;
use App\Models\User;
use App\Support\EmployeeAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'E-posta veya şifre hatalı.'], 422);
        }

        $plain = Str::random(40);
        ApiToken::query()->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plain),
            'name' => 'telefon',
        ]);

        return response()->json(['token' => $plain, 'name' => $user->name]);
    }

    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'name' => $user->name,
            'notices' => Notice::query()->where('user_id', $user->id)->whereNull('read_at')->pluck('body'),
            'leave' => LeaveRequest::query()->where('employee_id', $user->employee?->id ?? 0)->where('status', 'pending')->count(),
        ]);
    }

    public function leave(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        return response()->json([
            'balances' => $employee === null ? [] : LeaveBalance::query()->where('employee_id', $employee->id)->get(['unit', 'amount', 'lookup_id']),
            'requests' => $employee === null ? [] : LeaveRequest::query()->where('employee_id', $employee->id)->latest()->get(),
        ]);
    }

    public function payslips(Request $request, EmployeeAccess $access): JsonResponse
    {
        $visible = $access->visibleIds($request->user());
        $query = Payslip::query()->orderByDesc('year')->orderByDesc('month');

        if ($visible !== null) {
            $query->whereIn('employee_id', $visible === [] ? [0] : $visible);
        }

        return response()->json(['payslips' => $query->get(['employee_id', 'year', 'month', 'net'])]);
    }

    public function forms(Request $request): JsonResponse
    {
        return response()->json([
            'forms' => Form::query()->where('status', 'published')->orderBy('name')->get(['public_id', 'name', 'identity']),
        ]);
    }

    public function document(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee instanceof Employee, 404);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $path = $data['file']->store('documents/'.$employee->id, 'local');
        $document = EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'path' => $path,
            'original_name' => $data['file']->getClientOriginalName(),
        ]);

        return response()->json(['id' => $document->id, 'name' => $document->original_name], 201);
    }
}
