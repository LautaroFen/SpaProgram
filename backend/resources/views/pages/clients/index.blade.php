@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Clientes</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Listado y gestión de clientes.</p>
        </div>
        <button id="openClientModal" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">
            Nuevo cliente
        </button>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
        <form method="get" action="{{ route('clients.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex-1">
                <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar</label>
                <input
                    id="q"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                    placeholder="Entidad, DNI, nombre, teléfono o email"
                />
            </div>

            <label class="mt-1 inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200 sm:mt-0 sm:h-10">
                <input
                    type="checkbox"
                    name="has_debt"
                    value="1"
                    @checked(($filters['has_debt'] ?? null) === true)
                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:text-slate-100 dark:focus:ring-slate-200"
                />
                Con deuda
            </label>

            <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Filtrar
                </button>
                <a href="{{ route('clients.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="overflow-x-auto">
            <table class="table-grid min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">DNI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Teléfono</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Email</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Deuda</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($clients as $client)
                        <tr
                            class="hover:bg-slate-50 dark:hover:bg-slate-900/40"
                        >
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $client->full_name }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $client->dni ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $client->phone ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-slate-700 dark:text-slate-200">{{ $client->email ?? '—' }}</div>
                                @if (!empty($client->email))
                                    <div class="mt-1">
                                        <span @class([
                                            'inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold',
                                            'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200' => $client->hasVerifiedEmail(),
                                            'border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200' => !$client->hasVerifiedEmail(),
                                        ])>
                                            {{ $client->hasVerifiedEmail() ? 'Verificado' : 'No verificado' }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-slate-100">
                                $ {{ number_format(($client->balance_cents ?? 0) / 100, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right" data-stop-row-click>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                    data-client-edit-button
                                    data-client-id="{{ $client->id }}"
                                    data-client-first-name="{{ e($client->first_name ?? '') }}"
                                    data-client-last-name="{{ e($client->last_name ?? '') }}"
                                    data-client-dni="{{ e($client->dni ?? '') }}"
                                    data-client-email="{{ e($client->email ?? '') }}"
                                    data-client-phone="{{ e($client->phone ?? '') }}"
                                >
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay clientes para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="px-4 py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

    <div
        id="clientModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'client') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Nuevo cliente</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Completá los datos y confirmá.</div>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-slate-100" data-modal-close>
                        Cerrar
                    </button>
                </div>

                <form method="post" action="{{ route('clients.store', [], false) }}" class="px-5 py-4">
                    @csrf
                    <input type="hidden" name="_form" value="client" />

                    @if ($errors->any() && old('_form') === 'client')
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
                            <label for="first_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                            <input id="first_name" name="first_name" data-only-letters value="{{ old('_form') === 'client' ? old('first_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Apellido</label>
                            <input id="last_name" name="last_name" data-only-letters value="{{ old('_form') === 'client' ? old('last_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Teléfono</label>
                            <input id="phone" name="phone" data-only-digits inputmode="numeric" pattern="[0-9]*" value="{{ old('_form') === 'client' ? old('phone') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email (opcional)</label>
                            <input id="email" name="email" type="email" value="{{ old('_form') === 'client' ? old('email') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="dni" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">DNI (opcional)</label>
                            <input id="dni" name="dni" data-only-digits inputmode="numeric" pattern="[0-9]*" value="{{ old('_form') === 'client' ? old('dni') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
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
        id="clientEditModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'client_edit') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Editar cliente</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400" id="clientEditSubtitle">—</div>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-slate-100" data-modal-close>
                        Cerrar
                    </button>
                </div>

                <form id="clientEditForm" method="post" action="" data-action-template="{{ route('clients.index', [], false) }}/__ID__" class="px-5 py-4">
                    @csrf
                    @method('patch')

                    <input type="hidden" name="_form" value="client_edit" />
                    <input type="hidden" name="_edit_id" id="clientEditId" value="{{ old('_form') === 'client_edit' ? old('_edit_id') : '' }}" />

                    @if ($errors->any() && old('_form') === 'client_edit')
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
                            <label for="clientEditFirstName" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                            <input id="clientEditFirstName" name="first_name" data-only-letters value="{{ old('_form') === 'client_edit' ? old('first_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="clientEditLastName" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Apellido</label>
                            <input id="clientEditLastName" name="last_name" data-only-letters value="{{ old('_form') === 'client_edit' ? old('last_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="clientEditPhone" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Teléfono</label>
                            <input id="clientEditPhone" name="phone" data-only-digits inputmode="numeric" pattern="[0-9]*" value="{{ old('_form') === 'client_edit' ? old('phone') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="clientEditEmail" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email (opcional)</label>
                            <input id="clientEditEmail" name="email" type="email" value="{{ old('_form') === 'client_edit' ? old('email') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="clientEditDni" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">DNI (opcional)</label>
                            <input id="clientEditDni" name="dni" data-only-digits inputmode="numeric" pattern="[0-9]*" value="{{ old('_form') === 'client_edit' ? old('dni') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
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
