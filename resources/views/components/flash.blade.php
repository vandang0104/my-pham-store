<div class="flash-stack">
    @foreach ($messages as $flash)
        <div class="flash flash--{{ $flash['type'] }}" role="{{ $flash['role'] }}" data-flash @if ($flash['autohide']) data-flash-autohide @endif>
            <div class="container flash__inner">
                <p class="flash__text">{{ $flash['message'] }}</p>
                <button type="button" class="flash__close" data-flash-close aria-label="Đóng thông báo">
                    <x-icon name="close" :size="20" />
                </button>
            </div>
        </div>
    @endforeach
</div>
