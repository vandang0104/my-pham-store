<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Tin liên hệ (admin + staff — nhóm route role:admin,staff): danh sách (chưa xử lý lên đầu), xem, đánh dấu đã xử lý */
class ContactController extends Controller
{
    public function index(): View
    {
        return view('admin.contacts.index', [
            'messages' => ContactMessage::query()->orderBy('is_handled')->latest()->latest('id')->paginate(20),
        ]);
    }

    public function show(ContactMessage $contact): View
    {
        return view('admin.contacts.show', ['message' => $contact]);
    }

    public function markHandled(ContactMessage $contact): RedirectResponse
    {
        $contact->forceFill(['is_handled' => true])->save();

        return redirect()->route('admin.contacts.index')->with('status', "Đã đánh dấu xử lý tin của {$contact->name}.");
    }
}
