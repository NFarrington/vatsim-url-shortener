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
Route::get('login/two-factor', 'Platform\TwoFactorAuthController@showForm')->name('login.two-factor');
Route::post('login/two-factor', 'Platform\TwoFactorAuthController@login');

Route::get('register', 'Platform\RegistrationController@showRegistrationForm')->name('register');
Route::post('register', 'Platform\RegistrationController@register')->name('register');
Route::get('register/verify/{token}', 'Platform\EmailVerificationController@verifyEmail')->name('register.verify');

Route::post('logout', 'Platform\LoginController@logout')->name('logout');

Route::get('dashboard', 'Platform\DashboardController@dashboard')->name('platform.dashboard');
Route::resource('urls', 'Platform\UrlController', ['as' => 'platform'])->only(['index', 'create', 'store', 'destroy']);
Route::get('settings', 'Platform\SettingsController@edit')->name('platform.settings');
Route::put('settings', 'Platform\SettingsController@update');
Route::get('settings/two-factor', 'Platform\SettingsController@show2FAForm')->name('platform.settings.two-factor');
Route::post('settings/two-factor', 'Platform\SettingsController@register2FA');
Route::delete('settings/two-factor', 'Platform\SettingsController@delete2FA');
Route::get('admin', 'Platform\Admin\AdminController@admin')->name('platform.admin');
Route::resource('admin/news', 'Platform\Admin\NewsController', ['as' => 'platform.admin']);

Route::get('{short_url}', 'UrlController@redirect')->name('short-url');
