<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [EnrollmentController::class, 'create'])->name('enrollments.create');
Route::post('/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin', [AuthController::class, 'admin'])->middleware(RoleMiddleware::class . ':admin')->name('admin');

    Route::middleware(RoleMiddleware::class . ':admin,mercadeo')->group(function () {
        Route::get('/reportes/inscripciones', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/reportes/inscripciones/exportar', [EnrollmentController::class, 'export'])->name('enrollments.export');
        Route::get('/reportes/inscripciones/exportar/excel', [EnrollmentController::class, 'exportExcel'])->name('enrollments.export.excel');
        Route::get('/reportes/inscripciones/exportar/pdf', [EnrollmentController::class, 'exportPdf'])->name('enrollments.export.pdf');
        Route::patch('/reportes/inscripciones/{enrollment}/pagos', [EnrollmentController::class, 'updatePayments'])->name('enrollments.payments.update');
    });

    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
