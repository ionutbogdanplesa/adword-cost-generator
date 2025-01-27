<?php

namespace AdWords\Shared\Interfaces;

use AdWords\Shared\DTO\ReportFormatterConfig;

interface ReportFormatterInterface
{
    public function format(array $reportData, ReportFormatterConfig $config): string;
}

