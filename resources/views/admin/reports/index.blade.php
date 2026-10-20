@extends('layouts.admin')

@section('title', 'Thống kê doanh thu')

@section('content')
    <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end mb-5">
        <div class="col-auto">
            <label for="tu-ngay" class="form-label">Từ ngày</label>
            <input id="tu-ngay" name="tu-ngay" type="date" class="form-control" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <label for="den-ngay" class="form-label">Đến ngày</label>
            <input id="den-ngay" name="den-ngay" type="date" class="form-control" value="{{ $to->format('Y-m-d') }}">
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-outline-secondary">Xem</button></div>
        <div class="col-12 small text-muted">Chỉ tính đơn đã giao, theo ngày đặt hàng.</div>
    </form>

    <div class="card card-body mb-5">
        <h2 class="h5 mb-1">Doanh thu theo ngày ({{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }})</h2>
        <p class="mb-3">Tổng: <strong data-daily-total>{{ $dailyTotal }}</strong></p>
        <canvas data-chart='@json(['labels' => $daily['labels'], 'data' => $daily['data'], 'label' => 'Doanh thu (₫)'])' height="90"
            role="img" aria-label="Biểu đồ đường doanh thu theo ngày"></canvas>
    </div>

    <div class="row g-5">
        <div class="col-xl-6">
            <div class="card card-body">
                <h2 class="h5 mb-1">Doanh thu 12 tháng gần nhất</h2>
                <p class="mb-3">Tổng: <strong>{{ $monthlyTotal }}</strong></p>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Tháng</th><th class="text-end">Doanh thu</th></tr></thead>
                        <tbody>
                            @foreach ($monthly['labels'] as $i => $label)
                                <tr><td>{{ $label }}</td><td class="text-end">{{ $money($monthly['data'][$i]) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-body">
                <h2 class="h5 mb-3">Top 10 bán chạy (trong khoảng ngày)</h2>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>#</th><th>Sản phẩm</th><th class="text-end">SL bán</th><th class="text-end">Doanh thu</th></tr></thead>
                        <tbody>
                            @forelse ($top as $i => $row)
                                <tr><td>{{ $i + 1 }}</td><td>{{ $row->product_name }}</td><td class="text-end">{{ $row->quantity }}</td><td class="text-end">{{ $row->formatted_revenue }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">Chưa có đơn đã giao trong khoảng này.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
