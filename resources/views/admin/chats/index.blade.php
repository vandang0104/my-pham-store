@extends('layouts.admin')

@section('title', 'Chat với khách')

@section('content')
    @if ($conversations->isEmpty())
        <p class="text-muted">Chưa có hội thoại nào.</p>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Khách</th>
                        <th scope="col">Tin cuối</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col" class="text-end">Chưa đọc</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conversations as $conversation)
                        <tr>
                            <td><a href="{{ route('admin.chats.show', $conversation) }}">{{ $conversation->displayName() }}</a></td>
                            <td>{{ $conversation->last_message_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $conversation->status === 'open' ? 'Đang mở' : 'Đã đóng' }}</td>
                            <td class="text-end">
                                @if ($conversation->unread_count > 0)
                                    <span class="badge text-bg-danger">{{ $conversation->unread_count }}</span>
                                @else
                                    0
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $conversations->links() }}
    @endif
    <a href="{{ route('admin.dashboard') }}">Về trang quản trị</a>
@endsection
