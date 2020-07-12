<?php

namespace App\Providers;

use App\Entities\Domain;
use App\Entities\Organization;
use App\Entities\Url;
use App\Entities\User;
use App\Policies\DomainPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\UrlPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Domain::class => DomainPolicy::class,
        Organization::class => OrganizationPolicy::class,
        Url::class => UrlPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Guessing policy names doesn't work when Doctrine has proxied an entity
        // object, instead causing a fatal E_COMPILE_ERROR level error due to
        // attempting to load a class that does not exist.
        // https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/advanced-configuration.html#proxy-objects
        // https://www.doctrine-project.org/projects/doctrine-common/en/3.0/reference/class-loading.html
        // TODO: re-enable policy auto-loading using the parent of proxied classes
        Gate::guessPolicyNamesUsing(fn () => null);

        $this->registerPolicies();

        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });
    }
}
