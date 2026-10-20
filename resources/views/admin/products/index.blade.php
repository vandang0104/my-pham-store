@extends('layouts.admin')

@section('title', 'Sản phẩm')

@section('content')
    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="q" class="form-label">Tìm tên / SKU</label>
            <input id="q" name="q" type="search" class="form-control" value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <label for="danh-muc" class="form-label">Danh mục</label>
            <select id="danh-muc" name="danh-muc" class="form-select">
                <option value="">Tất cả</option>
                @foreach ($categories as $root)
                    <option value="{{ $root->id }}" @selected($filters['danh-muc'] === $root->id)>{{ $root->name }}</option>
                    @foreach ($root->children as $child)
                        <option value="{{ $child->id }}" @selected($filters['danh-muc'] === $child->id)>— {{ $child->name }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="trang-thai" class="form-label">Trạng thái</label>
            <select id="trang-thai" name="trang-thai" class="form-select">
                <option value="">Tất cả (chưa xoá)</option>
                <option value="dang-ban" @selected($filters['trang-thai'] === 'dang-ban')>Đang bán</option>
                <option value="an" @selected($filters['trang-thai'] === 'an')>Đang ẩn</option>
                <option value="da-xoa" @selected($filters['trang-thai'] === 'da-xoa')>Đã xoá</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-outline-secondary">Lọc</button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary ms-auto">Thêm sản phẩm</a>
        </div>
    </form>

    <p class="text-muted small">{{ $products->total() }} sản phẩm</p>
    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Ảnh</th><th>Tên / SKU</th><th>Danh mục</th><th class="text-end">Giá</th><th class="text-end">Tồn</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->primary_image)
                                    <img src="{{ $product->primary_image->url }}" alt="{{ $product->name }}" width="40" height="50" class="object-fit-cover" loading="lazy">
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">{{ $product->name }}</div>
                                <div class="small text-muted">{{ $product->sku }} · {{ $product->brand?->name }}</div>
                            </td>
                            <td class="small">{{ $product->category?->name }}</td>
                            <td class="text-end"><x-price :product="$product" size="sm" class="mb-0" /></td>
                            <td class="text-end">{{ $product->stock }}</td>
                            <td>
                                @if ($product->trashed())
                                    <span class="badge text-bg-dark">Đã xoá</span>
                                @elseif ($product->is_active)
                                    <span class="badge text-bg-success">Đang bán</span>
                                @else
                                    <span class="badge text-bg-secondary">Đang ẩn</span>
                                @endif
                                @if ($product->is_featured) <span class="badge text-bg-info">Nổi bật</span> @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
                                @if ($product->trashed())
                                    @can('restore', $product)
                                        <form method="POST" action="{{ route('admin.products.restore', $product) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Khôi phục</button>
                                        </form>
                                    @endcan
                                @else
                                    @can('delete', $product)
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline"
                                            onsubmit="return confirm('Xoá (mềm) sản phẩm “{{ $product->name }}”?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Xoá</button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Không có sản phẩm phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $products->links() }}
@endsection
