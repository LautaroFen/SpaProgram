<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\IndexRequest;
use App\Http\Requests\Users\StoreRequest;
use App\Http\Requests\Users\StoreRoleRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Http\Requests\Users\UpdateRoleRequest;
use App\Mail\UserVerifyEmail;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Queries\Users\UsersIndexQuery;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function index(IndexRequest $request, UsersIndexQuery $usersIndexQuery)
    {
        $filters = $request->filters();

        $users = $usersIndexQuery
            ->build($filters)
            ->paginate(15)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->get();

        $rolesTable = Role::query()
            ->withCount('users')
            ->when(! empty($filters['role_q']), function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['role_q'] . '%');
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'roles_page')
            ->withQueryString();

        return view('pages.users.index', [
            'users' => $users,
            'roles' => $roles,
            'rolesTable' => $rolesTable,
            'filters' => $filters,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->payload();

        $user = User::create([
            'role_id' => $payload['role_id'],
            'is_active' => true,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'job_title' => $payload['job_title'],
            'email' => $payload['email'],
            'password' => $payload['password'],
        ]);

        if (! empty($user->email)) {
            try {
                Mail::to($user->email)->send(new UserVerifyEmail($user));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        AuditLog::record('create', User::class, (int) $user->id, [
            'summary' => 'Alta de usuario',
            'email' => $user->email,
        ]);

        return redirect()->to(route('users.index', [], false));
    }

    public function storeRole(StoreRoleRequest $request)
    {
        $payload = $request->payload();

        $role = Role::create([
            'name' => $payload['name'],
            'is_active' => true,
        ]);

        AuditLog::record('role.create', Role::class, (int) $role->id, [
            'summary' => 'Alta de rol',
            'name' => $role->name,
        ]);

        return redirect()->to(route('users.index', [], false));
    }

    public function update(UpdateRequest $request, User $user)
    {
        $payload = $request->payload();

        $oldEmail = $user->email;

        $updateData = [
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'job_title' => $payload['job_title'],
            'role_id' => $payload['role_id'],
            'email' => $payload['email'],
            'is_active' => $payload['is_active'],
        ];

        if (isset($payload['password']) && trim((string) $payload['password']) !== '') {
            $updateData['password'] = (string) $payload['password'];
        }

        $user->update($updateData);

        $newEmail = $user->email;
        $emailChanged = mb_strtolower(trim((string) $oldEmail)) !== mb_strtolower(trim((string) $newEmail));
        if ($emailChanged) {
            $user->markEmailAsUnverified();

            if (! empty($user->email)) {
                try {
                    Mail::to($user->email)->send(new UserVerifyEmail($user));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        AuditLog::record('user.update', User::class, (int) $user->id, [
            'summary' => 'Actualizó usuario',
            'email' => $user->email,
            'is_active' => $user->is_active,
        ]);

        return redirect()->to(route('users.index', [], false));
    }

    public function updateRole(UpdateRoleRequest $request, Role $role)
    {
        $payload = $request->payload();

        $role->update([
            'name' => $payload['name'],
            'is_active' => $payload['is_active'],
        ]);

        AuditLog::record('role.update', Role::class, (int) $role->id, [
            'summary' => 'Actualizó rol',
            'name' => $role->name,
            'is_active' => $role->is_active,
        ]);

        return redirect()->to(route('users.index', [], false));
    }
}
