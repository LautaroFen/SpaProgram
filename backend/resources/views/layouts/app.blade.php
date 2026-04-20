<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LogoPWA.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <script>
        (() => {
            try {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const useDark = stored ? stored === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', useDark);
            } catch (e) {
                // ignore
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 dark:bg-slate-950/90 dark:border-slate-800">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button
                        id="mobileMenuButton"
                        type="button"
                        class="md:hidden inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                        aria-label="Abrir menú"
                        aria-expanded="false"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16" />
                            <path d="M4 12h16" />
                            <path d="M4 18h16" />
                        </svg>
                    </button>

                    <a href="{{ route('dashboard', [], false) }}" class="flex items-center gap-2 font-semibold tracking-tight">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white dark:bg-slate-950">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt=""
                                class="h-10 w-10 object-contain"
                                aria-hidden="true"
                            />
                        </span>
                        <span class="hidden sm:inline">DayOff</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <span class="hidden sm:inline text-sm text-slate-600 dark:text-slate-300">
                            {{ trim(auth()->user()->first_name.' '.auth()->user()->last_name) ?: 'Usuario' }}
                        </span>

                        <button
                            type="button"
                            data-theme-toggle
                            class="inline-flex items-center justify-center rounded-md bg-white p-2 text-slate-700 hover:bg-slate-50 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                            aria-pressed="false"
                        >
                            <img
                                src="{{ asset('images/modo-oscuro.png') }}"
                                alt=""
                                class="h-6 w-6 dark:hidden"
                                aria-hidden="true"
                            />
                            <img
                                src="{{ asset('images/modo-claro.png') }}"
                                alt=""
                                class="hidden h-6 w-6 dark:inline-block"
                                aria-hidden="true"
                            />
                        </button>

                        <form method="POST" action="{{ route('logout', [], false) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900">
                                Salir
                            </button>
                        </form>
                    @endauth
                </div>
            </div>

            <div id="mobileMenu" class="md:hidden hidden pb-3">
                <nav class="grid gap-1 rounded-lg border border-slate-200 bg-slate-50 p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900/40">
                    @php
                        $authUser = auth()->user();
                        $isAdmin = $authUser?->isAdmin() ?? false;
                        $isReception = $authUser?->isReceptionist() ?? false;
                        $canSeeClients = $isAdmin || $isReception;
                        $canSeePayments = $isAdmin || $isReception;
                        $canSeeUsers = $isAdmin;
                        $canSeeServices = $isAdmin;
                        $canSeeExpenses = $isAdmin;
                        $canSeeAudit = $isAdmin;
                    @endphp
                    <x-nav-link class="w-full" :href="route('dashboard', [], false)" :active="request()->routeIs('dashboard')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 10.5 12 3l9 7.5" />
                                <path d="M5 10v10h14V10" />
                            </svg>
                        </x-slot:icon>
                        Inicio
                    </x-nav-link>
                    @if ($canSeeUsers)
                        <x-nav-link class="w-full" :href="route('users.index', [], false)" :active="request()->routeIs('users.*')">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M19 8v6" />
                                    <path d="M22 11h-6" />
                                </svg>
                            </x-slot:icon>
                            Usuarios
                        </x-nav-link>
                    @endif
                    @if ($canSeeClients)
                        <x-nav-link class="w-full" :href="route('clients.index', [], false)" :active="request()->routeIs('clients.*')">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </x-slot:icon>
                            Clientes
                        </x-nav-link>
                    @endif
                    <x-nav-link class="w-full" :href="route('appointments.index', [], false)" :active="request()->routeIs('appointments.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <path d="M16 2v4" />
                                <path d="M8 2v4" />
                                <path d="M3 10h18" />
                                <path d="M12 14v4" />
                                <path d="M10 16h4" />
                            </svg>
                        </x-slot:icon>
                        Turnos
                    </x-nav-link>
                    @if ($canSeeServices)
                        <x-nav-link class="w-full" :href="route('services.index', [], false)" :active="request()->routeIs('services.*')">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 2v2" />
                                    <path d="M12 20v2" />
                                    <path d="M4.93 4.93l1.41 1.41" />
                                    <path d="M17.66 17.66l1.41 1.41" />
                                    <path d="M2 12h2" />
                                    <path d="M20 12h2" />
                                    <path d="M4.93 19.07l1.41-1.41" />
                                    <path d="M17.66 6.34l1.41-1.41" />
                                    <circle cx="12" cy="12" r="4" />
                                </svg>
                            </x-slot:icon>
                            Servicios
                        </x-nav-link>
                    @endif
                    @if ($canSeePayments)
                        <x-nav-link class="w-full" :href="route('payments.index', [], false)" :active="request()->routeIs('payments.*')">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <path d="M2 10h20" />
                                    <path d="M6 15h2" />
                                </svg>
                            </x-slot:icon>
                            Pagos
                        </x-nav-link>
                    @endif
                    @if ($canSeeExpenses)
                        <x-nav-link class="w-full" :href="route('expenses.index', [], false)" :active="request()->routeIs('expenses.*')">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 2v20" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                            </x-slot:icon>
                            Expensas
                        </x-nav-link>
                    @endif
                    @if ($canSeeAudit)
                        <x-nav-link class="w-full" :href="route('audit-logs.index', [], false)" :active="request()->routeIs('audit-logs.*')">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 18h6" />
                                    <path d="M10 22h4" />
                                    <path d="M12 2a7 7 0 0 0-4 12.74V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.26A7 7 0 0 0 12 2z" />
                                </svg>
                            </x-slot:icon>
                            Auditoría
                        </x-nav-link>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    <aside class="hidden md:block fixed inset-y-0 left-0 w-60 bg-slate-50 pt-14 z-20 dark:bg-slate-900/40">
        <div class="h-full overflow-y-auto">
            <div class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Menú</div>
            <nav class="flex flex-col items-center gap-1 px-3">
                @php
                    $authUser = auth()->user();
                    $isAdmin = $authUser?->isAdmin() ?? false;
                    $isReception = $authUser?->isReceptionist() ?? false;
                    $canSeeClients = $isAdmin || $isReception;
                    $canSeePayments = $isAdmin || $isReception;
                    $canSeeUsers = $isAdmin;
                    $canSeeServices = $isAdmin;
                    $canSeeExpenses = $isAdmin;
                    $canSeeAudit = $isAdmin;
                @endphp
                <x-nav-link class="w-11/12 justify-start" :href="route('dashboard', [], false)" :active="request()->routeIs('dashboard')">
                    <x-slot:icon>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 10.5 12 3l9 7.5" />
                            <path d="M5 10v10h14V10" />
                        </svg>
                    </x-slot:icon>
                    Inicio
                </x-nav-link>
                @if ($canSeeUsers)
                    <x-nav-link class="w-11/12 justify-start" :href="route('users.index', [], false)" :active="request()->routeIs('users.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M19 8v6" />
                                <path d="M22 11h-6" />
                            </svg>
                        </x-slot:icon>
                        Usuarios
                    </x-nav-link>
                @endif
                @if ($canSeeClients)
                    <x-nav-link class="w-11/12 justify-start" :href="route('clients.index', [], false)" :active="request()->routeIs('clients.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </x-slot:icon>
                        Clientes
                    </x-nav-link>
                @endif
                <x-nav-link class="w-11/12 justify-start" :href="route('appointments.index', [], false)" :active="request()->routeIs('appointments.*')">
                    <x-slot:icon>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <path d="M16 2v4" />
                            <path d="M8 2v4" />
                            <path d="M3 10h18" />
                            <path d="M12 14v4" />
                            <path d="M10 16h4" />
                        </svg>
                    </x-slot:icon>
                    Turnos
                </x-nav-link>
                @if ($canSeeServices)
                    <x-nav-link class="w-11/12 justify-start" :href="route('services.index', [], false)" :active="request()->routeIs('services.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 2v2" />
                                <path d="M12 20v2" />
                                <path d="M4.93 4.93l1.41 1.41" />
                                <path d="M17.66 17.66l1.41 1.41" />
                                <path d="M2 12h2" />
                                <path d="M20 12h2" />
                                <path d="M4.93 19.07l1.41-1.41" />
                                <path d="M17.66 6.34l1.41-1.41" />
                                <circle cx="12" cy="12" r="4" />
                            </svg>
                        </x-slot:icon>
                        Servicios
                    </x-nav-link>
                @endif
                @if ($canSeePayments)
                    <x-nav-link class="w-11/12 justify-start" :href="route('payments.index', [], false)" :active="request()->routeIs('payments.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <path d="M2 10h20" />
                                <path d="M6 15h2" />
                            </svg>
                        </x-slot:icon>
                        Pagos
                    </x-nav-link>
                @endif
                @if ($canSeeExpenses)
                    <x-nav-link class="w-11/12 justify-start" :href="route('expenses.index', [], false)" :active="request()->routeIs('expenses.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 2v20" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </x-slot:icon>
                        Expensas
                    </x-nav-link>
                @endif
                @if ($canSeeAudit)
                    <x-nav-link class="w-11/12 justify-start" :href="route('audit-logs.index', [], false)" :active="request()->routeIs('audit-logs.*')">
                        <x-slot:icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 18h6" />
                                <path d="M10 22h4" />
                                <path d="M12 2a7 7 0 0 0-4 12.74V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.26A7 7 0 0 0 12 2z" />
                            </svg>
                        </x-slot:icon>
                        Auditoría
                    </x-nav-link>
                @endif
            </nav>
        </div>
    </aside>

    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
        <main class="py-6 lg:py-8 md:pl-60">
            @php
                $contentContainerClass = trim($__env->yieldContent(
                    'contentContainerClass',
                    'rounded-xl border border-slate-200 bg-white p-4 sm:p-6 lg:p-8 dark:border-slate-800 dark:bg-slate-950'
                ));
            @endphp

            @if ($contentContainerClass === 'none')
                @yield('content')
            @else
                <div class="{{ $contentContainerClass }}">
                    @yield('content')
                </div>
            @endif
        </main>
    </div>
</body>
</html>
