@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Panel principal</h1>
            <p class="mt-1 text-sm text-slate-500">Elegí una opción del menú para trabajar.</p>
        </div>
        <div class="hidden sm:flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Sistema OK</span>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('clients.index') }}" class="rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
            <div class="text-sm font-semibold">Clientes</div>
            <div class="mt-1 text-sm text-slate-500">Altas, búsqueda y deuda.</div>
        </a>
        <a href="{{ route('appointments.index') }}" class="rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
            <div class="text-sm font-semibold">Turnos</div>
            <div class="mt-1 text-sm text-slate-500">Agenda, estados y anticipos.</div>
        </a>
        <a href="{{ route('payments.index') }}" class="rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
            <div class="text-sm font-semibold">Pagos</div>
            <div class="mt-1 text-sm text-slate-500">Registrar pagos y aplicar descuentos.</div>
        </a>
    </div>
@endsection
