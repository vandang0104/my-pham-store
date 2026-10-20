<?php

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('chat phía khách', function () {
    it('khách vãng lai: tin đầu cần tên, gửi được, đọc lại tin của mình', function () {
        $this->postJson(route('chat.send'), ['body' => 'Chào shop'])->assertStatus(422)->assertJsonValidationErrors('name');

        $this->postJson(route('chat.send'), ['body' => 'Chào shop', 'name' => 'Lan'])->assertCreated()->assertJsonPath('message.body', 'Chào shop');
        $this->postJson(route('chat.send'), ['body' => 'Còn serum không?'])->assertCreated();

        $conversation = ChatConversation::query()->sole();
        expect($conversation->guest_name)->toBe('Lan')->and($conversation->user_id)->toBeNull();

        $this->getJson(route('chat.messages'))->assertOk()->assertJsonCount(2, 'messages')->assertJsonPath('needs_name', false);
    });

    it('user đăng nhập: hội thoại theo user_id, lấy tin sau id', function () {
        $user = User::factory()->customer()->create();
        $this->actingAs($user)->postJson(route('chat.send'), ['body' => 'Tin 1'])->assertCreated();
        $id = $this->postJson(route('chat.send'), ['body' => 'Tin 2'])->json('message.id');
        $this->postJson(route('chat.send'), ['body' => 'Tin 3']);

        expect(ChatConversation::query()->sole()->user_id)->toBe($user->id);
        $this->getJson(route('chat.messages', ['sau' => $id]))->assertJsonCount(1, 'messages')->assertJsonPath('messages.0.body', 'Tin 3');
    });

    it('không đọc được hội thoại của người khác', function () {
        $chu = User::factory()->customer()->create();
        $this->actingAs($chu)->postJson(route('chat.send'), ['body' => 'Bí mật']);

        $this->actingAs(User::factory()->customer()->create())->getJson(route('chat.messages'))
            ->assertOk()->assertJsonCount(0, 'messages')->assertDontSee('Bí mật');
    });

    it('tin quá 1000 ký tự bị từ chối', function () {
        $this->postJson(route('chat.send'), ['body' => str_repeat('a', 1001), 'name' => 'Lan'])->assertJsonValidationErrors('body');
    });

    it('tắt chat: API 404 và không hiện nút Chat', function () {
        $this->get('/')->assertSee('chat-launcher', false);

        Setting::query()->create(['key' => 'chat_enabled', 'value' => '0']);

        $this->getJson(route('chat.messages'))->assertNotFound();
        $this->postJson(route('chat.send'), ['body' => 'x', 'name' => 'Lan'])->assertNotFound();
        $this->get('/')->assertDontSee('chat-launcher', false);
    });
});

describe('chat phía quản trị', function () {
    it('staff thấy hội thoại (chưa đọc lên đầu), trả lời, khách nhận được; đóng hội thoại', function () {
        $this->postJson(route('chat.send'), ['body' => 'Cần tư vấn', 'name' => 'Lan']);
        $conversation = ChatConversation::query()->sole();
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('admin.chats.index'))->assertOk()->assertSee('Lan (khách)');
        $this->get(route('admin.chats.show', $conversation))->assertOk()->assertSee('Cần tư vấn');
        expect($conversation->messages()->first()->read_at)->not->toBeNull();

        $this->postJson(route('admin.chats.reply', $conversation), ['body' => 'Chào chị Lan'])->assertCreated();
        expect(ChatMessage::query()->latest('id')->first())
            ->sender_type->toBe(ChatMessage::FROM_STAFF)
            ->sender_id->toBe($staff->id);

        $this->patch(route('admin.chats.close', $conversation))->assertRedirect(route('admin.chats.index'));
        expect($conversation->fresh()->status)->toBe(ChatConversation::STATUS_CLOSED);
    });

    it('khách và khách hàng không vào được khu chat quản trị', function () {
        $conversation = ChatConversation::query()->create(['guest_token' => 'abc', 'guest_name' => 'X']);

        $this->get(route('admin.chats.index'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->customer()->create())->get(route('admin.chats.show', $conversation))->assertForbidden();
    });
});
