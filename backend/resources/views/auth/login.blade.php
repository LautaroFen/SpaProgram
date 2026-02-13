@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Iniciar sesión</h1>
            <p class="mt-1 text-sm text-slate-500">Ingresá con tu email y contraseña.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('login.store', [], false) }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                value="{{ old('email') }}"
                class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-slate-900"
            />
            @error('email')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-slate-900"
            />
            @error('password')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300" />
                Recordarme
            </label>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Entrar
        </button>

        <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
            <div class="font-semibold text-slate-700">Usuarios demo (desarrollo)</div>
            <div class="mt-1">
                Se crean por seed solo si <span class="font-mono">SPA_SEED_DEMO_USERS=true</span>.
            </div>
            <div class="mt-1">
                Emails/passwords se configuran en <span class="font-mono">.env</span> (SPA_DEMO_*). Si no, se generan contraseñas aleatorias y se imprimen en la consola al ejecutar el seeder.
            </div>
        </div>
    </form>
@endsection
