<?php

use App\Http\Controllers\MarkerController;


Route::post('/markers', [MarkerController::class, 'store']);
Route::post('/markers/delete', [MarkerController::class , 'destroy'])->name('markers.destroy');
Route::post('/markers/update', [MarkerController::class, 'update'])->name('markers.update');

Route::get('/', [MarkerController::class, 'index']);
