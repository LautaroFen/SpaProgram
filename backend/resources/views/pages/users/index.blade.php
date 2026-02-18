@extends('layouts.app')

@section('title', 'Usuarios')

@section('contentContainerClass', 'none')

@section('content')
    <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Usuarios</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Personal del spa con acceso al sistema y rol asignado.</p>
            </div>
            <button id="openUserModal" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">
                Nuevo usuario
            </button>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <form method="get" action="{{ route('users.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="w-full sm:flex-1 sm:min-w-[18rem]">
                    <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar</label>
                    <input
                        id="q"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                        placeholder="Entidad, nombre, email o cargo"
                    />
                </div>

                @if (!empty($filters['role_q']))
                    <input type="hidden" name="role_q" value="{{ $filters['role_q'] }}" />
                @endif

                <div class="w-full sm:w-56">
                    <label for="role_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Rol</label>
                    <select id="role_id" name="role_id" data-placeholder-select class="mt-1 w-full rounded-md border border-slate-300 bg-white text-slate-900 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                        <option value="" disabled hidden @selected(($filters['role_id'] ?? null) === null)>Seleccionar un rol</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(($filters['role_id'] ?? null) === (int) $role->id)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                    <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Filtrar
                    </button>
                    <a href="{{ route('users.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="table-grid min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-900 dark:bg-slate-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Rol</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Cargo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Alta</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($users as $user)
                            <tr
                                class="hover:bg-slate-50 dark:hover:bg-slate-900/40"
                            >
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ trim($user->first_name.' '.$user->last_name) ?: '—' }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $user->id }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-700 dark:text-slate-200">{{ $user->email ?? '—' }}</div>
                                    @if (!empty($user->email))
                                        <div class="mt-1">
                                            <span @class([
                                                'inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold',
                                                'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200' => $user->hasVerifiedEmail(),
                                                'border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200' => !$user->hasVerifiedEmail(),
                                            ])>
                                                {{ $user->hasVerifiedEmail() ? 'Verificado' : 'No verificado' }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $user->role?->name ? ucfirst($user->role->name) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                    {{ $user->job_title ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $user->created_at?->isoFormat('D MMM YYYY') ?? '—' }}</td>
                                <td class="px-4 py-3" data-stop-row-click>
                                    <span @class([
                                        'inline-flex items-center whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold',
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' => $user->is_active,
                                        'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300' => ! $user->is_active,
                                    ])>
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right" data-stop-row-click>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                        data-user-edit-button
                                        data-user-id="{{ $user->id }}"
                                        data-user-first-name="{{ e($user->first_name ?? '') }}"
                                        data-user-last-name="{{ e($user->last_name ?? '') }}"
                                        data-user-email="{{ e($user->email ?? '') }}"
                                        data-user-job-title="{{ e($user->job_title ?? '') }}"
                                        data-user-role-id="{{ e((string) ($user->role_id ?? '')) }}"
                                        data-user-is-active="{{ $user->is_active ? '1' : '0' }}"
                                    >
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay usuarios para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-4 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </section>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 dark:border-slate-800 dark:bg-slate-950">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold tracking-tight">Roles</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Creá roles para asignarlos a los usuarios del spa.</p>
            </div>
            <button id="openRoleModal" type="button" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Nuevo rol
            </button>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <form method="get" action="{{ route('users.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                @if (!empty($filters['q']))
                    <input type="hidden" name="q" value="{{ $filters['q'] }}" />
                @endif
                @if (!empty($filters['role_id']))
                    <input type="hidden" name="role_id" value="{{ $filters['role_id'] }}" />
                @endif

                <div class="flex-1 sm:min-w-[16rem]">
                    <label for="role_q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar roles</label>
                    <input
                        id="role_q"
                        name="role_q"
                        value="{{ $filters['role_q'] ?? '' }}"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                        placeholder="admin, recepcion, profesional..."
                    />
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                    <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Filtrar
                    </button>
                    <a href="{{ route('users.index', array_filter([
                        'q' => $filters['q'] ?? null,
                        'role_id' => $filters['role_id'] ?? null,
                    ]), false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="table-grid min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-900 dark:bg-slate-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Nombre</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Usuarios</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($rolesTable as $role)
                            <tr
                                class="hover:bg-slate-50 dark:hover:bg-slate-900/40"
                            >
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $role->name }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $role->id }}</div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $role->users_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-right" data-stop-row-click>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                        data-role-edit-button
                                        data-role-id="{{ $role->id }}"
                                        data-role-name="{{ e($role->name) }}"
                                        data-role-is-active="{{ $role->is_active ? '1' : '0' }}"
                                    >
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay roles para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rolesTable->hasPages())
                <div class="px-4 py-3">
                    {{ $rolesTable->links() }}
                </div>
            @endif
        </div>
    </section>

    <div
        id="userModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'user') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-start justify-center p-4 sm:items-center">
            <div class="w-full max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Nuevo usuario</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Creá un usuario y asignale un rol.</div>
                    </div>
                    
                </div>

                <form method="post" action="{{ route('users.store', [], false) }}" class="px-5 py-4">
                    @csrf
                    <input type="hidden" name="_form" value="user" />

                    @if (!empty($filters['q']))
                        <input type="hidden" name="q" value="{{ $filters['q'] }}" />
                    @endif
                    @if (!empty($filters['role_id']))
                        <input type="hidden" name="role_id" value="{{ $filters['role_id'] }}" />
                    @endif

                    @if ($errors->any() && old('_form') === 'user')
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
                            <input id="first_name" name="first_name" data-only-letters value="{{ old('_form') === 'user' ? old('first_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Apellido</label>
                            <input id="last_name" name="last_name" data-only-letters value="{{ old('_form') === 'user' ? old('last_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('_form') === 'user' ? old('email') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="usuario@dominio.com" />
                        </div>

                        <div>
                            <label for="new_role_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Rol</label>
                            <select id="new_role_id" name="new_role_id" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                                <option value="" disabled @selected(old('_form') === 'user' && old('new_role_id') === null)>Elegí un rol</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('_form') === 'user' && (string) old('new_role_id') === (string) $role->id)>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="job_title" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cargo (opcional)</label>
                            <input id="job_title" name="job_title" data-only-letters value="{{ old('_form') === 'user' ? old('job_title') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="Recepcionista / Masajista / etc." />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Contraseña</label>
                            <input id="password" name="password" type="password" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="Mínimo 8 caracteres" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Confirmar contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
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
        id="roleModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'role') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-center justify-center p-4">
            <div class="w-full rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Nuevo rol</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Usá un nombre corto, por ejemplo: <span class="font-semibold">recepcion</span>.</div>
                    </div>
                    
                </div>

                <form method="post" action="{{ route('users.roles.store', [], false) }}" class="px-5 py-4">
                    @csrf
                    <input type="hidden" name="_form" value="role" />

                    @if (!empty($filters['q']))
                        <input type="hidden" name="q" value="{{ $filters['q'] }}" />
                    @endif
                    @if (!empty($filters['role_id']))
                        <input type="hidden" name="role_id" value="{{ $filters['role_id'] }}" />
                    @endif
                    @if (!empty($filters['role_q']))
                        <input type="hidden" name="role_q" value="{{ $filters['role_q'] }}" />
                    @endif

                    @if ($errors->any() && old('_form') === 'role')
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            <div class="font-semibold">Revisá estos campos:</div>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label for="role_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                        <input
                            id="role_name"
                            name="name"
                            value="{{ old('_form') === 'role' ? old('name') : '' }}"
                            required
                            class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            placeholder="recepcion"
                        />
                        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Permitido: letras, números, guión y guión bajo.</div>
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
        id="userStatusModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'user_edit') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-center justify-center p-4">
            <div class="w-full rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Editar usuario</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400" id="userStatusSubtitle">—</div>
                    </div>
                    
                </div>

                <form id="userStatusForm" method="post" action="" data-action-template="{{ route('users.index', [], false) }}/__ID__" class="px-5 py-4">
                    @csrf
                    @method('patch')

                    <input type="hidden" name="_form" value="user_edit" />
                    <input type="hidden" name="_edit_id" id="userEditId" value="{{ old('_form') === 'user_edit' ? old('_edit_id') : '' }}" />
                    <input type="hidden" name="is_active" value="0" />

                    @if ($errors->any() && old('_form') === 'user_edit')
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
                            <label for="userEditFirstName" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                            <input id="userEditFirstName" name="first_name" data-only-letters value="{{ old('_form') === 'user_edit' ? old('first_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div>
                            <label for="userEditLastName" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Apellido</label>
                            <input id="userEditLastName" name="last_name" data-only-letters value="{{ old('_form') === 'user_edit' ? old('last_name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="userEditEmail" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email</label>
                            <input id="userEditEmail" name="email" type="email" value="{{ old('_form') === 'user_edit' ? old('email') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="usuario@dominio.com" />
                        </div>

                        <div>
                            <label for="userEditRoleId" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Rol</label>
                            <select id="userEditRoleId" name="role_id" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200">
                                <option value="" disabled hidden>Elegí un rol</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('_form') === 'user_edit' && (string) old('role_id') === (string) $role->id)>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="userEditJobTitle" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cargo (opcional)</label>
                            <input id="userEditJobTitle" name="job_title" data-only-letters value="{{ old('_form') === 'user_edit' ? old('job_title') : '' }}" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="Recepcionista / Masajista / etc." />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="inline-flex items-center gap-3">
                                <input id="userStatusIsActive" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:text-slate-100 dark:focus:ring-slate-200" @checked(old('_form') === 'user_edit' ? (bool) old('is_active') : true) />
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Activo</span>
                            </label>
                        </div>

                        <div>
                            <label for="userEditPassword" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nueva contraseña (opcional)</label>
                            <input id="userEditPassword" name="password" type="password" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="Mínimo 8 caracteres" />
                        </div>

                        <div>
                            <label for="userEditPasswordConfirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Confirmar contraseña</label>
                            <input id="userEditPasswordConfirmation" name="password_confirmation" type="password" class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" />
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

    <div
        id="roleStatusModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'role_edit') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-xl items-center justify-center p-4">
            <div class="w-full rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Editar rol</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400" id="roleStatusSubtitle">—</div>
                    </div>
                    
                </div>

                <form id="roleStatusForm" method="post" action="" data-action-template="{{ route('users.index', [], false) }}/roles/__ID__" class="px-5 py-4">
                    @csrf
                    @method('patch')

                    <input type="hidden" name="_form" value="role_edit" />
                    <input type="hidden" name="_edit_id" id="roleEditId" value="{{ old('_form') === 'role_edit' ? old('_edit_id') : '' }}" />
                    <input type="hidden" name="is_active" value="0" />

                    @if ($errors->any() && old('_form') === 'role_edit')
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            <div class="font-semibold">Revisá estos campos:</div>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label for="roleEditName" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre</label>
                            <input id="roleEditName" name="name" value="{{ old('_form') === 'role_edit' ? old('name') : '' }}" required class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200" placeholder="recepcion" />
                            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Permitido: letras, números, guión y guión bajo.</div>
                        </div>

                        <label class="inline-flex items-center gap-3">
                            <input id="roleStatusIsActive" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:text-slate-100 dark:focus:ring-slate-200" @checked(old('_form') === 'role_edit' ? (bool) old('is_active') : true) />
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Activo</span>
                        </label>
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
