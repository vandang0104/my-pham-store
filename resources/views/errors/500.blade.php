{{-- Lỗi máy chủ: trang đứng riêng (không dùng layout cửa hàng vì header đọc DB — DB có thể chính là chỗ đang lỗi) --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đã có lỗi xảy ra · {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; background: #F5F2EC; color: #1E1B18; }
        main { max-width: 32rem; margin: 15vh auto; padding: 0 1rem; text-align: center; }
        a { color: #2E5A4C; }
    </style>
</head>
<body>
    <main>
        <h1>Đã có lỗi xảy ra</h1>
        <p>Hệ thống đang gặp sự cố. Vui lòng thử lại sau ít phút.</p>
        <p><a href="{{ url('/') }}">Về trang chủ</a></p>
    </main>
</body>
</html>
