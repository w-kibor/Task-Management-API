<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('tasks')->group(function (): void {
    Route::get('report', [TaskController::class, 'report']);
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/', [TaskController::class, 'index']);
    Route::patch('{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('{task}', [TaskController::class, 'destroy']);
});
