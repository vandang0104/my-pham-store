@extends('layouts.admin')

@section('title', $product->exists ? 'Sửa sản phẩm' : 'Thêm sản phẩm')

@section('content')
    @if ($product->trashed())
        <div class="alert alert-warning">Sản phẩm này đã bị xoá (mềm), khách không thấy. Khôi phục ở danh sách sản phẩm (lọc “Đã xoá”).</div>
    @endif

    <form method="POST" enctype="multipart/form-data" class="card card-body" novalidate
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-md-8">
                <label for="name" class="form-label">Tên sản phẩm <span class="form-required">(bắt buộc)</span></label>
                <input id="name" name="name" type="text" maxlength="200" required class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="sku" class="form-label">Mã SKU <span class="form-required">(bắt buộc)</span></label>
                <input id="sku" name="sku" type="text" maxlength="50" required class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}">
                @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="slug" class="form-label">Đường dẫn (slug)</label>
                <input id="slug" name="slug" type="text" maxlength="220" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}" aria-describedby="slug-help">
                <div id="slug-help" class="form-text">Bỏ trống để tự sinh từ tên (không dấu, duy nhất).</div>
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label">Danh mục <span class="form-required">(bắt buộc)</span></label>
                <select id="category_id" name="category_id" required class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">— Chọn —</option>
                    @foreach ($categories as $root)
                        <optgroup label="{{ $root->name }}">
                            <option value="{{ $root->id }}" @selected((int) old('category_id', $product->category_id) === $root->id)>{{ $root->name }}</option>
                            @foreach ($root->children as $child)
                                <option value="{{ $child->id }}" @selected((int) old('category_id', $product->category_id) === $child->id)>{{ $child->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label for="brand_id" class="form-label">Thương hiệu <span class="form-required">(bắt buộc)</span></label>
                <select id="brand_id" name="brand_id" required class="form-select @error('brand_id') is-invalid @enderror">
                    <option value="">— Chọn —</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected((int) old('brand_id', $product->brand_id) === $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label for="price" class="form-label">Giá (₫) <span class="form-required">(bắt buộc)</span></label>
                <input id="price" name="price" type="number" min="1000" step="1000" required class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}">
                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label for="sale_price" class="form-label">Giá khuyến mãi (₫)</label>
                <input id="sale_price" name="sale_price" type="number" min="0" step="1000" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $product->sale_price) }}">
                @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-2">
                <label for="stock" class="form-label">Tồn kho <span class="form-required">(bắt buộc)</span></label>
                <input id="stock" name="stock" type="number" min="0" required class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}">
                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-2">
                <label for="volume" class="form-label">Dung tích</label>
                <input id="volume" name="volume" type="text" maxlength="50" class="form-control @error('volume') is-invalid @enderror" value="{{ old('volume', $product->volume) }}">
                @error('volume') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-2">
                <label for="skin_type" class="form-label">Loại da</label>
                <select id="skin_type" name="skin_type" class="form-select @error('skin_type') is-invalid @enderror">
                    <option value="">—</option>
                    @foreach ($skinTypes as $skinType)
                        <option value="{{ $skinType->value }}" @selected(old('skin_type', $product->skin_type?->value) === $skinType->value)>{{ $skinType->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label for="short_description" class="form-label">Mô tả ngắn</label>
                <textarea id="short_description" name="short_description" rows="2" maxlength="500" class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $product->short_description) }}</textarea>
                @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Mô tả (Markdown)</label>
                <textarea id="description" name="description" rows="8" class="form-control font-monospace @error('description') is-invalid @enderror" aria-describedby="description-help">{{ old('description', $product->description) }}</textarea>
                <div id="description-help" class="form-text">Dùng ## cho tiêu đề, - cho danh sách. HTML bị loại bỏ khi hiển thị.</div>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="ingredients" class="form-label">Thành phần</label>
                <textarea id="ingredients" name="ingredients" rows="4" class="form-control">{{ old('ingredients', $product->ingredients) }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="usage_guide" class="form-label">Hướng dẫn sử dụng</label>
                <textarea id="usage_guide" name="usage_guide" rows="4" class="form-control">{{ old('usage_guide', $product->usage_guide) }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="image" class="form-label">Ảnh chính</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control @error('image') is-invalid @enderror" aria-describedby="image-help">
                <div id="image-help" class="form-text">JPG/PNG/WebP ≤ 2 MB. Chọn ảnh mới để thay ảnh chính hiện tại.</div>
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if ($product->exists && $product->primary_image)
                    <img src="{{ $product->primary_image->url }}" alt="{{ $product->name }}" width="96" height="120" class="object-fit-cover mt-3" data-current-image>
                @endif
            </div>
            <div class="col-md-6 d-flex flex-column justify-content-center gap-2">
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input" @checked(old('is_active', $product->is_active))>
                    <label for="is_active" class="form-check-label">Đang bán (hiện cho khách)</label>
                </div>
                <div class="form-check">
                    <input type="hidden" name="is_featured" value="0">
                    <input id="is_featured" name="is_featured" type="checkbox" value="1" class="form-check-input" @checked(old('is_featured', $product->is_featured))>
                    <label for="is_featured" class="form-check-label">Sản phẩm nổi bật (trang chủ)</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mt-5">
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-link">Về danh sách</a>
        </div>
    </form>
@endsection
