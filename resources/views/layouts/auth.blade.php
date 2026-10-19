<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    {{-- Khung tạm ở mức chức năng; giao diện hoàn chỉnh làm ở giai đoạn thiết kế --}}
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <p class="text-center mb-4">
                    <a href="{{ route('home') }}" class="fs-4 text-decoration-none text-dark">{{ config('app.name') }}</a>
                </p>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-4">@yield('title')</h1>

                        @if (session('status'))
                            <div class="alert alert-success" role="status">{{ session('status') }}</div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
