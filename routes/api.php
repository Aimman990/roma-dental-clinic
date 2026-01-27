<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
	// Patients
	Route::apiResource('patients', App\Http\Controllers\PatientsController::class);

	// Services
	Route::apiResource('services', App\Http\Controllers\ServicesController::class);

	// Appointments
	Route::apiResource('appointments', App\Http\Controllers\AppointmentsController::class);

	// Medical records (visits)
	Route::apiResource('medical-records', App\Http\Controllers\MedicalRecordsController::class);

	// Invoices + payments
	Route::get('invoices', [App\Http\Controllers\InvoicesController::class, 'index']);
	Route::post('invoices', [App\Http\Controllers\InvoicesController::class, 'store']);
	Route::get('invoices/{invoice}', [App\Http\Controllers\InvoicesController::class, 'show']);
	Route::get('invoices/{invoice}/adjustments', [App\Http\Controllers\AdjustmentsController::class, 'index']);
	Route::post('invoices/{invoice}/adjustments', [App\Http\Controllers\AdjustmentsController::class, 'store']);
	Route::put('invoices/{invoice}', [App\Http\Controllers\InvoicesController::class, 'update']);
	Route::delete('invoices/{invoice}', [App\Http\Controllers\InvoicesController::class, 'destroy']);

	Route::get('payments', [App\Http\Controllers\PaymentsController::class, 'index']);
	Route::post('payments', [App\Http\Controllers\PaymentsController::class, 'store']);
	Route::get('payments/{payment}', [App\Http\Controllers\PaymentsController::class, 'show']);
	Route::delete('payments/{payment}', [App\Http\Controllers\PaymentsController::class, 'destroy']);

	// Expenses
	Route::get('expenses', [App\Http\Controllers\ExpensesController::class, 'index']);
	Route::post('expenses', [App\Http\Controllers\ExpensesController::class, 'store']);
	Route::get('expenses/{expense}', [App\Http\Controllers\ExpensesController::class, 'show']);
	Route::put('expenses/{expense}', [App\Http\Controllers\ExpensesController::class, 'update']);
	Route::delete('expenses/{expense}', [App\Http\Controllers\ExpensesController::class, 'destroy']);

	// Salaries
	Route::get('salaries', [App\Http\Controllers\SalariesController::class, 'index']);
	Route::post('salaries/sheets', [App\Http\Controllers\SalariesController::class, 'storeSheet']);
	Route::post('salaries/payments', [App\Http\Controllers\SalariesController::class, 'addPayment']);
	Route::post('salaries/generate', [App\Http\Controllers\SalariesController::class, 'generate']);
	Route::post('salaries/withdraw', [App\Http\Controllers\SalariesController::class, 'withdraw']);

	// Withdrawals management (list, update, delete)
	Route::get('salaries/withdrawals', [App\Http\Controllers\SalariesController::class, 'withdrawals']);
	Route::put('salaries/withdrawals/{withdrawal}', [App\Http\Controllers\SalariesController::class, 'updateWithdrawal']);
	Route::delete('salaries/withdrawals/{withdrawal}', [App\Http\Controllers\SalariesController::class, 'deleteWithdrawal']);

	// Reports
	Route::get('reports/income', [App\Http\Controllers\ReportsController::class, 'income']);
	Route::get('reports/expenses', [App\Http\Controllers\ReportsController::class, 'expenses']);
	Route::get('reports/profit', [App\Http\Controllers\ReportsController::class, 'profit']);
	Route::get('reports/financial-summary', [App\Http\Controllers\ReportsController::class, 'financialSummary']);
	Route::get('reports/export/financials', [App\Http\Controllers\ReportsController::class, 'exportFinancials']);
	Route::get('reports/export/financials-pdf', [App\Http\Controllers\ReportsController::class, 'exportFinancialsPdf']);
	Route::get('reports/debts', [App\Http\Controllers\ReportsController::class, 'debts']);
	Route::get('reports/doctor/{id}', [App\Http\Controllers\ReportsController::class, 'doctorReport']);
	Route::get('reports/doctor/{id}/export', [App\Http\Controllers\ReportsController::class, 'exportDoctor']);
	Route::get('reports/export/income', [App\Http\Controllers\ReportsController::class, 'exportIncome']);
	Route::get('reports/export/income-pdf', [App\Http\Controllers\ReportsController::class, 'exportIncomePdf']);

	// Audit logs
	Route::get('audit/logs', [App\Http\Controllers\AuditLogsController::class, 'index']);

	// Comprehensive Reports
	Route::get('reports/ops/patients', [\App\Http\Controllers\OperationalReportsController::class, 'getPatientsReport']);
	Route::get('reports/ops/doctors', [\App\Http\Controllers\OperationalReportsController::class, 'getDoctorsReport']);
	Route::get('reports/ops/operations', [\App\Http\Controllers\OperationalReportsController::class, 'getOperationsReport']);
	Route::get('reports/ops/financials', [\App\Http\Controllers\OperationalReportsController::class, 'getFinancialsReport']);

	// Doctors summary (per-doctor aggregates)
	Route::get('reports/doctors', [\App\Http\Controllers\OperationalReportsController::class, 'doctorsSummary']);
	Route::get('reports/doctors/export', [\App\Http\Controllers\OperationalReportsController::class, 'exportDoctors']);
	Route::get('reports/services', [\App\Http\Controllers\OperationalReportsController::class, 'servicesSummary']);
	Route::get('reports/inventory', [\App\Http\Controllers\OperationalReportsController::class, 'inventorySummary']);
	Route::get('reports/services/export', [\App\Http\Controllers\OperationalReportsController::class, 'exportServices']);
	Route::get('reports/inventory/export', [\App\Http\Controllers\OperationalReportsController::class, 'exportInventory']);

});

// Admin-only user management (API)
Route::middleware(['web', 'auth'])->group(function () {
	Route::get('users', [App\Http\Controllers\UsersController::class, 'apiIndex']);
	Route::post('users', [App\Http\Controllers\UsersController::class, 'store']);
	Route::get('users/{user}', [App\Http\Controllers\UsersController::class, 'show']);
	Route::put('users/{user}', [App\Http\Controllers\UsersController::class, 'update']);
	Route::post('users/{user}/toggle-status', [App\Http\Controllers\UsersController::class, 'toggleStatus']);
	Route::delete('users/{user}', [App\Http\Controllers\UsersController::class, 'destroy']);
});
