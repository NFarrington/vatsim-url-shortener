<?php

namespace App\Providers;

use App\Entities\Domain;
use App\Entities\News;
use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use App\Entities\User;
use App\Repositories\DomainRepository;
use App\Repositories\NewsRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationUserRepository;
use App\Repositories\UrlRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        Domain::class => DomainRepository::class,
        News::class => NewsRepository::class,
        Organization::class => OrganizationRepository::class,
        OrganizationUser::class => OrganizationUserRepository::class,
        Url::class => UrlRepository::class,
        User::class => UserRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->repositories as $entity => $repository) {
            $this->app->bind(
                $repository,
                function ($app) use ($repository, $entity) {
                    return new $repository(
                        $app['em'],
                        $app['em']->getClassMetaData($entity));
                });
        }
    }
}
