<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    public static function dateOnly($date)
    {
        return Carbon::parse($date)->format('d/m/Y');
    }

    public static function timeOnly($date)
    {
        return Carbon::parse($date)->format('H:i:s');
    }

    public static function currency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}