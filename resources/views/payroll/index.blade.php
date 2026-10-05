@extends('layouts.app')

@section('title', 'Bordro')

@section('content')
    <h1 class="h3 mb-3">Bordro</h1>
    @if ($manageable->isNotEmpty())
        <form class="app-panel mb-3" method="post" action="{{ route('payslips.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="employee_id">Personel</label>
                    <select class="form-select" id="employee_id" name="employee_id" required>
                        @foreach ($manageable as $person)
                            <option value="{{ $person->id }}">{{ $person->fullName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="year">Yıl</label>
                    <input class="form-control" id="year" name="year" type="number" value="{{ now()->year }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="month">Ay</label>
                    <input class="form-control" id="month" name="month" type="number" min="1" max="12" value="{{ now()->month }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="hours">Saat</label>
                    <input class="form-control" id="hours" name="hours" inputmode="decimal">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="deduction">Kesinti</label>
                    <input class="form-control" id="deduction" name="deduction" inputmode="decimal" value="0">
                </div>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Yaz</button>
        </form>
    @endif
    <div class="d-none d-lg-block">
        <table class="table">
            <thead><tr><th>Kişi</th><th>Dönem</th><th>Brüt</th><th>Kesinti</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($payslips as $payslip)
                    <tr>
                        <td>{{ $payslip->employee->fullName() }}</td>
                        <td>{{ $payslip->year }} / {{ $payslip->month }}</td>
                        <td>{{ $payslip->gross }}</td>
                        <td>{{ $payslip->deduction }}</td>
                        <td>{{ $payslip->net }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-lg-none">
        @foreach ($payslips as $payslip)
            <article class="app-panel mb-2">
                <p class="fw-semibold mb-1">{{ $payslip->employee->fullName() }}</p>
                <p class="mb-0">{{ $payslip->year }}/{{ $payslip->month }} · net {{ $payslip->net }}</p>
            </article>
        @endforeach
    </div>
@endsection
