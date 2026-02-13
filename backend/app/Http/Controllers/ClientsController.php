<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clients\IndexRequest;
use App\Http\Requests\Clients\StoreRequest;
use App\Http\Requests\Clients\UpdateRequest;
use App\Mail\ClientVerifyEmail;
use App\Models\AuditLog;
use App\Models\Client;
use App\Queries\Clients\ClientsIndexQuery;
use Illuminate\Support\Facades\Mail;

class ClientsController extends Controller
{
    public function index(IndexRequest $request, ClientsIndexQuery $clientsIndexQuery)
    {
        $filters = $request->filters();

        $clients = $clientsIndexQuery
            ->build($filters)
            ->paginate(15)
            ->withQueryString();

        return view('pages.clients.index', [
            'clients' => $clients,
            'filters' => $filters,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->payload();

        $client = Client::create([
            'dni' => $payload['dni'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => $payload['email'],
            'email_verified_at' => null,
            'phone' => $payload['phone'],
            'balance_cents' => 0,
        ]);

        if (! empty($client->email)) {
            try {
                Mail::to($client->email)->send(new ClientVerifyEmail($client));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        AuditLog::record('create', Client::class, (int) $client->id, [
            'summary' => 'Alta de cliente',
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
        ]);

        return redirect()->to(route('clients.index', [], false));
    }

    public function update(UpdateRequest $request, Client $client)
    {
        $payload = $request->payload();

        $oldEmail = $client->email;

        $client->update([
            'dni' => $payload['dni'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
        ]);

        $newEmail = $client->email;
        $emailChanged = mb_strtolower(trim((string) $oldEmail)) !== mb_strtolower(trim((string) $newEmail));
        if ($emailChanged) {
            $client->markEmailAsUnverified();

            if (! empty($client->email)) {
                try {
                    Mail::to($client->email)->send(new ClientVerifyEmail($client));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        AuditLog::record('client.update', Client::class, (int) $client->id, [
            'summary' => 'Actualizó cliente',
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
        ]);

        return redirect()->to(route('clients.index', [], false));
    }
}
