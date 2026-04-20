<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

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

    public function export(Request $request)
    {
        return $this->exportArchive(shouldPurge: false);
    }

    public function exportAndPurge(Request $request)
    {
        return $this->exportArchive(shouldPurge: true);
    }

    private function exportArchive(bool $shouldPurge)
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $stamp = now()->format('Ymd_His');
        $token = Str::lower(Str::random(8));
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . "export_{$stamp}_{$token}.zip";

        $csvPaths = [];
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No se pudo crear el archivo de exportación.');
        }

        try {
            $csvPaths['clientes'] = $this->exportTableToCsv(
                table: 'clients',
                csvBasename: "clientes_{$stamp}.csv",
                orderBy: 'id'
            );
            $zip->addFile($csvPaths['clientes'], 'clientes.csv');

            $csvPaths['turnos'] = $this->exportTableToCsv(
                table: 'appointments',
                csvBasename: "turnos_{$stamp}.csv",
                orderBy: 'start_at'
            );
            $zip->addFile($csvPaths['turnos'], 'turnos.csv');

            $csvPaths['pagos'] = $this->exportTableToCsv(
                table: 'payments',
                csvBasename: "pagos_{$stamp}.csv",
                orderBy: 'created_at'
            );
            $zip->addFile($csvPaths['pagos'], 'pagos.csv');

            $csvPaths['expensas'] = $this->exportTableToCsv(
                table: 'expenses',
                csvBasename: "expensas_{$stamp}.csv",
                orderBy: 'performed_at'
            );
            $zip->addFile($csvPaths['expensas'], 'expensas.csv');

            $csvPaths['auditoria'] = $this->exportTableToCsv(
                table: 'audit_logs',
                csvBasename: "auditoria_{$stamp}.csv",
                orderBy: 'created_at'
            );
            $zip->addFile($csvPaths['auditoria'], 'auditoria.csv');
        } finally {
            $zip->close();
        }

        foreach ($csvPaths as $p) {
            @unlink($p);
        }

        if ($shouldPurge) {
            DB::transaction(function () {
                // Keep: clients, users, services.
                // Purge: audit logs, expenses, and paid appointments.
                AuditLog::query()->delete();
                Expense::query()->delete();
                Appointment::query()->where('status', 'paid')->delete();

                // NOTE: payments are not deleted; if linked to an appointment, appointment_id becomes NULL.
            });
        }

        $downloadName = $shouldPurge
            ? "export_y_limpieza_{$stamp}.zip"
            : "export_{$stamp}.zip";

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    private function exportTableToCsv(string $table, string $csvBasename, string $orderBy = 'id'): string
    {
        $tmpDir = storage_path('app/tmp');
        $path = $tmpDir . DIRECTORY_SEPARATOR . $csvBasename;

        $columns = Schema::getColumnListing($table);

        $fh = fopen($path, 'wb');
        if ($fh === false) {
            abort(500, "No se pudo crear el archivo CSV para {$table}.");
        }

        // Excel-friendly: UTF-8 BOM + separator hint.
        fwrite($fh, "\xEF\xBB\xBFsep=;\n");
        fputcsv($fh, $columns, ';');

        $orderColumn = in_array($orderBy, $columns, true) ? $orderBy : ($columns[0] ?? 'id');

        DB::table($table)
            ->orderBy($orderColumn)
            ->chunk(1000, function ($rows) use ($fh, $columns) {
                foreach ($rows as $row) {
                    $data = (array) $row;
                    $values = [];
                    foreach ($columns as $col) {
                        $v = $data[$col] ?? null;
                        if (is_bool($v)) {
                            $v = $v ? 1 : 0;
                        } elseif (is_array($v) || is_object($v)) {
                            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                        }
                        $values[] = $v;
                    }
                    fputcsv($fh, $values, ';');
                }
            });

        fclose($fh);

        return $path;
    }
}
