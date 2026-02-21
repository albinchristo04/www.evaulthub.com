<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\MatchAdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/watch/{slug}', [MatchController::class, 'show'])->name('watch');
Route::get('/league/{league}', [LeagueController::class, 'show'])->name('league.show');
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/dmca', [HomeController::class, 'dmca'])->name('dmca');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
    Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/import', [ImportController::class, 'index'])->name('import');
        Route::post('/import/fetch', [ImportController::class, 'fetch'])->name('import.fetch');
        Route::post('/import/store', [ImportController::class, 'store'])->name('import.store');

        Route::get('/matches', [MatchAdminController::class, 'index'])->name('matches');
        Route::get('/matches/create', [MatchAdminController::class, 'create'])->name('matches.create');
        Route::post('/matches', [MatchAdminController::class, 'store'])->name('matches.store');
        Route::get('/matches/{id}/edit', [MatchAdminController::class, 'edit'])->name('matches.edit');
        Route::put('/matches/{id}', [MatchAdminController::class, 'update'])->name('matches.update');
        Route::delete('/matches/{id}', [MatchAdminController::class, 'destroy'])->name('matches.destroy');
        Route::post('/matches/{id}/restore', [MatchAdminController::class, 'restore'])->name('matches.restore');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    });
});
