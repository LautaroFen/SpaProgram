<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $logs = AuditLog::query()
            ->with(['actor'])
            ->when($q !== '', function (Builder $query) use ($q) {
                $numericId = ctype_digit($q) ? (int) $q : null;

                $query->where(function (Builder $inner) use ($q, $numericId) {
                    $inner
                        ->where('action', 'like', '%' . $q . '%')
                        ->orWhere('entity_type', 'like', '%' . $q . '%')
                        ->orWhere('entity_id', $q)
                        ->orWhereHas('actor', function (Builder $a) use ($q) {
                            $a->where('first_name', 'like', '%' . $q . '%')
                                ->orWhere('last_name', 'like', '%' . $q . '%')
                                ->orWhere('email', 'like', '%' . $q . '%');
                        });

                    if ($numericId !== null) {
                        $inner->orWhere('id', $numericId);
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('pages.audit-logs.index', [
            'filters' => [
                'q' => $q,
            ],
            'logs' => $logs,
        ]);
    }
}
