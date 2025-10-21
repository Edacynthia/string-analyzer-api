<?php

use App\Http\Controllers\StringController;
use Illuminate\Support\Facades\Route;


Route::post('/strings', [StringController::class, 'store']);
Route::get('/strings', [StringController::class, 'index']);
Route::get('/strings/filter-by-natural-language', [StringController::class, 'filterByNaturalLanguage']);
Route::get('/strings/{value}', [StringController::class, 'show']);
Route::delete('/strings/{value}', [StringController::class, 'destroy']);
