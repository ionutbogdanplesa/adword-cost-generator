<?php

namespace AdWords\Shared\Formatters;

use AdWords\Shared\DTO\ReportFormatterConfig;
use AdWords\Shared\Interfaces\ReportFormatterInterface;

class CsvReportFormatter implements ReportFormatterInterface
{
    public function format(array $reportData, ReportFormatterConfig $config): string
    {
        $output = '';

        /**
         * Add header row
         */
        if ($config->includeHeader) {
            $output .= implode(
                    ',',
                    array_map(
                        static fn($header) => ucfirst($header),
                        $config->header
                    )
                ) . PHP_EOL;
        }

        $rows = array_map(
            static function(array $row) use ($config): string {
                return implode(
                    ',',
                    array_map(
                        static fn($column) => (string)($row[$column] ?? ''),
                        $config->header
                    )
                );
            },
            $reportData
        );

        $output .= implode(PHP_EOL, $rows);

        if (!empty($output)) {
            $output .= PHP_EOL;
        }

        return $output;
    }
}
