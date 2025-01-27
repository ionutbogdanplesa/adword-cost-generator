<?php

namespace AdWords\Shared\Utils;

class RandomGenerator
{
    public static function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public static function float(float $max = 1.0): float
    {
        return round((float) random_int(0, PHP_INT_MAX) / PHP_INT_MAX * $max, 2);
    }

    public static function hourOfTheDay(): int
    {
        return self::int(0, 23);
    }
    public static function minuteOfTheHour(): int
    {
        return self::int(0, 59);
    }

}
