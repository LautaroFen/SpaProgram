@extends('layouts.app')

@section('title', 'Expensas')

@section('contentContainerClass', 'none')

@section('content')
    <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Expensas</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registro de expensas y gastos de la empresa.</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $monthStart->isoFormat('D MMM') }} – {{ $monthEnd->isoFormat('D MMM YYYY') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('expenses.index', ['month' => $monthStart->subMonth()->toDateString(), 'q' => $filters['q'] ?? null], false) }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                >
                    Mes anterior
                </a>
                <a
                    href="{{ route('expenses.index', ['month' => $monthStart->addMonth()->toDateString(), 'q' => $filters['q'] ?? null], false) }}"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                >
                    Mes siguiente
                </a>

                <button id="openExpenseModal" type="button" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Nueva expensa
                </button>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <form method="get" action="{{ route('expenses.index', [], false) }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}" />
                <div class="flex-1">
                    <label for="q" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar</label>
                    <input
                        id="q"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                        placeholder="Entidad, categoría o proveedor"
                    />
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto sm:ml-auto">
                    <button class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Filtrar
                    </button>
                    <a href="{{ route('expenses.index', [], false) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
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
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Categoría</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Proveedor</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">A pagar</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Pagado</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white dark:text-slate-200 whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($expensesTable as $expense)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                    {{ $expense->performed_at?->isoFormat('D MMM YYYY') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $expense->category }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                    {{ $expense->payee }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap tabular-nums">
                                    $ {{ number_format(((int) $expense->amount_due_cents) / 100, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap tabular-nums">
                                    $ {{ number_format(((int) $expense->amount_paid_cents) / 100, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm" data-stop-row-click>
                                    <button
                                        type="button"
                                        data-expense-edit
                                        data-expense-id="{{ $expense->id }}"
                                        data-expense-category="{{ $expense->category }}"
                                        data-expense-payee="{{ $expense->payee }}"
                                        data-expense-amount-due="{{ number_format(((int) $expense->amount_due_cents) / 100, 2, '.', '') }}"
                                        data-expense-amount-paid="{{ number_format(((int) $expense->amount_paid_cents) / 100, 2, '.', '') }}"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
                                    >
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay expensas para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($expensesTable->hasPages())
                <div class="px-4 py-3">
                    {{ $expensesTable->links() }}
                </div>
            @endif
        </div>

        <div class="mt-3 flex items-center justify-end">
            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap tabular-nums">
                Total a pagar: $ {{ number_format(((int) $totalDueCents) / 100, 2, ',', '.') }} ·
                Total pagado: $ {{ number_format(((int) $totalPaidCents) / 100, 2, ',', '.') }}
            </div>
        </div>
    </section>
    <div
        id="expenseModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'expense') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-2xl items-center justify-center p-4">
            <div class="w-full rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Nueva expensa</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registrá una expensa de la empresa.</div>
                    </div>
                    
                </div>

                <form method="post" action="{{ route('expenses.store', [], false) }}" class="px-5 py-4">
                    @csrf
                    <input type="hidden" name="_form" value="expense" />

                    @if ($errors->any() && old('_form') === 'expense')
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
                            <label for="category" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Categoría</label>
                            <input
                                id="category"
                                name="category"
                                value="{{ old('category') }}"
                                required
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                placeholder="materiales, impuestos..."
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="payee" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Proveedor / servicio</label>
                            <input
                                id="payee"
                                name="payee"
                                value="{{ old('payee') }}"
                                required
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                                placeholder="AFIP, proveedor, alquiler..."
                            />
                        </div>

                        <div>
                            <label for="amount_due" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cantidad a pagar</label>
                            <input
                                id="amount_due"
                                name="amount_due"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('amount_due') }}"
                                required
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
                        </div>

                        <div>
                            <label for="amount_paid" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cantidad pagada</label>
                            <input
                                id="amount_paid"
                                name="amount_paid"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('amount_paid') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="performed_at" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Fecha de realización</label>
                            <input
                                id="performed_at"
                                name="performed_at"
                                type="datetime-local"
                                value="{{ old('performed_at') }}"
                                required
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
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
        id="expenseEditModal"
        data-open-on-load="{{ ($errors->any() && old('_form') === 'expense_edit') ? '1' : '0' }}"
        class="fixed inset-0 z-50 hidden"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-modal-overlay></div>

        <div class="relative mx-auto flex min-h-full max-w-2xl items-center justify-center p-4">
            <div class="w-full rounded-2xl bg-white shadow-xl dark:bg-slate-950">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Editar expensa</div>
                        <div id="expenseEditSubtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">—</div>
                    </div>
                    
                </div>

                <form
                    id="expenseEditForm"
                    method="post"
                    action="{{ route('expenses.update', ['expense' => old('expense_id', 0)], false) }}"
                    data-action-template="{{ route('expenses.update', ['expense' => '__ID__'], false) }}"
                    class="px-5 py-4"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_form" value="expense_edit" />
                    <input type="hidden" id="expenseEditId" name="expense_id" value="{{ old('expense_id') }}" />
                    <input type="hidden" id="expenseEditMonth" name="month" value="{{ $filters['month'] ?? '' }}" />
                    <input type="hidden" id="expenseEditQ" name="q" value="{{ $filters['q'] ?? '' }}" />

                    @if ($errors->any() && old('_form') === 'expense_edit')
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
                            <label for="expenseEditAmountDue" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cantidad a pagar</label>
                            <input
                                id="expenseEditAmountDue"
                                name="amount_due"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('amount_due') }}"
                                required
                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
                        </div>

                        <div>
                            <label for="expenseEditAmountPaid" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Cantidad pagada</label>
                            <input
                                id="expenseEditAmountPaid"
                                name="amount_paid"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('amount_paid') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-900 focus:ring-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-200 dark:focus:ring-slate-200"
                            />
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
