<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\StoreExpenseRequest;
use App\Models\AuditLog;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ExpensesController extends Controller
{
    public function index(Request $request)
    {
        $expenseQ = trim((string) $request->query('q', ''));

        if (Schema::hasTable('expenses')) {
            $expensesTable = Expense::query()
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
                ->orderByDesc('performed_at')
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
        }

        return view('pages.expenses.index', [
            'filters' => [
                'q' => $expenseQ,
            ],
            'expensesTable' => $expensesTable,
        ]);
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
