<?php

namespace AdWords\Shared\Utils;

use DateTimeImmutable;

class DateUtil
{
    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';
    public const YEAR_MONTH_FORMAT = 'Y-m';
    public const HOUR_MINUTE_FORMAT = 'H:i';

    public static function createFromString(string $date, $format = self::DATE_TIME_FORMAT): DateTimeImmutable | false
    {
        return DateTimeImmutable::createFromFormat($format, $date);
    }

}
