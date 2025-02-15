<?php

use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\NewsFiltersController;
use Illuminate\Support\Facades\Route;

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/filters', [NewsFiltersController::class, 'index']);
