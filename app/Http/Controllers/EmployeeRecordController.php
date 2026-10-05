<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeRecordController extends Controller
{
    public function storeAsset(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'given_on' => ['nullable', 'date'],
        ]);
        $employee->assets()->create($data);

        return back()->with('status', 'Envanter eklendi.');
    }

    public function storeDocument(Request $request, Employee $employee): RedirectResponse
    {
        $this->allowOwnerOrManager($request, $employee);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $file = $data['file'];
        $employee->documents()->create([
            'path' => $file->store('documents/'.$employee->id, 'local'),
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('status', 'Evrak yüklendi.');
    }

    public function verifyDocument(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        Gate::authorize('update', $employee);
        abort_unless($document->employee_id === $employee->id, 404);
        $document->update(['verified_at' => now()]);

        return back()->with('status', 'Evrak doğrulandı.');
    }

    public function downloadDocument(Employee $employee, EmployeeDocument $document): StreamedResponse
    {
        Gate::authorize('view', $employee);
        abort_unless($document->employee_id === $employee->id, 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function storeHealth(Request $request, Employee $employee): RedirectResponse
    {
        $this->allowOwnerOrManager($request, $employee);
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        $employee->health()->updateOrCreate([], ['note' => $data['note']]);

        return back()->with('status', 'Sağlık kaydı yazıldı.');
    }

    public function storeRelative(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'kinship' => ['nullable', 'string', 'max:80'],
        ]);
        $employee->relatives()->create($data);

        return back()->with('status', 'Yakın eklendi.');
    }

    public function storeNote(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $data = $request->validate([
            'kind' => ['required', 'in:odul,uyari'],
            'body' => ['required', 'string', 'max:2000'],
        ]);
        $employee->notes()->create([
            'author_id' => $request->user()->id,
            'kind' => $data['kind'],
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Kayıt eklendi.');
    }

    public function storeSafety(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'recorded_on' => ['required', 'date'],
        ]);
        $employee->safetyRecords()->create($data);

        return back()->with('status', 'İş güvenliği kaydı eklendi.');
    }

    private function allowOwnerOrManager(Request $request, Employee $employee): void
    {
        $own = $request->user()->employee?->is($employee) ?? false;
        abort_unless($own || $request->user()->can('update', $employee), 403);
    }
}
