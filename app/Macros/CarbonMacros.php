<?php

namespace App\Macros;

use Carbon\Carbon;

class CarbonMacros
{
    public static function diffForHumansAt(Carbon $carbon)
    {
        $diffDays = $carbon->diffInDays(now()->endOfDay());
        switch ($diffDays) {
            case 0:
                $date = 'Today';
                break;
            case 1:
                $date = 'Yesterday';
                break;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                $date = "{$diffDays} days ago";
                break;
            default:
                $date = $carbon->format('Y-m-d');
        }

        $time = $carbon->format('H:i');

        return $date.' at '.$time;
    }
}
