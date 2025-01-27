<?php

namespace AdWords\Shared\Outputs;

use AdWords\Shared\Interfaces\ReportOutputInterface;

class ConsoleOutput implements ReportOutputInterface
{
    public function output(string $formattedReport): void
    {
        echo $formattedReport;
    }
}
