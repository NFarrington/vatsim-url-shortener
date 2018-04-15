<?php

namespace Tests\Unit;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function news_can_be_unpublished()
    {
        create(News::class, ['published' => 0]);

        $this->assertEquals(0, News::published()->count());
    }

    /** @test */
    public function news_can_be_published()
    {
        create(News::class, ['published' => 1]);

        $this->assertEquals(1, News::published()->count());
    }
}
