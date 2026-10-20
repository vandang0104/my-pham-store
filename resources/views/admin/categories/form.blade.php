@extends('layouts.admin')

@section('title', $category->exists ? 'Sửa danh mục' : 'Thêm danh mục')

@section('content')
    <form method="POST" enctype="multipart/form-data" class="card card-body col-xl-8" novalidate
        action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-md-6">
                <label for="name" class="form-label">Tên danh mục <span class="form-required">(bắt buộc)</span></label>
                <input id="name" name="name" type="text" maxlength="150" required class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="slug" class="form-label">Đường dẫn (slug)</label>
                <input id="slug" name="slug" type="text" maxlength="170" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}" aria-describedby="slug-help">
                <div id="slug-help" class="form-text">Bỏ trống để tự sinh từ tên (không dấu, duy nhất).</div>
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="parent_id" class="form-label">Danh mục cha</label>
                <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">— Danh mục gốc —</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label for="sort_order" class="form-label">Thứ tự</label>
                <input id="sort_order" name="sort_order" type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input" @checked(old('is_active', $category->is_active))>
                    <label for="is_active" class="form-check-label">Đang bật</label>
                </div>
            </div>
            <div class="col-12">
                <label for="image" class="form-label">Ảnh (không bắt buộc)</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control @error('image') is-invalid @enderror">
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-3 mt-5">
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-link">Huỷ</a>
        </div>
    </form>
@endsection
