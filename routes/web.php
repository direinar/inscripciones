<?php

use App\Http\Controllers\Admin\CampusScheduleOptionController;
use App\Http\Controllers\Admin\JornadaOptionController;
use App\Http\Controllers\Admin\PeriodOptionController;
use App\Http\Controllers\Admin\ProgramOptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [EnrollmentController::class, 'create'])->name('enrollments.create');
Route::get('/municipios-por-departamento', [EnrollmentController::class, 'municipalitiesByDepartment'])
    ->name('enrollments.municipalities.by-department');
Route::post('/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin', [AuthController::class, 'admin'])->middleware(RoleMiddleware::class.':admin')->name('admin');

    Route::middleware(RoleMiddleware::class.':admin,mercadeo')->group(function () {
        Route::get('/prospectos', [EnrollmentController::class, 'prospects'])->name('prospects.index');
        Route::get('/mercadeo', [EnrollmentController::class, 'marketing'])->name('marketing.index');
        Route::get('/reportes/inscripciones', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/reportes/inscripciones/exportar', [EnrollmentController::class, 'export'])->name('enrollments.export');
        Route::get('/reportes/financieros', [EnrollmentController::class, 'financialReport'])->name('enrollments.financial');
        Route::get('/reportes/financieros/exportar/excel', [EnrollmentController::class, 'exportFinancialExcel'])->name('enrollments.financial.export.excel');
        Route::get('/reportes/financieros/exportar/pdf', [EnrollmentController::class, 'exportFinancialPdf'])->name('enrollments.financial.export.pdf');
        Route::get('/reportes/inscripciones/exportar/excel', [EnrollmentController::class, 'exportExcel'])->name('enrollments.export.excel');
        Route::get('/reportes/inscripciones/exportar/pdf', [EnrollmentController::class, 'exportPdf'])->name('enrollments.export.pdf');
        Route::patch('/reportes/inscripciones/{enrollment}/pagos', [EnrollmentController::class, 'updatePayments'])->name('enrollments.payments.update');
        Route::patch('/reportes/inscripciones/{enrollment}/datos-personales', [EnrollmentController::class, 'updatePersonalData'])->name('enrollments.personal-data.update');
    });

    Route::middleware(RoleMiddleware::class.':admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::resource('/admin/period-options', PeriodOptionController::class)
            ->parameters(['period-options' => 'periodOption'])
            ->except('show')
            ->names('admin.period-options');

        Route::resource('/admin/campus-schedule-options', CampusScheduleOptionController::class)
            ->parameters(['campus-schedule-options' => 'campusScheduleOption'])
            ->except('show')
            ->names('admin.campus-schedule-options');

        Route::resource('/admin/jornada-options', JornadaOptionController::class)
            ->parameters(['jornada-options' => 'jornadaOption'])
            ->except('show')
            ->names('admin.jornada-options');

        Route::resource('/admin/program-options', ProgramOptionController::class)
            ->parameters(['program-options' => 'programOption'])
            ->except('show')
            ->names('admin.program-options');
    });
});
