<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $anchor = $request->query('week')
            ? CarbonImmutable::parse($request->query('week'))
            : CarbonImmutable::now();

        $weekStart = $anchor->startOfWeek(CarbonImmutable::MONDAY)->startOfDay();
        // Spa works Monday–Saturday (no Sundays)
        $weekEnd = $weekStart->addDays(5)->endOfDay();

        $appointments = Appointment::query()
            ->with(['client', 'service', 'user'])
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->orderBy('start_at')
            ->get();

        $appointmentsByDay = $appointments->groupBy(fn (Appointment $a) => $a->start_at->toDateString());

        $days = collect(range(0, 5))->map(function (int $offset) use ($weekStart) {
            return $weekStart->addDays($offset);
        });

        return view('pages.dashboard.index', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'days' => $days,
            'appointmentsByDay' => $appointmentsByDay,
        ]);
    }
}
