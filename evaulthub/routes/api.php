<?php

use App\Http\Controllers\Api\MatchApiController;
use Illuminate\Support\Facades\Route;

Route::get('/matches', [MatchApiController::class, 'matches']);
Route::get('/match/{id}', [MatchApiController::class, 'show']);
