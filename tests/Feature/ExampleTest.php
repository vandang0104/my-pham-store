<?php

it('hiển thị trang chủ thành công', function () {
    $this->get('/')->assertOk();
});

it('dùng múi giờ Việt Nam và ngôn ngữ tiếng Việt', function () {
    expect(config('app.timezone'))->toBe('Asia/Ho_Chi_Minh')
        ->and(app()->getLocale())->toBe('vi');
});
