<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de email</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-xl px-4 py-10">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h1 class="text-lg font-semibold text-slate-900">Verificación de email</h1>
            <p class="mt-2 text-sm text-slate-700">{{ $message ?? '—' }}</p>
            <p class="mt-4 text-sm text-slate-600">
                Esta pestaña se cerrará automáticamente en 10 segundos. Si no se cierra, podés cerrarla manualmente.
            </p>
        </div>
    </div>

    <script>
        // Nota: algunos navegadores bloquean window.close() si la pestaña no fue abierta por script.
        setTimeout(() => {
            try {
                window.close();
            } catch (e) {
                // ignore
            }
        }, 10000);
    </script>
</body>
</html>
