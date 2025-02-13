<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/news',
    fn() =>
    response()->json([['title' => 'News 1'], ['title' => 'News 2'], ['title' => 'News 3']])
);
