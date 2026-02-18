<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\UpdateAmountsRequest;
use App\Http\Requests\Payments\StoreExpenseRequest;
use App\Models\AuditLog;
use App\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ExpensesController extends Controller
{
    public function index(Request $request)
    {
        $expenseQ = trim((string) $request->query('q', ''));

        $anchor = $request->query('month')
            ? $this->parseMonthAnchor($request->query('month'))
            : CarbonImmutable::now();

        $monthStart = $anchor->startOfMonth()->startOfDay();
        $monthEnd = $anchor->endOfMonth()->endOfDay();

        if (Schema::hasTable('expenses')) {
            $baseQuery = Expense::query()
                ->whereBetween('performed_at', [$monthStart, $monthEnd])
                ->when($expenseQ !== '', function ($query) use ($expenseQ) {
                    $numericId = ctype_digit($expenseQ) ? (int) $expenseQ : null;
                    $query->where(function ($inner) use ($expenseQ, $numericId) {
                        $inner
                            ->where('category', 'like', '%' . $expenseQ . '%')
                            ->orWhere('payee', 'like', '%' . $expenseQ . '%');

                        if ($numericId !== null) {
                            $inner->orWhere('id', $numericId);
                        }
                    });
                })
                ->orderByDesc('performed_at');

            $totalDueCents = (int) (clone $baseQuery)->sum('amount_due_cents');
            $totalPaidCents = (int) (clone $baseQuery)->sum('amount_paid_cents');

            $expensesTable = $baseQuery
                ->paginate(10)
                ->withQueryString();
        } else {
            $expensesTable = new LengthAwarePaginator(
                [],
                0,
                10,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => $request->url()]
            );
            $expensesTable->appends($request->query());

            $totalDueCents = 0;
            $totalPaidCents = 0;
        }

        return view('pages.expenses.index', [
            'filters' => [
                'q' => $expenseQ,
                'month' => $monthStart->toDateString(),
            ],
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'expensesTable' => $expensesTable,
            'totalDueCents' => $totalDueCents,
            'totalPaidCents' => $totalPaidCents,
        ]);
    }

    private function parseMonthAnchor(mixed $value): CarbonImmutable
    {
        try {
            return CarbonImmutable::parse((string) $value);
        } catch (\Throwable) {
            return CarbonImmutable::now();
        }
    }

    public function update(UpdateAmountsRequest $request, Expense $expense)
    {
        $payload = $request->payload();

        if ($payload['amount_paid_cents'] > $payload['amount_due_cents']) {
            throw ValidationException::withMessages([
                'amount_paid' => 'La cantidad pagada no puede ser mayor a la cantidad a pagar.',
            ]);
        }

        $expense->update([
            'amount_due_cents' => $payload['amount_due_cents'],
            'amount_paid_cents' => $payload['amount_paid_cents'],
        ]);

        AuditLog::record('update', Expense::class, (int) $expense->id, [
            'summary' => 'Actualización de expensa',
            'category' => $expense->category,
            'payee' => $expense->payee,
            'amount_due' => number_format(((int) $expense->amount_due_cents) / 100, 2, ',', '.'),
            'amount_paid' => number_format(((int) $expense->amount_paid_cents) / 100, 2, ',', '.'),
        ]);

        $query = array_filter([
            'month' => $payload['month'],
            'q' => $payload['q'],
        ], fn ($value) => $value !== null && $value !== '');

        return redirect()->to(route('expenses.index', $query, false));
    }

    public function store(StoreExpenseRequest $request)
    {
        $payload = $request->payload();

        if ($payload['amount_paid_cents'] > $payload['amount_due_cents']) {
            throw ValidationException::withMessages([
                'amount_paid' => 'La cantidad pagada no puede ser mayor a la cantidad a pagar.',
            ]);
        }

        $expense = Expense::create([
            'category' => $payload['category'],
            'payee' => $payload['payee'],
            'amount_due_cents' => $payload['amount_due_cents'],
            'amount_paid_cents' => $payload['amount_paid_cents'],
            'performed_at' => $payload['performed_at'],
        ]);

        AuditLog::record('create', Expense::class, (int) $expense->id, [
            'summary' => 'Alta de expensa',
            'category' => $expense->category,
            'payee' => $expense->payee,
            'amount_due' => number_format(((int) $expense->amount_due_cents) / 100, 2, ',', '.'),
        ]);

        return redirect()->to(route('expenses.index', [], false));
    }
}
