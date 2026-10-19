<?php

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PostStatus;
use App\Enums\ReviewStatus;
use App\Enums\SkinType;
use App\Enums\UserRole;

it('cho phép chuyển trạng thái đơn đúng luồng', function (OrderStatus $from, OrderStatus $to) {
    expect($from->canTransitionTo($to))->toBeTrue();
})->with([
    'chờ xác nhận → đã xác nhận' => [OrderStatus::Pending, OrderStatus::Confirmed],
    'chờ xác nhận → huỷ' => [OrderStatus::Pending, OrderStatus::Cancelled],
    'đã xác nhận → đang giao' => [OrderStatus::Confirmed, OrderStatus::Shipping],
    'đã xác nhận → huỷ' => [OrderStatus::Confirmed, OrderStatus::Cancelled],
    'đang giao → đã giao' => [OrderStatus::Shipping, OrderStatus::Delivered],
]);

it('chặn chuyển trạng thái đơn sai luồng', function (OrderStatus $from, OrderStatus $to) {
    expect($from->canTransitionTo($to))->toBeFalse();
})->with([
    'nhảy cóc chờ xác nhận → đang giao' => [OrderStatus::Pending, OrderStatus::Shipping],
    'nhảy cóc chờ xác nhận → đã giao' => [OrderStatus::Pending, OrderStatus::Delivered],
    'huỷ khi đang giao' => [OrderStatus::Shipping, OrderStatus::Cancelled],
    'huỷ khi đã giao' => [OrderStatus::Delivered, OrderStatus::Cancelled],
    'quay lui đã xác nhận → chờ xác nhận' => [OrderStatus::Confirmed, OrderStatus::Pending],
    'mở lại đơn đã huỷ' => [OrderStatus::Cancelled, OrderStatus::Pending],
    'giữ nguyên trạng thái' => [OrderStatus::Pending, OrderStatus::Pending],
]);

it('coi đã giao và đã huỷ là trạng thái cuối', function () {
    $final = array_filter(OrderStatus::cases(), fn (OrderStatus $s) => $s->isFinal());

    expect(array_values($final))->toBe([OrderStatus::Delivered, OrderStatus::Cancelled]);
});

it('có nhãn tiếng Việt cho mọi giá trị enum', function (string $enum) {
    foreach ($enum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()->not->toBe($case->value);
    }
})->with([
    UserRole::class, OrderStatus::class, PaymentMethod::class, PaymentStatus::class,
    CouponType::class, ReviewStatus::class, PostStatus::class, SkinType::class,
]);

it('chốt giá trị loại da', function () {
    expect(array_map(fn (SkinType $s) => $s->label(), SkinType::cases()))
        ->toBe(['Da dầu', 'Da khô', 'Da hỗn hợp', 'Da nhạy cảm', 'Mọi loại da']);
});
