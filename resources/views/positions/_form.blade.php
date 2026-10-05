@csrf
@if ($method !== 'post')
    @method($method)
@endif
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label" for="name">Pozisyon adı</label>
        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $position->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="department_id">Departman</label>
        <select class="form-select" id="department_id" name="department_id">
            <option value="">Seçilmedi</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $position->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label" for="collar">Yaka</label>
        <select class="form-select @error('collar') is-invalid @enderror" id="collar" name="collar" required>
            @foreach (\App\Enums\Collar::cases() as $collar)
                <option value="{{ $collar->value }}" @selected(old('collar', $position->collar?->value ?? 'white') === $collar->value)>{{ $collar->label() }}</option>
            @endforeach
        </select>
        @error('collar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label" for="parent_id">Birinci amir</label>
        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
            <option value="">Kök</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $position->parent_id ?? '') === (string) $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label" for="headcount">Norm kadro</label>
        <input class="form-control @error('headcount') is-invalid @enderror" id="headcount" name="headcount" type="number" min="1" value="{{ old('headcount', $position->headcount ?? 1) }}" required>
        @error('headcount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
@if (isset($position) && $position->exists)
    <p class="text-secondary mt-3 mb-0">İkinci amir: {{ $position->secondManager()?->name ?? 'Yok' }}</p>
@endif
<button class="btn btn-primary mt-3" type="submit">{{ $submit }}</button>
