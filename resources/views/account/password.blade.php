@extends('account.layout')

@section('title', 'Đổi mật khẩu')

@section('account')
    <form method="POST" action="{{ route('account.password.update') }}" class="card card-body col-lg-8" novalidate>
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="form-required">(bắt buộc)</span></label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required
                class="form-control @error('current_password') is-invalid @enderror">
            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Mật khẩu mới <span class="form-required">(bắt buộc)</span></label>
            <input id="password" name="password" type="password" autocomplete="new-password" required aria-describedby="password-help"
                class="form-control @error('password') is-invalid @enderror">
            <div id="password-help" class="form-text">Ít nhất 8 ký tự, khác mật khẩu hiện tại.</div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-5">
            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu mới <span class="form-required">(bắt buộc)</span></label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-control">
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
        </div>
    </form>
@endsection
