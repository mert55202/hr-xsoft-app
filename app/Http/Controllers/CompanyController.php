<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function edit(): View
    {
        return view('company.edit', [
            'company' => Company::current(),
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = Company::current();

        Gate::authorize('update', $company);

        $company->update($request->validated());

        return redirect()
            ->route('company.edit')
            ->with('status', 'Firma kaydı güncellendi.');
    }
}
