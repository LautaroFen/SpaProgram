@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Auditoría</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Historial de modificaciones realizadas por los usuarios.</p>
        </div>

        <div class="sm:pt-1">
            <details class="group">
                <summary class="list-none">
                    <span class="inline-flex cursor-pointer select-none items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Exportar
                    </span>
                </summary>

                <div class="mt-3 w-full rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                    <div class="font-semibold">Antes de confirmar</div>
                    <div class="mt-1 text-sm opacity-90">
                        La exportación genera un ZIP con CSV (compatible con Excel).
                    </div>

                    <div class="mt-3 text-sm">
                        Si confirmás, se van a borrar registros de:
                        <span class="font-semibold">auditoría</span>, <span class="font-semibold">expensas</span> y <span class="font-semibold">turnos pagados</span>.
                        <div class="mt-1 opacity-90">
                            No se borran: clientes, usuarios, servicios, ni turnos programados o con pago parcial.
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <iframe name="auditExportFrame" class="hidden"></iframe>

                        <form method="POST" target="auditExportFrame" data-export-reload action="{{ route('audit-logs.export', [], false) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900">
                                Exportar sin borrar
                            </button>
                        </form>

                        <form method="POST" target="auditExportFrame" data-export-reload action="{{ route('audit-logs.export-and-purge', [], false) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                                Confirmar exportar y borrar
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
        <form method="get" action="{{ route('audit-logs.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex-1">
                <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar</label>
                <input
                    id="q"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                    placeholder="Entidad log, usuario, acción, entidad o entidad asociada"
                />
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Filtrar
                </button>
                <a href="{{ route('audit-logs.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="overflow-x-auto">
            <table class="table-grid min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-900 dark:bg-slate-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Acción</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Entidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($logs as $log)
                        @php
                            $entityLabel = match ($log->entity_type) {
                                'App\\Models\\Client' => 'Cliente',
                                'App\\Models\\Service' => 'Servicio',
                                'App\\Models\\Appointment' => 'Turno',
                                'App\\Models\\Payment' => 'Pago',
                                'App\\Models\\Expense' => 'Expensa',
                                'App\\Models\\User' => 'Usuario',
                                'App\\Models\\Role' => 'Rol',
                                default => $log->entity_type,
                            };

                            $actionLabel = match ($log->action) {
                                'create' => 'Creación',
                                'payment.create' => 'Registro de pago',
                                'appointment.status_change' => 'Cambio de estado',
                                'role.create' => 'Creación de rol',
                                default => $log->action,
                            };

                            $detail = '';
                            if (is_array($log->metadata ?? null)) {
                                $meta = $log->metadata;
                                if (!empty($meta['summary'])) {
                                    $detail = (string) $meta['summary'];
                                } else {
                                    $pairs = collect($meta)
                                        ->only(['amount', 'method', 'status', 'appointment_id', 'client_id', 'from', 'to'])
                                        ->map(fn($v, $k) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)))
                                        ->values()
                                        ->take(4)
                                        ->all();
                                    $detail = implode(' · ', $pairs);
                                }
                            }
                        @endphp

                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ optional($log->created_at)->isoFormat('D MMM YYYY') ?? '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ optional($log->created_at)->format('H:i') ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $log->actor ? trim($log->actor->first_name.' '.$log->actor->last_name) : 'Sistema' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $log->ip_address ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $actionLabel }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                {{ $entityLabel }}
                                @if (!empty($log->entity_id))
                                    <span class="text-xs text-slate-500 dark:text-slate-400">#{{ $log->entity_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $detail !== '' ? $detail : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay registros para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-4 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
