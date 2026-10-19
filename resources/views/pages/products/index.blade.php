{{--
    Danh sách sản phẩm — dùng chung cho /san-pham, /danh-muc/{slug}, /thuong-hieu/{slug} (Bootstrap mặc định).
    Bộ lọc cột trái (desktop), thu gọn bằng collapse trên điện thoại. Giá trị hiện tại lấy từ $filters đã chuẩn hoá.
--}}
@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="container py-6">
        <x-breadcrumb :items="$breadcrumb" class="mb-4" />

        <div class="mb-5">
            <h1 class="t-title mb-2">{{ $title }}</h1>
            @if ($brand?->description)
                <p class="text-muted mb-0">{{ $brand->description }}</p>
            @endif
            @if ($category && $category->children->isNotEmpty())
                <nav aria-label="Danh mục con" class="d-flex flex-wrap gap-2 mt-3">
                    @foreach ($category->children as $child)
                        <a href="{{ route('categories.show', $child) }}" class="btn btn-sm btn-outline-secondary">{{ $child->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        <div class="row g-5">
            <aside class="col-lg-3" aria-label="Bộ lọc sản phẩm">
                <button class="btn btn-outline-secondary w-100 d-lg-none mb-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#bo-loc" aria-expanded="false" aria-controls="bo-loc">Bộ lọc</button>

                <form id="bo-loc" method="GET" action="{{ url()->current() }}" class="collapse d-lg-block card card-body">
                    <div class="mb-4">
                        <label for="loc-q" class="form-label">Từ khoá</label>
                        <input id="loc-q" type="search" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Tên, mã SP, thương hiệu" maxlength="100">
                    </div>

                    @unless ($category)
                        <div class="mb-4">
                            <label for="loc-danh-muc" class="form-label">Danh mục</label>
                            <select id="loc-danh-muc" name="danh-muc" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                @foreach ($categoryOptions as $root)
                                    <optgroup label="{{ $root->name }}">
                                        <option value="{{ $root->slug }}" @selected($filters['category'] === $root->slug)>Tất cả {{ mb_strtolower($root->name) }}</option>
                                        @foreach ($root->children as $child)
                                            <option value="{{ $child->slug }}" @selected($filters['category'] === $child->slug)>{{ $child->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    @endunless

                    @unless ($brand)
                        <fieldset class="mb-4">
                            <legend class="form-label">Thương hiệu</legend>
                            @foreach ($brandOptions as $option)
                                <div class="form-check">
                                    <input id="loc-th-{{ $option->id }}" class="form-check-input" type="checkbox" name="thuong-hieu[]" value="{{ $option->slug }}"
                                        @checked(in_array($option->slug, $filters['brands'], true))>
                                    <label for="loc-th-{{ $option->id }}" class="form-check-label">{{ $option->name }}</label>
                                </div>
                            @endforeach
                        </fieldset>
                    @endunless

                    <fieldset class="mb-4">
                        <legend class="form-label">Khoảng giá (₫)</legend>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="loc-gia-tu" class="visually-hidden">Giá từ</label>
                                <input id="loc-gia-tu" type="number" name="gia-tu" class="form-control" min="0" step="1000" inputmode="numeric"
                                    placeholder="Từ" value="{{ $filters['price_from'] }}">
                            </div>
                            <div class="col-6">
                                <label for="loc-gia-den" class="visually-hidden">Giá đến</label>
                                <input id="loc-gia-den" type="number" name="gia-den" class="form-control" min="0" step="1000" inputmode="numeric"
                                    placeholder="Đến" value="{{ $filters['price_to'] }}">
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-check mb-4">
                        <input id="loc-khuyen-mai" class="form-check-input" type="checkbox" name="khuyen-mai" value="1" @checked($filters['on_sale'])>
                        <label for="loc-khuyen-mai" class="form-check-label">Đang giảm giá</label>
                    </div>

                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="{{ url()->current() }}" class="btn btn-link px-0">Xoá bộ lọc</a>
                    </div>
                </form>
            </aside>

            <div class="col-lg-9">
                <p class="text-muted mb-4" role="status">{{ $products->total() }} sản phẩm</p>

                @if ($products->isEmpty())
                    <x-empty-state title="Không tìm thấy sản phẩm phù hợp" text="Thử bỏ bớt bộ lọc hoặc tìm bằng từ khoá khác."
                        :action-url="url()->current()" action-label="Xoá bộ lọc" />
                @else
                    <div class="row row-cols-2 row-cols-md-3 g-3">
                        @foreach ($products as $i => $product)
                            <div class="col">
                                <x-product-card :product="$product" :index="$i" :heading-level="2" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
