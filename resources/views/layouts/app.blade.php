<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="{{ $bodyClass ?? '' }}">
@if(isset($isCentered) && $isCentered)
    <div class="d-flex justify-content-center align-items-center vh-100">
        @yield('content')
    </div>
@else
    <div class="container py-4">
        @yield('content')
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@stack('scripts')
</body>
</html>
