<?php

use App\Support\Money;

it('định dạng tiền VND với dấu chấm phân cách hàng nghìn', function (int $amount, string $expected) {
    expect(Money::format($amount))->toBe($expected);
})->with([
    [0, "0\u{00A0}₫"],
    [9000, "9.000\u{00A0}₫"],
    [350000, "350.000\u{00A0}₫"],
    [12500000, "12.500.000\u{00A0}₫"],
]);
