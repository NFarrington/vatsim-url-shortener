<?php

use App\Http\Controllers\UrlController;

Route::get('{prefix?}/{short_url?}', [UrlController::class, 'redirect'])->name('short-url');
