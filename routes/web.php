<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'Site\SiteController@index')->name('site.home');
Route::get('about', 'Site\SiteController@about')->name('site.about');
Route::get('contact', 'Site\SiteController@contact')->name('site.contact');

Route::get('login', 'Platform\LoginController@showLoginForm')->name('login');
Route::post('login/vatsim', 'Platform\VatsimLoginController@login')->name('login.vatsim');
Route::get('login/vatsim/callback', 'Platform\VatsimLoginController@callback')->name('login.vatsim.callback');
Route::post('logout', 'Platform\LoginController@logout')->name('logout');

Route::get('dashboard', 'Platform\DashboardController@dashboard')->name('platform.dashboard');

Route::get('{short_url}', 'UrlController@redirect')->name('short-url');
