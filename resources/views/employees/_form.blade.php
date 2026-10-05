@csrf
@if ($method !== 'post')
    @method($method)
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="first_name">Ad</label>
        <input class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required>
        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="last_name">Soyad</label>
        <input class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required>
        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">E-posta</label>
        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $employee->email) }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="phone">Telefon</label>
        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="national_id">T.C. kimlik no</label>
        <input class="form-control @error('national_id') is-invalid @enderror" id="national_id" name="national_id" inputmode="numeric" maxlength="11" value="{{ old('national_id', $employee->national_id) }}">
        @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="birth_date">Doğum tarihi</label>
        <input class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}">
        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="started_on">İşe giriş</label>
        <input class="form-control @error('started_on') is-invalid @enderror" id="started_on" name="started_on" type="date" value="{{ old('started_on', $employee->started_on?->format('Y-m-d')) }}">
        @error('started_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="position_id">Pozisyon</label>
        <select class="form-select @error('position_id') is-invalid @enderror" id="position_id" name="position_id" required>
            <option value="">Seçin</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected((int) old('position_id', $employee->position_id) === $position->id)>{{ $position->name }}</option>
            @endforeach
        </select>
        @error('position_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="user_id">Giriş hesabı</label>
        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
            <option value="">Bağlı değil</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((int) old('user_id', $employee->user_id) === $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="pay_type">Maaş türü</label>
        <select class="form-select @error('pay_type') is-invalid @enderror" id="pay_type" name="pay_type">
            <option value="">Seçilmedi</option>
            @foreach (\App\Enums\PayType::cases() as $payType)
                <option value="{{ $payType->value }}" @selected(old('pay_type', $employee->pay_type?->value) === $payType->value)>{{ $payType->label() }}</option>
            @endforeach
        </select>
        @error('pay_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="pay_amount">Tutar</label>
        <input class="form-control @error('pay_amount') is-invalid @enderror" id="pay_amount" name="pay_amount" inputmode="decimal" value="{{ old('pay_amount', $employee->pay_amount) }}">
        @error('pay_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="currency">Para birimi</label>
        <input class="form-control" id="currency" name="currency" value="{{ old('currency', $employee->currency ?: 'TRY') }}" maxlength="8">
    </div>
</div>

<button class="btn btn-primary mt-3" type="submit">{{ $submit }}</button>
