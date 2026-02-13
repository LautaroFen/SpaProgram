<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="flex items-center justify-center gap-2 mb-6">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white font-semibold">SP</div>
                <div class="text-lg font-semibold tracking-tight">SpaProgram</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @yield('content')
            </div>

            <div class="mt-4 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} SpaProgram
            </div>
        </div>
    </div>
</body>
</html>
