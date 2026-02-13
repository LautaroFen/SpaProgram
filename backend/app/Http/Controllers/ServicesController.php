<?php

namespace App\Http\Controllers;

use App\Http\Requests\Services\IndexRequest;
use App\Http\Requests\Services\StoreRequest;
use App\Models\AuditLog;
use App\Models\Service;
use App\Queries\Services\ServicesIndexQuery;

class ServicesController extends Controller
{
    public function index(IndexRequest $request, ServicesIndexQuery $servicesIndexQuery)
    {
        $filters = $request->filters();

        $services = $servicesIndexQuery
            ->build($filters)
            ->paginate(15)
            ->withQueryString();

        return view('pages.services.index', [
            'services' => $services,
            'filters' => $filters,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->payload();

        $service = Service::create($data);

        AuditLog::record('create', Service::class, (int) $service->id, [
            'summary' => 'Alta de servicio',
            'name' => $service->name,
        ]);

        return redirect()->to(route('services.index', [], false));
    }
}
