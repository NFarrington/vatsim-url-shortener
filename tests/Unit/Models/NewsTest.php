<?php

namespace Tests\Unit\Models;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Models\News
 */
class NewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function published_articles_appear_in_the_published_scope()
    {
        create(News::class, ['published' => 1]);

        $this->assertEquals(1, News::published()->count());
    }

    /** @test */
    function unpublished_articles_are_excluded_from_the_published_scope()
    {
        create(News::class, ['published' => 0]);

        $this->assertEquals(0, News::published()->count());
    }
}
