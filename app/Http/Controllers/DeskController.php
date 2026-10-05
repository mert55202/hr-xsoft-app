<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Models\Announcement;
use App\Models\CompanyEvent;
use App\Models\Employee;
use App\Models\Lookup;
use App\Models\Meal;
use App\Models\Position;
use App\Models\WorkRequest;
use App\Models\WorkTask;
use App\Support\EmployeeAccess;
use App\Support\LookupKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeskController extends Controller
{
    public function announcements(): View
    {
        $this->gate(AppModule::Announcement);

        return view('desk.list', [
            'title' => 'Duyuru',
            'rows' => $this->scoped(Announcement::query())->latest()->get(),
            'fields' => ['title' => 'Başlık', 'body' => 'Metin'],
            'action' => route('announcements.store'),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $this->gate(AppModule::Announcement);
        $data = $request->validate(['title' => ['required', 'string', 'max:160'], 'body' => ['required', 'string', 'max:4000']]);
        Announcement::query()->create([...$data, 'department_id' => $request->user()->department_id]);

        return back();
    }

    public function meals(): View
    {
        $this->gate(AppModule::Meal);

        return view('desk.list', [
            'title' => 'Yemek',
            'rows' => $this->scoped(Meal::query())->orderByDesc('served_on')->get(),
            'fields' => ['served_on' => 'Tarih', 'title' => 'Menü'],
            'action' => route('meals.store'),
            'date' => 'served_on',
        ]);
    }

    public function storeMeal(Request $request): RedirectResponse
    {
        $this->gate(AppModule::Meal);
        $data = $request->validate(['served_on' => ['required', 'date'], 'title' => ['required', 'string', 'max:160']]);
        Meal::query()->create([...$data, 'department_id' => $request->user()->department_id]);

        return back();
    }

    public function events(): View
    {
        $this->gate(AppModule::Event);

        return view('desk.list', [
            'title' => 'Etkinlik',
            'rows' => $this->scoped(CompanyEvent::query())->orderBy('starts_on')->get(),
            'fields' => ['title' => 'Ad', 'starts_on' => 'Tarih'],
            'action' => route('events.store'),
            'date' => 'starts_on',
        ]);
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $this->gate(AppModule::Event);
        $data = $request->validate(['title' => ['required', 'string', 'max:160'], 'starts_on' => ['required', 'date']]);
        CompanyEvent::query()->create([...$data, 'department_id' => $request->user()->department_id]);

        return back();
    }

    public function tasks(): View
    {
        $this->gate(AppModule::Task);

        return view('desk.list', [
            'title' => 'Görev',
            'rows' => $this->scoped(WorkTask::query())->latest()->get(),
            'fields' => ['title' => 'Görev'],
            'action' => route('tasks.store'),
        ]);
    }

    public function storeTask(Request $request): RedirectResponse
    {
        $this->gate(AppModule::Task);
        $data = $request->validate(['title' => ['required', 'string', 'max:160']]);
        WorkTask::query()->create([...$data, 'department_id' => $request->user()->department_id]);

        return back();
    }

    public function jobs(): View
    {
        $this->gate(AppModule::JobProfile);
        $query = Position::query()->with('department')->orderBy('name');

        if (! auth()->user()->isAdmin()) {
            $query->where('department_id', auth()->user()->department_id);
        }

        return view('desk.jobs', ['positions' => $query->get()]);
    }

    public function requests(EmployeeAccess $access): View
    {
        $this->gate(AppModule::Request);
        $user = auth()->user();
        $selfId = $user->employee?->id;
        $childIds = [];

        if ($user->employee?->position_id !== null) {
            $childIds = Employee::query()
                ->where('is_candidate', false)
                ->whereIn('position_id', Position::query()->where('parent_id', $user->employee->position_id)->pluck('id'))
                ->pluck('id')
                ->all();
        }

        $ids = array_values(array_filter([$selfId, ...$childIds]));

        return view('desk.requests', [
            'rows' => WorkRequest::query()->with('employee')->whereIn('employee_id', $ids === [] ? [0] : $ids)->latest()->get(),
            'types' => Lookup::query()->where('kind', 'request_type')->orderBy('name')->get(),
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $this->gate(AppModule::Request);
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 404);
        $data = $request->validate([
            'lookup_id' => ['nullable', 'integer', 'exists:lookups,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);
        WorkRequest::query()->create([...$data, 'employee_id' => $employee->id]);

        return back();
    }

    public function lookups(): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('desk.lookups', [
            'kinds' => LookupKind::labels(),
            'rows' => Lookup::query()->orderBy('kind')->orderBy('name')->get(),
        ]);
    }

    public function storeLookup(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'kind' => ['required', Rule::in(array_keys(LookupKind::labels()))],
            'name' => ['required', 'string', 'max:160'],
            'unit' => ['nullable', Rule::in(['day', 'hour', 'both'])],
        ]);
        Lookup::query()->create([
            'kind' => $data['kind'],
            'name' => $data['name'],
            'meta' => $data['kind'] === 'leave_type' ? ['unit' => $data['unit'] ?? 'both'] : null,
        ]);

        return back();
    }

    private function gate(AppModule $module): void
    {
        abort_unless(auth()->user()->sees($module), 403);
    }

    private function scoped(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }
}
