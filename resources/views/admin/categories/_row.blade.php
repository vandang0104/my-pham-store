<tr>
    <td @class(['ps-6' => $child, 'fw-semibold' => ! $child])>{{ $child ? '↳ ' : '' }}{{ $category->name }}</td>
    <td class="text-muted small">{{ $category->slug }}</td>
    <td class="text-end">{{ $category->sort_order }}</td>
    <td class="text-end">{{ $category->products_count }}</td>
    <td>
        <span @class(['badge', 'text-bg-success' => $category->is_active, 'text-bg-secondary' => ! $category->is_active])>{{ $category->is_active ? 'Đang bật' : 'Đã tắt' }}</span>
    </td>
    <td class="text-end text-nowrap">
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
        @can('delete', $category)
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline"
                onsubmit="return confirm('Xoá danh mục “{{ $category->name }}”?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Xoá</button>
            </form>
        @endcan
    </td>
</tr>
