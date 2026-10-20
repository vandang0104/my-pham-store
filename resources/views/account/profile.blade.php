@extends('account.layout')

@section('title', 'Hồ sơ')

@section('account')
    <form method="POST" action="{{ route('account.profile.update') }}" class="card card-body" novalidate>
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled aria-describedby="email-help">
                <div id="email-help" class="form-text">Email đăng nhập không đổi được.</div>
            </div>
            <div class="col-md-6">
                <label for="name" class="form-label">Họ tên <span class="form-required">(bắt buộc)</span></label>
                <input id="name" name="name" type="text" maxlength="255" autocomplete="name" required
                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Số điện thoại <span class="form-required">(bắt buộc)</span></label>
                <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required
                    class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="birthday" class="form-label">Ngày sinh</label>
                <input id="birthday" name="birthday" type="date" autocomplete="bday"
                    class="form-control @error('birthday') is-invalid @enderror" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}">
                @error('birthday') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="skin_type" class="form-label">Loại da</label>
                <select id="skin_type" name="skin_type" class="form-select @error('skin_type') is-invalid @enderror">
                    <option value="">Không chọn</option>
                    @foreach ($skinTypes as $skinType)
                        <option value="{{ $skinType->value }}" @selected(old('skin_type', $user->skin_type?->value) === $skinType->value)>{{ $skinType->label() }}</option>
                    @endforeach
                </select>
                @error('skin_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mt-5">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
    </form>
@endsection
