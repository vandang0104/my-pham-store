{{-- Câu hỏi thường gặp (Bootstrap accordion, mở/đóng bằng collapse có sẵn của Bootstrap JS). --}}
@extends('layouts.app')

@section('title', 'Câu hỏi thường gặp')

@section('content')
    <div class="container py-6">
        <x-breadcrumb :items="[['label' => 'Câu hỏi thường gặp']]" class="mb-4" />
        <h1 class="t-title mb-5">Câu hỏi thường gặp</h1>

        @if ($groups->isEmpty())
            <x-empty-state title="Chưa có câu hỏi nào" text="Bạn có thể gửi câu hỏi cho chúng tôi qua trang liên hệ."
                :action-url="route('contact.create')" action-label="Liên hệ" />
        @else
            <div class="row">
                <div class="col-lg-9">
                    @foreach ($groups as $group => $faqs)
                        <section class="mb-6" aria-labelledby="faq-group-{{ $loop->index }}">
                            <h2 id="faq-group-{{ $loop->index }}" class="h5 mb-3">{{ $group }}</h2>
                            <div class="accordion" id="faq-{{ $loop->index }}">
                                @foreach ($faqs as $faq)
                                    <div class="accordion-item">
                                        <h3 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#faq-item-{{ $faq->id }}" aria-expanded="false" aria-controls="faq-item-{{ $faq->id }}">
                                                {{ $faq->question }}
                                            </button>
                                        </h3>
                                        <div id="faq-item-{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faq-{{ $loop->parent->index }}">
                                            <div class="accordion-body">{{ $faq->answer }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <p class="text-muted">Chưa tìm thấy câu trả lời? <a href="{{ route('contact.create') }}">Gửi câu hỏi cho chúng tôi</a>.</p>
                </div>
            </div>
        @endif
    </div>
@endsection
