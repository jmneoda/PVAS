<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Receptionist\DashboardController as ReceptionistDashboard;
use App\Http\Controllers\Receptionist\CustomerController as ReceptionistCustomerController;
use App\Http\Controllers\Receptionist\ReceptionistAppointmentController;
use App\Http\Controllers\Receptionist\ReportsController as ReceptionistReportsController;
use App\Http\Controllers\Vet\VetDashboardController;
use App\Http\Controllers\Vet\VetAppointmentController;
use App\Http\Controllers\Vet\VetReportsController;
use App\Models\Customer as CustomerModel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // ── Admin Dashboard ───────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // ── Appointments (Admin) ──────────────────────────────────────────────
    Route::get   ('/appointments',             [AppointmentController::class, 'index'])       ->name('appointments.index');
    Route::post  ('/appointments',             [AppointmentController::class, 'store'])       ->name('appointments.store');
    Route::get   ('/appointments/{id}',        [AppointmentController::class, 'show'])        ->name('appointments.show');
    Route::patch ('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::delete('/appointments/{id}',        [AppointmentController::class, 'destroy'])     ->name('appointments.destroy');


    // ── Customers (Admin) ─────────────────────────────────────────────────
    Route::get   ('/customers',               [CustomerController::class, 'index'])  ->name('customers.index');
    Route::post  ('/customers',               [CustomerController::class, 'store'])  ->name('customers.store');
    Route::get   ('/customers/{customer}',    [CustomerController::class, 'show'])   ->name('customers.show');
    Route::get   ('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put   ('/customers/{customer}',    [CustomerController::class, 'update']) ->name('customers.update');
    Route::delete('/customers/{customer}',    [CustomerController::class, 'destroy'])->name('customers.destroy');


    // ── Staff ─────────────────────────────────────────────────────────────
    Route::get   ('/staff',      [StaffController::class, 'index'])  ->name('staff.index');
    Route::post  ('/staff',      [StaffController::class, 'store'])  ->name('staff.store');
    Route::put   ('/staff/{id}', [StaffController::class, 'update']) ->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');


    // ── Reports (Admin) ───────────────────────────────────────────────────
    // NOTE: static segments (pdf, csv) MUST come before the {id} wildcard
    // so Laravel does not mistake "pdf" or "csv" for a record ID.
    Route::get   ('/reports',         [ReportsController::class, 'index'])      ->name('reports.index');
    Route::get   ('/reports/pdf',     [ReportsController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get   ('/reports/csv',     [ReportsController::class, 'downloadCsv'])->name('reports.csv');
    Route::get   ('/reports/{id}',    [ReportsController::class, 'show'])       ->name('reports.show');
    Route::delete('/reports/{id}',    [ReportsController::class, 'destroy'])    ->name('reports.destroy');


    // ══════════════════════════════════════════════════════════════════════
    // RECEPTIONIST routes
    // ══════════════════════════════════════════════════════════════════════

    Route::prefix('receptionist')->name('receptionist.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [ReceptionistDashboard::class, 'index'])->name('dashboard');

        // Customers
        Route::get   ('/customers',      [ReceptionistCustomerController::class, 'index'])  ->name('customers.index');
        Route::post  ('/customers',      [ReceptionistCustomerController::class, 'store'])  ->name('customers.store');
        Route::put   ('/customers/{id}', [ReceptionistCustomerController::class, 'update']) ->name('customers.update');
        Route::delete('/customers/{id}', [ReceptionistCustomerController::class, 'destroy'])->name('customers.destroy');

        // Appointments
        Route::get   ('/appointments',                      [ReceptionistAppointmentController::class, 'index'])       ->name('appointments.index');
        Route::post  ('/appointments',                      [ReceptionistAppointmentController::class, 'store'])       ->name('appointments.store');
        Route::get   ('/appointments/{appointment}',        [ReceptionistAppointmentController::class, 'show'])        ->name('appointments.show');
        Route::put   ('/appointments/{appointment}',        [ReceptionistAppointmentController::class, 'update'])      ->name('appointments.update');
        Route::patch ('/appointments/{appointment}/status', [ReceptionistAppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
        Route::delete('/appointments/{appointment}',        [ReceptionistAppointmentController::class, 'destroy'])     ->name('appointments.destroy');

        // Reports
        // NOTE: static segments (pdf, csv) MUST come before the {id} wildcard
        Route::get   ('/reports',      [ReceptionistReportsController::class, 'index'])      ->name('reports.index');
        Route::get   ('/reports/pdf',  [ReceptionistReportsController::class, 'downloadPdf'])->name('reports.pdf');
        Route::get   ('/reports/csv',  [ReceptionistReportsController::class, 'downloadCsv'])->name('reports.csv');
        Route::get   ('/reports/{id}', [ReceptionistReportsController::class, 'show'])       ->name('reports.show');
        Route::delete('/reports/{id}', [ReceptionistReportsController::class, 'destroy'])    ->name('reports.destroy');

    });

    // ── AJAX: pets for a given customer ───────────────────────────────────
    Route::get('/customers/{customer}/pets', function (CustomerModel $customer) {
        return response()->json(
            $customer->pets()->select('id', 'pet_name', 'breed', 'species')->get()
        );
    })->name('customers.pets');


    // ══════════════════════════════════════════════════════════════════════
    // VET routes  —  Appointments + Reports only
    // ══════════════════════════════════════════════════════════════════════

    Route::prefix('vet')->name('vet.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [VetDashboardController::class, 'index'])->name('dashboard');

        // Appointments
        // NOTE: static segments (pdf, csv) MUST come before the {appointment} wildcard
        Route::get   ('/appointments',                      [VetAppointmentController::class, 'index'])       ->name('appointments.index');
        Route::get   ('/appointments/{appointment}',        [VetAppointmentController::class, 'show'])        ->name('appointments.show');
        Route::patch ('/appointments/{appointment}/status', [VetAppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');

        // Reports
        // NOTE: static segments (pdf, csv) MUST come before the {id} wildcard
        Route::get   ('/reports',      [VetReportsController::class, 'index'])      ->name('reports.index');
        Route::get   ('/reports/pdf',  [VetReportsController::class, 'downloadPdf'])->name('reports.pdf');
        Route::get   ('/reports/csv',  [VetReportsController::class, 'downloadCsv'])->name('reports.csv');
        Route::get   ('/reports/{id}', [VetReportsController::class, 'show'])       ->name('reports.show');
        Route::delete('/reports/{id}', [VetReportsController::class, 'destroy'])    ->name('reports.destroy');

    });

});

require __DIR__.'/auth.php';