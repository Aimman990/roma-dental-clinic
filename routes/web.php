<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
    Route::get('/audit-log', [App\Http\Controllers\AuditLogsController::class, 'webIndex'])->name('audit.index');

    // Manage patients / UI pages (web)
    Route::get('/patients', function () {
        return view('patients.index');
    })->name('patients.index');
    Route::get('/appointments', function () {
        return view('appointments.index');
    })->name('appointments.index');
    Route::resource('invoices', App\Http\Controllers\InvoicesController::class);
    Route::get('/payments', function () {
        return view('payments.index');
    })->name('payments.index');
    Route::get('/services', function () {
        return view('services.index');
    })->name('services.index');
    Route::get('/expenses', function () {
        return view('expenses.index');
    })->name('expenses.index');
    Route::get('/users', [App\Http\Controllers\UsersController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/toggle-status', [App\Http\Controllers\UsersController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/salaries', function () {
        return view('salaries.index');
    })->name('salaries.index');
    Route::get('/reports/hub', [\App\Http\Controllers\OperationalReportsController::class, 'index'])->name('reports.hub');
    // Redirect '/reports' to the reports hub for easier discovery
    Route::get('/reports', function () { return redirect('/reports/hub'); })->name('reports.index');
    Route::get('/reports/doctors', function () {
        return view('reports.doctors');
    })->name('reports.doctors');
    Route::get('/reports/doctor/{id}', function ($id) {
        return view('reports.doctor', ['doctor_id' => $id]);
    })->name('reports.doctor');
    Route::get('/reports/patients', function () {
        return view('reports.patients');
    })->name('reports.patients');
    Route::get('/reports/services', function () {
        return view('reports.services');
    })->name('reports.services');
    Route::get('/reports/inventory', function () {
        return view('reports.inventory');
    })->name('reports.inventory');
    Route::get('/reports/appointments', function () {
        return view('reports.appointments');
    })->name('reports.appointments');
    Route::get('/reports/expenses', function () {
        return view('reports.expenses');
    })->name('reports.expenses');
    Route::get('/reports/income', function () {
        return view('reports.index');
    })->name('reports.income');
    Route::get('/finance', function () {
        return view('reports.finance');
    })->name('reports.finance');
    Route::get('/finance/payouts', function () {
        return view('reports.payouts');
    })->name('reports.payouts');

    // Inventory
    Route::resource('inventory', App\Http\Controllers\InventoryController::class);
    Route::post('inventory/{item}/adjustment', [App\Http\Controllers\InventoryController::class, 'adjustment'])->name('inventory.adjustment');

    // Treatment Plans
    Route::resource('treatment-plans', App\Http\Controllers\TreatmentPlanController::class);

    // Lab Orders
    Route::resource('lab-orders', App\Http\Controllers\LabOrderController::class);
});

// Expose API routes under /api path so browser SPA can use session cookies.
// This mounts routes defined in routes/api.php under the '/api' prefix and
// keeps them protected by the web session guard.
Route::prefix('api')->group(function () {
    require base_path('routes/api.php');
});


