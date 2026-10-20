@extends('layouts.admin')

@section('title', 'Danh mục')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Thêm danh mục</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Tên</th><th>Đường dẫn</th><th class="text-end">Thứ tự</th><th class="text-end">Sản phẩm</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr>
                </thead>
                <tbody>
                    @forelse ($roots as $root)
                        @include('admin.categories._row', ['category' => $root, 'child' => false])
                        @foreach ($root->children as $child)
                            @include('admin.categories._row', ['category' => $child, 'child' => true])
                        @endforeach
                    @empty
                        <tr><td colspan="6" class="text-muted">Chưa có danh mục.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
