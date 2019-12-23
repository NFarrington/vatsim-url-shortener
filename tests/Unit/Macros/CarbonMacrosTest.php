<?php

namespace Tests\Unit\Macros;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class CarbonMacrosTest extends TestCase
{
    const YEAR = 2019, MONTH = 12, DAY = 23, HOUR = 15, MINUTE = 29, SECOND = 1;

    public static $now;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$now = Carbon::create(self::YEAR, self::MONTH, self::DAY, self::HOUR, self::MINUTE, self::SECOND);
        Carbon::setTestNow(self::$now);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        Carbon::setTestNow();
    }

    /** @test */
    function diff_time_for_humans_outputs_correctly()
    {
        $date7DaysAgo = self::YEAR.'-'.self::MONTH.'-'.(self::DAY - 7);
        $time = self::HOUR.':'.self::MINUTE;

        $this->assertEquals("Today at {$time}", self::$now->diffForHumansAt());
        $this->assertEquals("Yesterday at {$time}", self::$now->copy()->subDays(1)->diffForHumansAt());
        $this->assertEquals("2 days ago at {$time}", self::$now->copy()->subDays(2)->diffForHumansAt());
        $this->assertEquals("{$date7DaysAgo} at {$time}", self::$now->copy()->subDays(7)->diffForHumansAt());
    }
}
