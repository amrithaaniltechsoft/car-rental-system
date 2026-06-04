<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('admin-dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Fleet Management
    Route::resource('vehicles', VehicleController::class);
    Route::get('/vehicles-data', [VehicleController::class, 'getData'])->name('vehicles.data');

    // Customer Management
    Route::resource('customers', CustomerController::class);
    Route::get('/customers-data', [CustomerController::class, 'getData'])->name('customers.data');

    // Vehicle Bookings
    Route::get('/bookings/next-id', [BookingController::class, 'getNextBookingId'])->name('bookings.next-id');
    Route::resource('bookings', BookingController::class);
    Route::get('/bookings-data', [BookingController::class, 'getData'])->name('bookings.data');
    Route::post('/bookings/check-availability', [BookingController::class, 'checkAvailability'])->name('bookings.check-availability');
    Route::get('/bookings/{booking}/data', [BookingController::class, 'getBookingData'])->name('bookings.get-data');
    Route::get('/customers/{customer}/details', [BookingController::class, 'getCustomerDetails'])->name('customers.details');
    Route::post('/bookings/{booking}/invoice', [BookingController::class, 'createInvoice'])->name('bookings.create-invoice');
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirmBooking'])->name('bookings.confirm');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show', 'edit', 'update', 'destroy', 'store']);
    Route::get('/invoices-data', [InvoiceController::class, 'getData'])->name('invoices.data');
    Route::get('/invoices/bookings-by-customer', [InvoiceController::class, 'getBookingsByCustomer'])->name('invoices.bookings-by-customer');
    Route::post('/invoices/{invoice}/bill', [InvoiceController::class, 'createBill'])->name('invoices.createBill');

    // Bills
    Route::resource('bills', BillController::class)->only(['index', 'store']);
    Route::get('/bills-data', [BillController::class, 'getData'])->name('bills.data');
    Route::get('/bills/next-number', [BillController::class, 'getNextBillNumber'])->name('bills.next-number');

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/customer/{customer}', [ReportController::class, 'customerReport'])->name('reports.customer');
    Route::get('/reports/vehicle/{vehicle}', [ReportController::class, 'vehicleReport'])->name('reports.vehicle');
    Route::post('/reports/generate', [ReportController::class, 'generateReport'])->name('reports.generate');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
