@extends('layouts.admin')

@section('title', $message->subject)

@section('content')
    <a href="{{ route('admin.contacts.index') }}" class="small d-inline-block mb-4">← Danh sách tin liên hệ</a>

    <div class="card card-body col-xl-8">
        <p class="mb-1"><strong>{{ $message->name }}</strong> · <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
            @if ($message->phone) · {{ $message->phone }} @endif</p>
        <p class="small text-muted mb-4">Gửi lúc {{ $message->created_at->format('d/m/Y H:i') }}</p>
        <div class="mb-5" style="white-space: pre-line">{{ $message->message }}</div>

        @if ($message->is_handled)
            <span class="badge text-bg-success align-self-start">Đã xử lý</span>
        @else
            <form method="POST" action="{{ route('admin.contacts.handled', $message) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary">Đánh dấu đã xử lý</button>
            </form>
        @endif
    </div>
@endsection
