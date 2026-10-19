{{--
    Chi tiết sản phẩm (Bootstrap mặc định): ảnh chính + ảnh phụ (đổi ảnh bằng resources/js/gallery.js, không lightbox),
    thông tin, giá, tồn kho, thêm vào giỏ, mô tả (Markdown đã lọc), thành phần, hướng dẫn, sản phẩm liên quan.
--}}
@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', $product->short_description ?? $product->name)

@section('content')
    <div class="container py-6">
        <x-breadcrumb :items="$breadcrumb" class="mb-5" />

        <div class="row g-6">
            <div class="col-md-6" data-gallery>
                @if ($product->primary_image)
                    <div class="ratio product-detail__main bg-body-tertiary">
                        <img src="{{ $product->primary_image->url }}" alt="{{ $product->name }}" class="object-fit-cover" width="800" height="1000" data-gallery-main>
                    </div>
                    @if ($product->images->count() > 1)
                        <div class="d-flex flex-wrap gap-2 mt-3" role="group" aria-label="Ảnh sản phẩm">
                            @foreach ($product->images as $i => $image)
                                <button type="button" class="product-detail__thumb p-0 border-0" data-gallery-thumb data-src="{{ $image->url }}"
                                    aria-label="Xem ảnh {{ $i + 1 }}" @if ($i === 0) aria-current="true" @endif>
                                    <img src="{{ $image->url }}" alt="" width="80" height="100" loading="lazy" class="object-fit-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="ratio product-detail__main bg-body-tertiary"><div></div></div>
                @endif
            </div>

            <div class="col-md-6">
                @if ($product->brand)
                    <p class="mb-2"><a href="{{ route('brands.show', $product->brand) }}" class="link-secondary">{{ $product->brand->name }}</a></p>
                @endif
                <h1 class="t-title mb-3">{{ $product->name }}</h1>
                <p class="text-muted small mb-4">
                    Mã SP: {{ $product->sku }}
                    @if ($product->volume) · {{ $product->volume }} @endif
                    @if ($product->skin_type) · {{ $product->skin_type->label() }} @endif
                </p>

                <x-price :product="$product" size="lg" class="mb-3" />
                @if ($product->is_on_sale)
                    <x-discount-badge :percent="$product->discount_percent" :decorative="false" class="mb-3" />
                @endif

                <p class="mb-4">
                    @if ($product->stock > 0)
                        <span class="badge text-bg-success">Còn hàng</span>
                    @else
                        <span class="badge text-bg-secondary">Hết hàng</span>
                    @endif
                </p>

                @if ($product->short_description)
                    <p class="mb-5">{{ $product->short_description }}</p>
                @endif

                {{-- Giỏ hàng làm ở S2: form đã đúng route POST /gio-hang, nút tạm khoá tới khi có CartController --}}
                <form method="POST" action="{{ url('/gio-hang') }}" class="row g-3 align-items-end">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="col-auto">
                        <label for="so-luong" class="form-label">Số lượng</label>
                        <input id="so-luong" type="number" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}" class="form-control product-detail__qty" @disabled($product->stock <= 0)>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" disabled>{{ $product->stock > 0 ? 'Thêm vào giỏ' : 'Hết hàng' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-8">
            <div class="col-lg-8">
                @if ($description)
                    <section class="mb-6" aria-labelledby="mo-ta">
                        <h2 id="mo-ta" class="h4 mb-3">Mô tả</h2>
                        <div class="product-detail__prose">{!! $description !!}</div>
                    </section>
                @endif
                @if ($product->ingredients)
                    <section class="mb-6" aria-labelledby="thanh-phan">
                        <h2 id="thanh-phan" class="h4 mb-3">Thành phần</h2>
                        <p>{{ $product->ingredients }}</p>
                    </section>
                @endif
                @if ($usageGuide)
                    <section class="mb-6" aria-labelledby="huong-dan">
                        <h2 id="huong-dan" class="h4 mb-3">Hướng dẫn sử dụng</h2>
                        <div class="product-detail__prose">{!! $usageGuide !!}</div>
                    </section>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-8" aria-labelledby="lien-quan">
                <h2 id="lien-quan" class="t-title mb-5">Sản phẩm liên quan</h2>
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    @foreach ($related as $i => $item)
                        <div class="col">
                            <x-product-card :product="$item" :index="$i" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
