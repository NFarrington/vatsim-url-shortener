<?php

namespace Tests\Unit\Config;

use LaravelDoctrine\ORM\EntityManagerFactory;
use Tests\TestCase;

class DoctrineCacheTest extends TestCase
{
    public static function cacheDrivers(): array
    {
        return [
            'array' => ['array'],
            'file' => ['file'],
            'php_file' => ['php_file'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('cacheDrivers')]
    public function entity_manager_can_be_created_with_cache_driver(string $driver)
    {
        config()->set('doctrine.cache.default', $driver);
        foreach (['metadata', 'query', 'result'] as $cache) {
            config()->set("doctrine.cache.$cache.driver", $driver);
        }

        $entityManager = $this->app[EntityManagerFactory::class]->create(config('doctrine.managers.default'));

        $configuration = $entityManager->getConfiguration();
        $this->assertNotNull($configuration->getMetadataCache());
        $this->assertNotNull($configuration->getQueryCache());
        $this->assertNotNull($configuration->getResultCache());
    }
}
