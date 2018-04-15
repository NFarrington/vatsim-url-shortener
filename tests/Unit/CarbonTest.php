<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class CarbonTest extends TestCase
{
    /** @test */
    public function diff_time_for_humans_outputs_correctly()
    {
        $carbon = new Carbon();
        $time = $carbon->format('H:i');

        $this->assertEquals("Today at {$time}", $carbon->diffForHumansAt());

        $this->assertEquals("Yesterday at {$time}", $carbon->copy()->subDays(1)->diffForHumansAt());

        $this->assertEquals("2 days ago at {$time}", $carbon->copy()->subDays(2)->diffForHumansAt());

        $date = $carbon->copy()->subdays(7)->format('Y-m-d');
        $this->assertEquals("{$date} at {$time}", $carbon->copy()->subDays(7)->diffForHumansAt());
    }
}
