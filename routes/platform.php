<?php

use App\Http\Controllers\Platform\Admin\AdminController;
use App\Http\Controllers\Platform\Admin\DomainController;
use App\Http\Controllers\Platform\Admin\ErrorController;
use App\Http\Controllers\Platform\Admin\NewsController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\EmailVerificationController;
use App\Http\Controllers\Platform\InfoController;
use App\Http\Controllers\Platform\LoginController;
use App\Http\Controllers\Platform\OrganizationController;
use App\Http\Controllers\Platform\OrganizationPrefixController;
use App\Http\Controllers\Platform\OrganizationUsersController;
use App\Http\Controllers\Platform\RegistrationController;
use App\Http\Controllers\Platform\SettingsController;
use App\Http\Controllers\Platform\TwoFactorAuthController;
use App\Http\Controllers\Platform\UrlController;
use App\Http\Controllers\Platform\VatsimConnectLoginController;
use App\Http\Controllers\Site\SiteController;

Route::get('/', [SiteController::class, 'index'])->name('site.home');
Route::get('about', [SiteController::class, 'about'])->name('site.about');
Route::get('contact', [SiteController::class, 'contact'])->name('site.contact');

Route::get('terms-of-use', [InfoController::class, 'terms'])->name('platform.terms');
Route::get('privacy-policy', [InfoController::class, 'privacy'])->name('platform.privacy');

Route::get('platform/login', [LoginController::class, 'showLoginForm'])->name('platform.login');
Route::get('platform/login/vatsim-connect', [VatsimConnectLoginController::class, 'login'])->name('platform.login.vatsim-connect');
Route::get('platform/login/two-factor', [TwoFactorAuthController::class, 'showForm'])->name('platform.login.two-factor');
Route::post('platform/login/two-factor', [TwoFactorAuthController::class, 'login']);

Route::get('platform/register', [RegistrationController::class, 'showRegistrationForm'])->name('platform.register');
Route::post('platform/register', [RegistrationController::class, 'register']);
Route::get('platform/register/verify/{token}', [EmailVerificationController::class, 'verifyEmail'])->name('platform.register.verify');

Route::post('platform/logout', [LoginController::class, 'logout'])->name('platform.logout');

Route::get('platform', [DashboardController::class, 'platform'])->name('platform');
Route::get('platform/dashboard', [DashboardController::class, 'dashboard'])->name('platform.dashboard');

Route::resource('platform/urls', UrlController::class, ['as' => 'platform']);

Route::resource('platform/organizations', OrganizationController::class, ['as' => 'platform']);
Route::resource('platform/organizations.prefix', OrganizationPrefixController::class, ['as' => 'platform'])->only(['create', 'store']);
Route::resource('platform/organizations.users', OrganizationUsersController::class, ['as' => 'platform'])->only(['store', 'destroy']);

Route::get('platform/settings', [SettingsController::class, 'edit'])->name('platform.settings');
Route::put('platform/settings', [SettingsController::class, 'update']);
Route::get('platform/settings/two-factor', [SettingsController::class, 'show2FAForm'])->name('platform.settings.two-factor');
Route::post('platform/settings/two-factor', [SettingsController::class, 'register2FA']);
Route::delete('platform/settings/two-factor', [SettingsController::class, 'delete2FA']);

Route::get('platform/admin', [AdminController::class, 'admin'])->name('platform.admin');
Route::resource('platform/admin/domains', DomainController::class, ['as' => 'platform.admin']);
Route::get('platform/admin/generate-error/{statusCode?}', [ErrorController::class, 'generateError'])->name('platform.admin.generate-error');
Route::resource('platform/admin/news', NewsController::class, ['as' => 'platform.admin']);

Route::get('platform/support', [InfoController::class, 'support'])->name('platform.support');
