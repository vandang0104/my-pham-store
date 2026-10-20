{{-- Bảo trì (503): trang đứng riêng (không dùng layout cửa hàng vì header đọc DB — DB có thể chính là chỗ đang lỗi) --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đang bảo trì · {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; background: #F5F2EC; color: #1E1B18; }
        main { max-width: 32rem; margin: 15vh auto; padding: 0 1rem; text-align: center; }
        a { color: #2E5A4C; }
    </style>
</head>
<body>
    <main>
        <h1>Cửa hàng đang bảo trì</h1>
        <p>Chúng tôi đang cập nhật hệ thống, vui lòng quay lại sau ít phút.</p>
        <p><a href="{{ url('/') }}">Về trang chủ</a></p>
    </main>
</body>
</html>
