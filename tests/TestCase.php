<?php

namespace Tests;

use App\Entities\User;
use Codeception\AssertThrows;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\InteractsWithDatabase;
use Tests\Traits\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, InteractsWithDatabase, AssertThrows;

    /**
     * The user currently signed in.
     */
    protected ?User $user;

    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        $this->em = $this->app->make('em');
    }

    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase();
        }
    }

    protected function signIn($user = null)
    {
        $this->user = $user ?: create(User::class);

        $this->actingAs($this->user);

        return $this;
    }

    protected function signInAdmin(User $user = null)
    {
        $this->user = $user ?: create(User::class);

        $this->user->setAdmin(true);

        $this->actingAs($this->user);

        return $this;
    }

    protected function refreshAllEntities(EntityManagerInterface $em = null)
    {
        $em = $em ?: $this->em;
        foreach ($em->getUnitOfWork()->getIdentityMap() as $className => $entities) {
            foreach ($entities as $entity) {
                $em->refresh($entity);
            }
        }
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $parameters = array_map(function ($a) { return (string) $a; }, $parameters);

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }
}
