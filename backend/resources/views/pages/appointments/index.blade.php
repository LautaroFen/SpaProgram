@extends('layouts.app')

@section('title', 'Turnos')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Turnos</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Agenda y gestión de turnos.</p>
        </div>
        <button id="openAppointmentModal" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">
            Nuevo turno
        </button>
    </div>

    @if (! empty($filters['week']) && $weekStart && $weekEnd)
        <div class="mt-4 text-sm text-slate-600 dark:text-slate-300">
            Semana: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $weekStart->isoFormat('D MMM') }} – {{ $weekEnd->isoFormat('D MMM YYYY') }}</span>
        </div>
    @endif

    @if ($errors->any() && old('_form') === 'appointment_delete')
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ $errors->first('delete') }}
        </div>
    @endif

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
        <form method="get" action="{{ route('appointments.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="w-full sm:w-48">
                <label for="week" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Semana</label>
                <input
                    id="week"
                    name="week"
                    type="date"
                    value="{{ $filters['week'] ?? '' }}"
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                />
            </div>

            <div class="w-full sm:flex-1 sm:min-w-[16rem]">
                <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar</label>
                <input
                    id="q"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                    placeholder="Entidad turno, cliente o servicio"
                />
            </div>

            <div class="w-full sm:w-56">
                <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Estado</label>
                <select
                    id="status"
                    name="status"
                    data-placeholder-select
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                >
                    <option value="" disabled hidden @selected(($filters['status'] ?? '') === '')>Seleccionar un estado</option>
                    <option value="scheduled" @selected(($filters['status'] ?? '') === 'scheduled')>Programado</option>
                    <option value="pre_paid" @selected(($filters['status'] ?? '') === 'pre_paid')>Pago parcial</option>
                    <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Atrasado</option>
                    <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Pagado</option>
                    <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelado</option>
                    <option value="no_show" @selected(($filters['status'] ?? '') === 'no_show')>No asistió</option>
                </select>
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Filtrar
                </button>
                <a href="{{ route('appointments.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
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
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Inicio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Servicio</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Monto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Profesional</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Estado</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($appointments as $appt)
                        <tr
                            class="hover:bg-slate-50 dark:hover:bg-slate-900/40"
                        >
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $appt->start_at->isoFormat('ddd D MMM') }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $appt->start_at->format('H:i') }} – {{ $appt->end_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $appt->client?->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $appt->service?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap tabular-nums">
                                $ {{ number_format(((int) ($appt->price_cents ?? 0)) / 100, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $appt->user?->first_name ?? '' }} {{ $appt->user?->last_name ?? '' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center whitespace-nowrap text-xs rounded-full px-2 py-1 font-semibold {{ $appt->ui_status_class ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $appt->ui_status_label ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-2" data-stop-row-click>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                        data-appointment-edit-button
                                        data-appointment-id="{{ $appt->id }}"
                                        data-appointment-start-date="{{ $appt->start_at?->toDateString() }}"
                                        data-appointment-start-time="{{ $appt->start_at?->format('H:i') }}"
                                        data-appointment-client-id="{{ $appt->client_id ?? '' }}"
                                        data-appointment-service-id="{{ $appt->service_id ?? '' }}"
                                        data-appointment-user-id="{{ $appt->user_id ?? '' }}"
                                        data-appointment-deposit="{{ number_format(((int) ($appt->deposit_cents ?? 0)) / 100, 2, '.', '') }}"
                                        data-client-name="{{ e($appt->client?->full_name ?? '—') }}"
                                    >
                                        Editar
                                    </button>

                                    <form
                                        method="post"
                                        action="{{ route('appointments.destroy', ['appointment' => $appt->id], false) }}"
                                        data-confirm-delete-appointment
                                        data-client-name="{{ e($appt->client?->full_name ?? '—') }}"
                                        class="inline-flex"
                                    >
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name="_form" value="appointment_delete" />
                                        <input type="hidden" name="week" value="{{ $filters['week'] ?? '' }}" />

                                        <button
                                            type="submit"
                                            class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900/50 dark:bg-slate-950 dark:text-red-300 dark:hover:bg-red-950/30"
                                            title="Borrar turno"
                                        >
                                            Borrar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">No hay turnos con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($appointments->hasPages())
            <div class="px-4 py-3">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>

    <div
        id="appointmentModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'appointment') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-2xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Nuevo turno</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Completá los datos y confirmá.</div>
                    </div>
                    
                </div>

                <form method="post" action="{{ route('appointments.store', [], false) }}" class="px-5 py-4" id="appointmentForm">
                    @csrf

                    <input type="hidden" name="_form" value="appointment" />

                    <input type="hidden" name="week" value="{{ $filters['week'] ?? '' }}" />

                    @if ($errors->any() && old('_form') === 'appointment')
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            <div class="font-semibold">Revisá estos campos:</div>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="service_id" class="block text-sm font-semibold text-slate-700">Servicio</label>
                            <select
                                id="service_id"
                                name="service_id"
                                data-placeholder-select
                                required
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            >
                                <option value="">Elegí un servicio…</option>
                                @foreach ($services as $service)
                                    <option
                                        value="{{ $service->id }}"
                                        data-price-cents="{{ $service->price_cents }}"
                                        data-duration-minutes="{{ $service->duration_minutes }}"
                                        @selected(old('service_id') == $service->id)
                                    >
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-2 flex flex-wrap gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <div>Duración: <span id="appointmentServiceDuration" class="font-semibold text-slate-900 dark:text-slate-100">—</span></div>
                                <div>Precio: <span id="appointmentServicePrice" class="font-semibold text-slate-900 dark:text-slate-100">—</span></div>
                                <div>Fin estimado: <span id="appointmentEndTime" class="font-semibold text-slate-900 dark:text-slate-100">—</span></div>
                            </div>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Fecha</label>
                            <input
                                id="start_date"
                                name="start_date"
                                type="date"
                                required
                                min="{{ now()->toDateString() }}"
                                value="{{ old('start_date') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Hora</label>
                            <input
                                id="start_time"
                                name="start_time"
                                type="time"
                                required
                                value="{{ old('start_time') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
                        </div>

                        <div>
                            <label for="deposit" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Anticipo (opcional)</label>
                            <input
                                id="deposit"
                                name="deposit"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('deposit') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                placeholder="0,00"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se descuenta del precio al sumar la deuda del cliente.</p>
                        </div>

                        <div>
                            <label for="discount_percent" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Descuento (%)</label>
                            <select
                                id="discount_percent"
                                name="discount_percent"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            >
                                @for ($percent = 0; $percent <= 100; $percent += 5)
                                    <option value="{{ $percent }}" @selected((int) old('discount_percent', 0) === $percent)>
                                        {{ $percent }}%
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="client_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cliente</label>
                            <select
                                id="client_id"
                                name="client_id"
                                data-placeholder-select
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            >
                                <option value="">Nuevo cliente…</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                        {{ $client->full_name }} ({{ $client->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Si el cliente no existe, dejalo en “Nuevo cliente” y completá los datos.</p>
                        </div>

                        <div id="newClientFields" class="sm:col-span-2">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="client_first_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                                    <input
                                        id="client_first_name"
                                        name="client_first_name"
                                        value="{{ old('client_first_name') }}"
                                        class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                    />
                                </div>
                                <div>
                                    <label for="client_last_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Apellido</label>
                                    <input
                                        id="client_last_name"
                                        name="client_last_name"
                                        value="{{ old('client_last_name') }}"
                                        class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                    />
                                </div>
                                <div>
                                    <label for="client_phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Teléfono</label>
                                    <input
                                        id="client_phone"
                                        name="client_phone"
                                        value="{{ old('client_phone') }}"
                                        class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                    />
                                </div>
                                <div>
                                    <label for="client_email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email (opcional)</label>
                                    <input
                                        id="client_email"
                                        name="client_email"
                                        type="email"
                                        value="{{ old('client_email') }}"
                                        class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                    />
                                </div>
                                <div>
                                    <label for="client_dni" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">DNI (opcional)</label>
                                    <input
                                        id="client_dni"
                                        name="client_dni"
                                        value="{{ old('client_dni') }}"
                                        class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Notas (opcional)</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900" data-modal-close>
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            Aceptar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div
        id="appointmentEditModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'appointment_edit') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-2xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Editar turno</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400" id="appointmentEditSubtitle">—</div>
                    </div>
                    
                </div>

                <form id="appointmentEditForm" method="post" action="" data-action-template="{{ route('appointments.index', [], false) }}/__ID__" class="px-5 py-4">
                    @csrf
                    @method('patch')

                    <input type="hidden" name="_form" value="appointment_edit" />
                    <input type="hidden" name="_edit_id" id="appointmentEditId" value="{{ old('_form') === 'appointment_edit' ? old('_edit_id') : '' }}" />
                    <input type="hidden" name="week" value="{{ old('_form') === 'appointment_edit' ? old('week') : ($filters['week'] ?? '') }}" />

                    @if ($errors->any() && old('_form') === 'appointment_edit')
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            <div class="font-semibold">Revisá estos campos:</div>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="edit_service_id" class="block text-sm font-semibold text-slate-700">Servicio</label>
                            <select id="edit_service_id" name="service_id" data-placeholder-select required class="mt-1 w-full rounded-lg border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                                <option value="" disabled hidden>Elegí un servicio…</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" @selected(old('_form') === 'appointment_edit' && (string) old('service_id') === (string) $service->id)>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit_start_date" class="block text-sm font-semibold text-slate-700">Fecha</label>
                            <input id="edit_start_date" name="start_date" type="date" required min="{{ now()->toDateString() }}" value="{{ old('_form') === 'appointment_edit' ? old('start_date') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="edit_start_time" class="block text-sm font-semibold text-slate-700">Hora</label>
                            <input id="edit_start_time" name="start_time" type="time" required value="{{ old('_form') === 'appointment_edit' ? old('start_time') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="edit_deposit" class="block text-sm font-semibold text-slate-700">Anticipo (opcional)</label>
                            <input
                                id="edit_deposit"
                                name="deposit"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('_form') === 'appointment_edit' ? old('deposit') : '' }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                placeholder="0,00"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="edit_client_id" class="block text-sm font-semibold text-slate-700">Cliente</label>
                            <select id="edit_client_id" name="client_id" data-placeholder-select required class="mt-1 w-full rounded-lg border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                                <option value="" disabled hidden>Elegí un cliente…</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('_form') === 'appointment_edit' && (string) old('client_id') === (string) $client->id)>
                                        {{ $client->full_name }} ({{ $client->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="edit_user_id" class="block text-sm font-semibold text-slate-700">Profesional</label>
                            <select id="edit_user_id" name="user_id" data-placeholder-select required class="mt-1 w-full rounded-lg border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                                <option value="" disabled hidden>Elegí un profesional…</option>
                                @foreach ($staff as $person)
                                    <option value="{{ $person->id }}" @selected(old('_form') === 'appointment_edit' && (string) old('user_id') === (string) $person->id)>
                                        {{ trim(($person->first_name ?? '').' '.($person->last_name ?? '')) ?: '—' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900" data-modal-close>
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
