<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

/** Câu hỏi thường gặp: chỉ hiển thị từ seed (CRUD đã cắt). Nhóm xếp theo câu đầu tiên của nhóm (sort_order). */
class FaqController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.faq', [
            'groups' => Faq::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('group'),
        ]);
    }
}
