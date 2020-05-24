<?php

Route::get('{prefix?}/{short_url?}', 'UrlController@redirect')->name('short-url');
