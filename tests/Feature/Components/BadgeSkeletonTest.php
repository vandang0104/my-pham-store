<?php

// Badge (DESIGN.md §9: chỉ số đếm và phần trăm giảm) và skeleton đang tải

describe('badge giỏ hàng', function () {
    it('ẩn khi 0, một chữ số không giãn, từ 10 giãn ngang, trên 99 hiện 99+', function (int $count, ?string $shown, bool $wide) {
        $html = (string) $this->blade('<x-cart-badge :count="$n" />', ['n' => $count]);

        if ($shown === null) {
            expect(trim($html))->toBe('');

            return;
        }

        expect($html)->toContain('>'.$shown.'<', $count.' sản phẩm trong giỏ')
            ->and(str_contains($html, 'cart-badge--wide'))->toBe($wide);
    })->with([
        [0, null, false],
        [3, '3', false],
        [12, '12', true],
        [120, '99+', true],
    ]);
});

describe('nhãn phần trăm giảm', function () {
    it('mặc định aria-hidden (giá đã được <x-price> đọc), đứng riêng thì đọc được', function () {
        $this->blade('<x-discount-badge :percent="20" />')
            ->assertSee('−20%')
            ->assertSee('aria-hidden="true"', false);

        $this->blade('<x-discount-badge :percent="20" :decorative="false" />')
            ->assertSee('−20%')
            ->assertDontSee('aria-hidden', false);
    });
});

describe('skeleton', function () {
    it('lưới thẻ: đúng số khung, báo đang tải cho trình đọc màn hình, khung aria-hidden', function () {
        $html = (string) $this->blade('<x-skeleton.product-grid :count="3" />');

        expect(substr_count($html, 'skeleton skeleton-card'))->toBe(3)
            ->and($html)->toContain('product-grid', 'aria-busy="true"', 'role="status"', 'Đang tải sản phẩm…')
            ->and(substr_count($html, 'aria-hidden="true"'))->toBe(3);
    });

    it('danh sách dòng: ảnh nhỏ + 2 dòng chữ + giá', function () {
        $html = (string) $this->blade('<x-skeleton.list :count="2" />');

        expect(substr_count($html, 'skeleton skeleton-row'))->toBe(2)
            ->and($html)->toContain('skeleton-row__thumb', 'skeleton-row__name', 'skeleton-row__meta', 'skeleton-row__price', 'aria-busy="true"');
    });

    it('CSS skeleton phẳng: không gradient, không shimmer; đứng yên khi giảm chuyển động', function () {
        $scss = file_get_contents(resource_path('scss/_skeleton.scss'));

        expect($scss)->not->toContain('gradient(')
            ->not->toContain('translateX')
            ->not->toContain('background-position')
            ->toContain('prefers-reduced-motion: reduce');
    });
});
