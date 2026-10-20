@extends('layouts.admin')

@section('title', 'Tin liên hệ')

@section('content')
    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Người gửi</th><th>Chủ đề</th><th>Ngày gửi</th><th>Trạng thái</th></tr></thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr @class(['fw-semibold' => ! $message->is_handled])>
                            <td>{{ $message->name }}<div class="small text-muted fw-normal">{{ $message->email }}</div></td>
                            <td><a href="{{ route('admin.contacts.show', $message) }}">{{ $message->subject }}</a></td>
                            <td>{{ $message->created_at->format('d/m/Y H:i') }}</td>
                            <td><span @class(['badge', 'text-bg-warning' => ! $message->is_handled, 'text-bg-success' => $message->is_handled])>{{ $message->is_handled ? 'Đã xử lý' : 'Chưa xử lý' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">Chưa có tin liên hệ.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $messages->links() }}
@endsection
