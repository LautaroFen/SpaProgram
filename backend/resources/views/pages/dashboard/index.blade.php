@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="w-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">Turnos de la semana</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $weekStart->isoFormat('D MMM') }} – {{ $weekEnd->isoFormat('D MMM YYYY') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('dashboard', ['week' => $weekStart->subWeek()->toDateString()], false) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
            >
                Semana anterior
            </a>
            <a
                href="{{ route('dashboard', ['week' => $weekStart->addWeek()->toDateString()], false) }}"
                class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800"
            >
                Semana siguiente
            </a>
        </div>
    </div>

    <div class="mt-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            @foreach ($days as $day)
                @php
                    $dateKey = $day->toDateString();
                    $items = $appointmentsByDay->get($dateKey, collect());
                @endphp

                <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="border-b border-slate-200 px-3 py-2 dark:border-slate-800">
                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $day->isoFormat('dddd') }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $day->isoFormat('D MMM') }}</div>
                    </div>

                    <div class="p-3 space-y-2 min-h-[220px]">
                        @forelse ($items as $appt)
                            <div class="rounded-lg border border-slate-200 p-2 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950/30 dark:hover:bg-slate-950/50">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $appt->start_at->format('H:i') }} – {{ $appt->end_at->format('H:i') }}
                                    </div>
                                    @php
                                        $statusLabel = [
                                            'scheduled' => 'Programado',
                                            'paid' => 'Pagado',
                                            'cancelled' => 'Cancelado',
                                            'no_show' => 'No asistió',
                                        ][$appt->status] ?? '—';

                                        $statusClass = match ($appt->status) {
                                            'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
                                            'scheduled' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
                                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
                                        };
                                    @endphp
                                    <span class="text-[11px] rounded-full px-2 py-0.5 {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $appt->client?->full_name ?? 'Cliente' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $appt->service?->name ?? 'Servicio' }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-400">Sin turnos</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
