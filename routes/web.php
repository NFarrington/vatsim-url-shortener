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

Route::domain(config('app.url'))->group(function () {
    Route::get('/', 'Site\SiteController@index')->name('site.home');
    Route::get('about', 'Site\SiteController@about')->name('site.about');
    Route::get('contact', 'Site\SiteController@contact')->name('site.contact');

    Route::get('terms-of-use', 'Platform\InfoController@terms')->name('platform.terms');
    Route::get('privacy-policy', 'Platform\InfoController@privacy')->name('platform.privacy');

    Route::get('platform/login', 'Platform\LoginController@showLoginForm')->name('platform.login');
    Route::post('platform/login/vatsim', 'Platform\VatsimLoginController@login')->name('platform.login.vatsim');
    Route::get('platform/login/vatsim/callback', 'Platform\VatsimLoginController@callback')->name('platform.login.vatsim.callback');
    Route::get('platform/login/two-factor', 'Platform\TwoFactorAuthController@showForm')->name('platform.login.two-factor');
    Route::post('platform/login/two-factor', 'Platform\TwoFactorAuthController@login');

    Route::get('platform/register', 'Platform\RegistrationController@showRegistrationForm')->name('platform.register');
    Route::post('platform/register', 'Platform\RegistrationController@register')->name('platform.register');
    Route::get('platform/register/verify/{token}', 'Platform\EmailVerificationController@verifyEmail')->name('platform.register.verify');

    Route::post('platform/logout', 'Platform\LoginController@logout')->name('platform.logout');

    Route::get('platform', 'Platform\DashboardController@platform')->name('platform');
    Route::get('platform/dashboard', 'Platform\DashboardController@dashboard')->name('platform.dashboard');

    Route::resource('platform/urls', 'Platform\UrlController', ['as' => 'platform']);

    Route::resource('platform/organizations', 'Platform\OrganizationController', ['as' => 'platform']);
    Route::resource('platform/organizations.prefix', 'Platform\OrganizationPrefixController', ['as' => 'platform'])->only(['create', 'store']);
    Route::resource('platform/organizations.users', 'Platform\OrganizationUsersController', ['as' => 'platform'])->only(['store', 'destroy']);

    Route::get('platform/settings', 'Platform\SettingsController@edit')->name('platform.settings');
    Route::put('platform/settings', 'Platform\SettingsController@update');
    Route::get('platform/settings/two-factor', 'Platform\SettingsController@show2FAForm')->name('platform.settings.two-factor');
    Route::post('platform/settings/two-factor', 'Platform\SettingsController@register2FA');
    Route::delete('platform/settings/two-factor', 'Platform\SettingsController@delete2FA');

    Route::get('platform/admin', 'Platform\Admin\AdminController@admin')->name('platform.admin');
    Route::resource('platform/admin/domains', 'Platform\Admin\DomainController', ['as' => 'platform.admin']);
    Route::resource('platform/admin/news', 'Platform\Admin\NewsController', ['as' => 'platform.admin']);

    Route::get('platform/support', 'Platform\InfoController@support')->name('platform.support')->domain();

    Route::post('system/mailgun', 'System\MailgunController@event')->name('system.mailgun');
});

Route::get('{prefix?}/{short_url?}', 'UrlController@redirect')->name('short-url');
