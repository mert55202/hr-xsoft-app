@csrf
@if ($method !== 'post')
    @method($method)
@endif
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="name">Ad</label>
        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $form->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="identity">Kimlik</label>
        <select class="form-select @error('identity') is-invalid @enderror" id="identity" name="identity" @disabled(! $form->isDraft() && $form->exists) required>
            @foreach (\App\Enums\FormIdentity::cases() as $identity)
                <option value="{{ $identity->value }}" @selected(old('identity', $form->identity?->value ?? 'named') === $identity->value)>{{ $identity->label() }}</option>
            @endforeach
        </select>
        @if (! $form->isDraft() && $form->exists)
            <input type="hidden" name="identity" value="{{ $form->identity->value }}">
        @endif
        @error('identity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Açıklama</label>
        <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $form->description) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="thank_you">Teşekkür</label>
        <textarea class="form-control" id="thank_you" name="thank_you" rows="2">{{ old('thank_you', $form->thank_you) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="starts_on">Başlangıç</label>
        <input class="form-control" id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', $form->starts_on?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="ends_on">Bitiş</label>
        <input class="form-control" id="ends_on" name="ends_on" type="date" value="{{ old('ends_on', $form->ends_on?->format('Y-m-d')) }}">
    </div>
</div>
<button class="btn btn-primary mt-3" type="submit">{{ $submit }}</button>
