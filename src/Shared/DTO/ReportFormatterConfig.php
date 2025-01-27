<?php

namespace AdWords\Shared\DTO;

use AdWords\Shared\Enums\TextAlignment;
class ReportFormatterConfig
{
    public function __construct(
        public readonly array $header,
        public readonly TextAlignment $textAlignment = TextAlignment::CENTER,
        public readonly bool $includeHeader = true,
    ) {
    }
}
