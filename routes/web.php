<?php

use App\Http\Controllers\MarkerController;


Route::post('/markers', [MarkerController::class, 'store']);
Route::post('/markers/delete', [MarkerController::class, 'destroy'])->name('markers.destroy');

Route::get('/markers/{disease}', [MarkerController::class, 'showByDisease']);
Route::get('/', [MarkerController::class, 'index']);
