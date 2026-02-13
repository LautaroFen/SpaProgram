<?php

use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuditLogsController;
use App\Http\Controllers\ClientEmailVerificationController;
use App\Http\Controllers\UserEmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/verify/client-email/{client}', [ClientEmailVerificationController::class, 'verify'])
	->middleware('signed')
	->name('clients.email.verify');

Route::get('/verify/user-email/{user}', [UserEmailVerificationController::class, 'verify'])
	->middleware('signed')
	->name('users.email.verify');

Route::middleware('guest')->group(function () {
	Route::get('/login', [LoginController::class, 'create'])->name('login');
	Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
	// Everyone (admin / recepcion / other roles)
	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
	Route::get('/appointments', [AppointmentsController::class, 'index'])->name('appointments.index');
	Route::post('/appointments', [AppointmentsController::class, 'store'])->name('appointments.store');
	Route::patch('/appointments/{appointment}', [AppointmentsController::class, 'update'])->name('appointments.update');
	Route::delete('/appointments/{appointment}', [AppointmentsController::class, 'destroy'])->name('appointments.destroy');

	// Admin + Recepción
	Route::middleware('role.access:reception')->group(function () {
		Route::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
		Route::post('/clients', [ClientsController::class, 'store'])->name('clients.store');
		Route::patch('/clients/{client}', [ClientsController::class, 'update'])->name('clients.update');

		Route::get('/payments', [PaymentsController::class, 'index'])->name('payments.index');
		Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
		Route::patch('/payments/{payment}', [PaymentsController::class, 'update'])->name('payments.update');
		Route::delete('/payments/{payment}', [PaymentsController::class, 'destroy'])->name('payments.destroy');
	});

	// Only Admin
	Route::middleware('role.access:admin')->group(function () {
		Route::get('/audit-logs', [AuditLogsController::class, 'index'])->name('audit-logs.index');
		Route::get('/users', [UsersController::class, 'index'])->name('users.index');
		Route::post('/users', [UsersController::class, 'store'])->name('users.store');
		Route::patch('/users/{user}', [UsersController::class, 'update'])->name('users.update');
		Route::post('/users/roles', [UsersController::class, 'storeRole'])->name('users.roles.store');
		Route::patch('/users/roles/{role}', [UsersController::class, 'updateRole'])->name('users.roles.update');
		Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
		Route::post('/services', [ServicesController::class, 'store'])->name('services.store');
		Route::get('/expenses', [\App\Http\Controllers\ExpensesController::class, 'index'])->name('expenses.index');
		Route::post('/expenses', [\App\Http\Controllers\ExpensesController::class, 'store'])->name('expenses.store');
	});
});
