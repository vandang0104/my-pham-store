<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Trang liên hệ: lưu tin vào contact_messages (is_handled = false); admin xem ở khu quản trị. Không gửi mail. */
class ContactController extends Controller
{
    public function create(): View
    {
        $hotline = (string) Setting::get('store_hotline');

        return view('pages.contact', [
            'honeypot' => ContactRequest::HONEYPOT,
            'store' => [
                'hotline' => $hotline,
                'hotline_tel' => preg_replace('/\D/', '', $hotline),
                'email' => Setting::get('store_email'),
                'address' => Setting::get('store_address'),
                'hours' => Setting::get('store_hours'),
            ],
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        // Honeypot: bot nhận phản hồi như gửi thành công nhưng tin không được lưu
        if (! $request->isBot()) {
            ContactMessage::query()->create($request->validated());
        }

        return redirect()->route('contact.create')->with('status', __('contact.sent'));
    }
}
