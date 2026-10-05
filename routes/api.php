<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MobileController;
use Illuminate\Support\Facades\Route;

Route::post('/giris', [MobileController::class, 'login']);

Route::middleware('token')->group(function () {
    Route::get('/bugun', [MobileController::class, 'today']);
    Route::get('/izin', [MobileController::class, 'leave']);
    Route::get('/bordro', [MobileController::class, 'payslips']);
    Route::get('/formlar', [MobileController::class, 'forms']);
    Route::post('/evrak', [MobileController::class, 'document']);
});
