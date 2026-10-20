<?php

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

/** @param array<string, mixed> $overrides */
function lienHeHopLe(array $overrides = []): array
{
    return [
        'name' => 'Nguyễn Thị Lan',
        'email' => 'Lan@Example.com',
        'phone' => '0912 345 678',
        'subject' => 'Hỏi về serum',
        'message' => 'Da dầu có dùng được serum niacinamide không ạ?',
        ...$overrides,
    ];
}

describe('câu hỏi thường gặp', function () {
    it('trả 200, nhóm theo chủ đề, câu hỏi dạng accordion, đúng thứ tự, ẩn câu đã tắt', function () {
        Faq::factory()->create(['group' => 'Vận chuyển', 'question' => 'Bao lâu nhận được hàng?', 'sort_order' => 3]);
        Faq::factory()->create(['group' => 'Đặt hàng', 'question' => 'Có cần tài khoản không?', 'sort_order' => 1]);
        Faq::factory()->create(['group' => 'Đặt hàng', 'question' => 'Huỷ đơn thế nào?', 'sort_order' => 2]);
        Faq::factory()->create(['group' => 'Đặt hàng', 'question' => 'Câu hỏi đã tắt', 'is_active' => false]);

        $this->get(route('faq.index'))
            ->assertOk()
            ->assertSee('class="accordion"', false)
            ->assertSeeInOrder(['Đặt hàng', 'Có cần tài khoản không?', 'Huỷ đơn thế nào?', 'Vận chuyển', 'Bao lâu nhận được hàng?'])
            ->assertDontSee('Câu hỏi đã tắt');
    });

    it('chưa có câu hỏi nào → trạng thái trống', function () {
        $this->get(route('faq.index'))->assertOk()->assertSee('Chưa có câu hỏi nào');
    });
});

describe('liên hệ', function () {
    it('form 200, có thông tin cửa hàng từ settings và ô honeypot ẩn', function () {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('name="website"', false)
            ->assertSee(Setting::get('store_hotline'))
            ->assertSee(Setting::get('store_email'))
            ->assertSee(Setting::get('store_hours'));
    });

    it('gửi hợp lệ: lưu tin chưa xử lý (email viết thường, SĐT chuẩn hoá), báo thành công', function () {
        $this->post(route('contact.store'), lienHeHopLe())
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('status');

        $tin = ContactMessage::query()->sole();
        expect($tin->email)->toBe('lan@example.com')
            ->and($tin->phone)->toBe('0912345678')
            ->and($tin->subject)->toBe('Hỏi về serum')
            ->and($tin->is_handled)->toBeFalse();
    });

    it('SĐT không bắt buộc', function () {
        $this->post(route('contact.store'), lienHeHopLe(['phone' => '']))->assertSessionHasNoErrors();
        expect(ContactMessage::query()->sole()->phone)->toBeNull();
    });

    it('thiếu hoặc sai trường thì báo lỗi, không lưu', function (array $overrides, string $field) {
        $this->post(route('contact.store'), lienHeHopLe($overrides))->assertSessionHasErrors($field);
        expect(ContactMessage::count())->toBe(0);
    })->with([
        'thiếu họ tên' => [['name' => ''], 'name'],
        'email sai' => [['email' => 'lan@'], 'email'],
        'SĐT sai' => [['phone' => '012345'], 'phone'],
        'thiếu chủ đề' => [['subject' => ''], 'subject'],
        'nội dung quá ngắn' => [['message' => 'Chào'], 'message'],
        'nội dung quá dài' => [['message' => str_repeat('a', 2001)], 'message'],
    ]);

    it('honeypot: bot điền ô ẩn → vẫn báo thành công nhưng không lưu', function () {
        $this->post(route('contact.store'), lienHeHopLe(['website' => 'http://spam.example']))
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('status');

        expect(ContactMessage::count())->toBe(0);
    });

    it('giới hạn 3 lượt / 10 phút theo IP: lượt thứ 4 bị chặn, báo lỗi tiếng Việt', function () {
        RateLimiter::clear('contact|127.0.0.1');

        foreach (range(1, 3) as $i) {
            $this->post(route('contact.store'), lienHeHopLe())->assertSessionHasNoErrors();
        }

        $this->from(route('contact.create'))->post(route('contact.store'), lienHeHopLe())
            ->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors(['throttle' => 'Bạn đã gửi quá nhiều tin. Vui lòng thử lại sau 10 phút.']);

        expect(ContactMessage::count())->toBe(3);
    });
});

describe('header và footer', function () {
    it('link Câu hỏi thường gặp và Liên hệ trỏ đúng route', function () {
        $this->get(route('faq.index'))
            ->assertSee('href="'.route('faq.index').'"', false)
            ->assertSee('href="'.route('contact.create').'"', false);
    });
});
