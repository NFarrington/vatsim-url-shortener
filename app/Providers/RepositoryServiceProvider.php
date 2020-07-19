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
use App\Services\UrlService;
use Doctrine\DBAL\DBALException;
use Illuminate\Support\Arr;
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

    protected array $nullableRepositories = [
        UrlRepository::class => UrlService::class,
    ];

    public function register()
    {
        $this->registerRepositories();
        $this->registerNullableRepositories();
    }

    protected function registerRepositories()
    {
        foreach ($this->repositories as $entity => $repository) {
            $this->app->bind(
                $repository,
                function ($app) use ($entity, $repository) {
                    return new $repository(
                        $app['em'],
                        $app['em']->getClassMetaData($entity),
                    );
                }
            );
        }
    }

    protected function registerNullableRepositories(): void
    {
        foreach ($this->nullableRepositories as $repository => $dependents) {
            $entity = array_flip($this->repositories)[$repository];
            foreach (Arr::wrap($dependents) as $dependent) {
                $this->app->when($dependent)
                    ->needs($repository)
                    ->give(
                        function ($app) use ($entity, $repository) {
                            try {
                                return new $repository(
                                    $app['em'],
                                    $app['em']->getClassMetaData($entity)
                                );
                            } catch (DBALException $e) {
                                report($e);

                                return null;
                            }
                        }
                    );
            }
        }
    }
}
