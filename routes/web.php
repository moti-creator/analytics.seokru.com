<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AskController;

Route::get('/', [ReportController::class, 'landing']);
Route::get('/start/{type}', [ReportController::class, 'start'])->name('start');

Route::get('/auth/google', [AuthController::class, 'redirect']);
Route::get('/auth/google/callback', [AuthController::class, 'callback']);

Route::get('/connect', [ReportController::class, 'connectForm'])->name('connect');
Route::post('/generate', [ReportController::class, 'generate'])->name('generate');

Route::get('/ask', [AskController::class, 'form'])->name('ask.form');
Route::post('/ask', [AskController::class, 'run'])->name('ask.run');

Route::get('/report/{id}', [ReportController::class, 'show'])->name('report.show');
Route::get('/report/{id}/pdf', [ReportController::class, 'pdf'])->name('report.pdf');
