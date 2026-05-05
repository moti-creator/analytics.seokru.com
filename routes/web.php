<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AskController;
use App\Http\Controllers\TelegramWebhookController;

Route::get('/', [ReportController::class, 'landing']);

// Legal pages (required for Google OAuth verification)
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/about', 'legal.about')->name('about');
Route::view('/legal/privacy', 'legal.privacy');
Route::view('/legal/terms', 'legal.terms');
Route::get('/start/{type}', [ReportController::class, 'start'])->name('start');
Route::post('/ask/start', [ReportController::class, 'askStart'])->name('ask.start');

Route::get('/auth/google', [AuthController::class, 'redirect']);
Route::get('/auth/google/callback', [AuthController::class, 'callback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/connect', [ReportController::class, 'connectForm'])->name('connect');
Route::post('/generate', [ReportController::class, 'generate'])->name('generate');

// Dashboard (property first, then pick report)
Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
Route::post('/dashboard/property', [ReportController::class, 'updateProperty'])->name('dashboard.property');
Route::get('/generate/{type}', [ReportController::class, 'generateDirect'])->name('generate.direct');

Route::get('/ask', [AskController::class, 'form'])->name('ask.form');
Route::post('/ask', [AskController::class, 'run'])->name('ask.run');
Route::get('/ask/clarify', [AskController::class, 'clarifyForm'])->name('ask.clarify');
Route::post('/ask/clarify', [AskController::class, 'clarifySubmit'])->name('ask.clarify.submit');
Route::post('/ask/saved', [AskController::class, 'saveQuery'])->name('ask.save');
Route::delete('/ask/saved/{saved}', [AskController::class, 'deleteSaved'])->name('ask.saved.delete');

Route::get('/r/{report:slug}', [ReportController::class, 'show'])->name('report.show');
Route::get('/r/{report:slug}/pdf', [ReportController::class, 'pdf'])->name('report.pdf');

// Telegram bot webhook — CSRF exempted via VerifyCsrfToken::$except
Route::post('/webhooks/telegram', [TelegramWebhookController::class, 'handle'])->name('webhook.telegram');

// TDNet outreach dashboard
Route::middleware(['tdnet.auth'])->group(function () {
    Route::get('/tdnet', [\App\Http\Controllers\TdnetController::class, 'index'])->name('tdnet.index');
    Route::post('/tdnet', [\App\Http\Controllers\TdnetController::class, 'index']); // login form post
    Route::get('/tdnet/leads/{lead}', [\App\Http\Controllers\TdnetController::class, 'show']);
    Route::post('/tdnet/leads/{lead}/generate', [\App\Http\Controllers\TdnetController::class, 'generate']);
    Route::post('/tdnet/leads/{lead}/regenerate-body', [\App\Http\Controllers\TdnetController::class, 'regenerateBody']);
    Route::post('/tdnet/leads/{lead}/subject', [\App\Http\Controllers\TdnetController::class, 'pickSubject']);
    Route::post('/tdnet/leads/{lead}/sent', [\App\Http\Controllers\TdnetController::class, 'markSent']);
    Route::post('/tdnet/leads/{lead}/skip', [\App\Http\Controllers\TdnetController::class, 'markSkipped']);
    Route::post('/tdnet/leads/{lead}/replied', [\App\Http\Controllers\TdnetController::class, 'markReplied']);
    Route::post('/tdnet/source', [\App\Http\Controllers\TdnetController::class, 'source']);
    Route::post('/tdnet/logout', function (\Illuminate\Http\Request $r) {
        $r->session()->forget('tdnet_auth');
        return redirect('/tdnet');
    })->name('tdnet.logout');
});
