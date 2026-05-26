<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('news.index');
});

Route::resource('news', NewsController::class)->except(['show']);
