<?php

namespace AdWords\Shared\Interfaces;

interface ReportOutputInterface
{
    public function output(string $formattedReport): void;
}
