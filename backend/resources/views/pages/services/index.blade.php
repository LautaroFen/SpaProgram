@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Servicios</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Servicios, duración y precios.</p>
        </div>
        <button id="openServiceModal" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">
            Nuevo servicio
        </button>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
        <form method="get" action="{{ route('services.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="w-full sm:flex-1 sm:min-w-[16rem]">
                <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="Entidad o nombre del servicio" />
            </div>

            <div class="w-full sm:w-56">
                <label for="is_active" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Activo</label>
                <select id="is_active" name="is_active" data-placeholder-select class="mt-1 w-full rounded-md border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                    <option value="" disabled hidden @selected(($filters['is_active'] ?? null) === null)>Seleccionar una opción</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Sí</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>No</option>
                </select>
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filtrar</button>
                <a href="{{ route('services.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="overflow-x-auto">
            <table class="table-grid min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-900 dark:bg-slate-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Duración</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Precio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($services as $service)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ $service->name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                {{ $service->duration_minutes }} min
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap tabular-nums">
                                $ {{ number_format(($service->price_cents ?? 0) / 100, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold',
                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' => $service->is_active,
                                    'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300' => ! $service->is_active,
                                ])>
                                    {{ $service->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    data-service-edit
                                    data-service-id="{{ $service->id }}"
                                    data-service-name="{{ $service->name }}"
                                    data-service-duration-minutes="{{ $service->duration_minutes }}"
                                    data-service-price-cents="{{ $service->price_cents ?? 0 }}"
                                    data-service-is-active="{{ $service->is_active ? '1' : '0' }}"
                                    data-service-update-url="{{ route('services.update', ['service' => $service->id], false) }}"
                                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                >
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay servicios para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($services->hasPages())
            <div class="px-4 py-3">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <div
        id="serviceModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'service') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div id="serviceModalTitle" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ (old('_form') === 'service' && old('service_id')) ? 'Editar servicio' : 'Nuevo servicio' }}
                        </div>
                        <div id="serviceModalSubtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">Nombre, duración y precio.</div>
                    </div>
                    
                </div>

                <form
                    id="serviceForm"
                    method="post"
                    action="{{ (old('_form') === 'service' && old('service_id')) ? route('services.update', ['service' => old('service_id')], false) : route('services.store', [], false) }}"
                    data-create-url="{{ route('services.store', [], false) }}"
                    class="px-5 py-4"
                >
                    @csrf
                    <input type="hidden" name="_form" value="service" />
                    <input type="hidden" id="serviceEditId" name="service_id" value="{{ old('service_id') }}" />

                    @if (old('_form') === 'service' && old('service_id'))
                        @method('PATCH')
                    @endif

                    @if ($errors->any() && old('_form') === 'service')
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
                            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                            <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="duration_minutes" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Duración (min)</label>
                            <input id="duration_minutes" name="duration_minutes" type="number" min="1" step="1" value="{{ old('duration_minutes') }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Precio</label>
                            <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <label class="sm:col-span-2 inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="hidden" name="is_active_new" value="0" />
                            <input type="checkbox" name="is_active_new" value="1" @checked(old('is_active_new', '1') == '1') class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:text-slate-100 dark:focus:ring-slate-200" />
                            Activo
                        </label>
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
@endsection
