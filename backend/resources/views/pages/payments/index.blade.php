@extends('layouts.app')

@section('title', 'Pagos')

@section('contentContainerClass', 'none')

@section('content')
    <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Pagos</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registrar pagos y aplicar descuentos.</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                <button id="openPaymentModal" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">
                    Registrar pago
                </button>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <form method="get" action="{{ route('payments.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="w-full sm:flex-1 sm:min-w-[20rem]">
                    <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar cliente</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="Entidad pago o cliente (DNI/nombre/teléfono/email)" />
                </div>

                <div class="w-full sm:w-56">
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Estado</label>
                    <select id="status" name="status" data-placeholder-select class="mt-1 w-full rounded-md border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                        <option value="" disabled hidden @selected(($filters['status'] ?? '') === '')>Seleccionar un estado</option>
                        <option value="partial" @selected(($filters['status'] ?? '') === 'partial')>Pago parcial</option>
                        <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Pagado</option>
                    </select>
                </div>

                <div class="w-full sm:w-56">
                    <label for="method" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Método</label>
                    <select id="method" name="method" data-placeholder-select class="mt-1 w-full rounded-md border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                        <option value="" disabled hidden @selected(($filters['method'] ?? '') === '')>Seleccionar un método</option>
                        <option value="cash" @selected(($filters['method'] ?? '') === 'cash')>Efectivo</option>
                        <option value="card" @selected(($filters['method'] ?? '') === 'card')>Tarjeta</option>
                        <option value="transfer" @selected(($filters['method'] ?? '') === 'transfer')>Transferencia</option>
                        <option value="other" @selected(($filters['method'] ?? '') === 'other')>Otro</option>
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <label for="from" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Desde</label>
                    <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                </div>

                <div class="w-full sm:w-44">
                    <label for="to" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Hasta</label>
                    <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                    <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filtrar</button>
                    <a href="{{ route('payments.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="table-grid min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Cliente</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Monto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Método</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $payment->created_at->isoFormat('D MMM YYYY') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $payment->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $payment->client?->full_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    $ {{ number_format(($payment->amount_cents ?? 0) / 100, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                    @php
                                        $methodLabels = [
                                            'cash' => 'Efectivo',
                                            'card' => 'Tarjeta',
                                            'transfer' => 'Transferencia',
                                            'other' => 'Otro',
                                        ];
                                        $statusLabels = [
                                            'partial' => 'Pago parcial',
                                            'paid' => 'Pagado',
                                            'void' => 'Anulado',
                                        ];

                                        $statusClass = match ($payment->status) {
                                            'paid' => 'bg-emerald-50 text-emerald-700',
                                            'partial' => 'bg-blue-50 text-blue-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    {{ $methodLabels[$payment->method] ?? $payment->method }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-1 font-semibold {{ $statusClass }}">{{ $statusLabels[$payment->status] ?? $payment->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right" data-stop-row-click>
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                            data-payment-edit-button
                                            data-payment-id="{{ $payment->id }}"
                                            data-payment-amount="{{ number_format(((int) ($payment->amount_cents ?? 0)) / 100, 2, '.', '') }}"
                                            data-client-name="{{ $payment->client?->full_name ?? '—' }}"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="post"
                                            action="{{ route('payments.destroy', $payment, false) }}"
                                            data-confirm-delete-payment
                                            data-client-name="{{ $payment->client?->full_name ?? '—' }}"
                                        >
                                            @csrf
                                            @method('delete')

                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900/50 dark:bg-slate-950 dark:text-red-300 dark:hover:bg-red-950/30"
                                            >
                                                Borrar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">No hay pagos para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="px-4 py-3">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </section>

    <div
        id="paymentModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'payment') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-2xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <div class="text-lg font-semibold text-slate-900">Registrar pago</div>
                        <div class="mt-1 text-sm text-slate-500">Asociá un cliente (y opcionalmente un turno).</div>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700" data-modal-close>
                        Cerrar
                    </button>
                </div>

                <form method="post" action="{{ route('payments.store', [], false) }}" class="px-5 py-4">
                    @csrf
                    <input type="hidden" name="_form" value="payment" />

                    @if ($errors->any() && old('_form') === 'payment')
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
                            <label for="client_id" class="block text-sm font-semibold text-slate-700">Cliente</label>
                            <select id="client_id" name="client_id" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900">
                                <option value="">Nuevo cliente…</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('_form') === 'payment' && old('client_id') == $client->id)>
                                        {{ $client->full_name }} ({{ $client->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Si el cliente no existe, dejalo en “Nuevo cliente” y completá los datos.</p>
                        </div>

                        <div id="newPaymentClientFields" class="sm:col-span-2">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="client_first_name" class="block text-sm font-semibold text-slate-700">Nombre</label>
                                    <input id="client_first_name" name="client_first_name" value="{{ old('_form') === 'payment' ? old('client_first_name') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                                </div>
                                <div>
                                    <label for="client_last_name" class="block text-sm font-semibold text-slate-700">Apellido</label>
                                    <input id="client_last_name" name="client_last_name" value="{{ old('_form') === 'payment' ? old('client_last_name') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                                </div>
                                <div>
                                    <label for="client_phone" class="block text-sm font-semibold text-slate-700">Teléfono</label>
                                    <input id="client_phone" name="client_phone" value="{{ old('_form') === 'payment' ? old('client_phone') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                                </div>
                                <div>
                                    <label for="client_email" class="block text-sm font-semibold text-slate-700">Email (opcional)</label>
                                    <input id="client_email" name="client_email" type="email" value="{{ old('_form') === 'payment' ? old('client_email') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                                </div>
                                <div>
                                    <label for="client_dni" class="block text-sm font-semibold text-slate-700">DNI (opcional)</label>
                                    <input id="client_dni" name="client_dni" value="{{ old('_form') === 'payment' ? old('client_dni') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="appointment_id" class="block text-sm font-semibold text-slate-700">Turno (opcional)</label>
                            <select id="appointment_id" name="appointment_id" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900">
                                <option value="">Sin turno</option>
                                @foreach ($appointments as $appt)
                                    <option
                                        value="{{ $appt->id }}"
                                        data-client-id="{{ $appt->client_id }}"
                                        data-remaining-cents="{{ (int) ($appt->remaining_cents ?? 0) }}"
                                        @selected(old('_form') === 'payment' && old('appointment_id') == $appt->id)
                                    >
                                        {{ $appt->start_at->isoFormat('D MMM YYYY HH:mm') }} — {{ $appt->client?->full_name ?? 'Cliente' }} — {{ $appt->service?->name ?? 'Servicio' }} — Saldo $ {{ number_format(((int) ($appt->remaining_cents ?? 0)) / 100, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="appointmentRemainingHint" class="mt-1 text-xs text-slate-500">Seleccioná un turno para cargar el saldo automáticamente.</p>
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-semibold text-slate-700">Monto</label>
                            <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('_form') === 'payment' ? old('amount') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                        </div>

                        <div>
                            <label for="method_new" class="block text-sm font-semibold text-slate-700">Método</label>
                            <select id="method_new" name="method_new" required class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900">
                                <option value="cash" @selected(old('_form') === 'payment' ? old('method_new', 'cash') === 'cash' : true)>Efectivo</option>
                                <option value="card" @selected(old('_form') === 'payment' && old('method_new') === 'card')>Tarjeta</option>
                                <option value="transfer" @selected(old('_form') === 'payment' && old('method_new') === 'transfer')>Transferencia</option>
                                <option value="other" @selected(old('_form') === 'payment' && old('method_new') === 'other')>Otro</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="reference" class="block text-sm font-semibold text-slate-700">Referencia (opcional)</label>
                            <input id="reference" name="reference" value="{{ old('_form') === 'payment' ? old('reference') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-semibold text-slate-700">Notas (opcional)</label>
                            <textarea id="notes" name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900">{{ old('_form') === 'payment' ? old('notes') : '' }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-modal-close>
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
        id="paymentEditModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'payment_edit') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-2xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <div class="text-lg font-semibold text-slate-900">Editar pago</div>
                        <div class="mt-1 text-sm text-slate-500" id="paymentEditSubtitle">—</div>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700" data-modal-close>
                        Cerrar
                    </button>
                </div>

                <form id="paymentEditForm" method="post" action="" data-action-template="{{ route('payments.index', [], false) }}/__ID__" class="px-5 py-4">
                    @csrf
                    @method('patch')

                    <input type="hidden" name="_form" value="payment_edit" />
                    <input type="hidden" name="_edit_id" id="paymentEditId" value="{{ old('_form') === 'payment_edit' ? old('_edit_id') : '' }}" />

                    @if ($errors->any() && old('_form') === 'payment_edit')
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
                        <div>
                            <label for="paymentEditAmount" class="block text-sm font-semibold text-slate-700">Monto</label>
                            <input
                                id="paymentEditAmount"
                                name="amount"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                value="{{ old('_form') === 'payment_edit' ? old('amount') : '' }}"
                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-modal-close>
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
