<?php

namespace AdWords\Shared\Formatters;

use AdWords\Shared\DTO\ReportFormatterConfig;
use AdWords\Shared\Enums\TextAlignment;
use AdWords\Shared\Interfaces\ReportFormatterInterface;

class ConsoleReportFormatter implements ReportFormatterInterface
{
    public function format(array $reportData, ReportFormatterConfig $config): string
    {
        $columnWidths = array_reduce(
            $reportData,
            static function (array $widths, array $row) use ($config): array {
                foreach ($config->header as $column) {
                    $value = $row[$column] ?? '';
                    $widths[$column] = max(
                        $widths[$column],
                        strlen((string)$value)
                    );
                }
                return $widths;
            },
            array_combine($config->header, array_map('strlen', $config->header))
        );


        /**
         * Add padding for borders
         */
        $columnWidths = array_map(static fn($width) => $width + 2, $columnWidths);
        $totalWidth = array_sum($columnWidths) + count($columnWidths) + 1;

        $output = [];

        /**
         * Top border
         */
        $output[] = str_repeat('-', $totalWidth);

        /**
         * Header row
         */
        if ($config->includeHeader) {
            $headerRow = '|';
            foreach ($config->header as $column) {
                $headerRow .= $this->alignText(ucfirst($column), $columnWidths[$column], $config->textAlignment) . '|';
            }
            $output[] = $headerRow;
            $output[] = str_repeat('-', $totalWidth);
        }


        $dataRows = array_map(
            fn(array $row): string => '|' . implode('|', array_map(
                    fn($column): string => $this->alignText(
                        (string)($row[$column] ?? ''),
                        $columnWidths[$column],
                        $config->textAlignment
                    ),
                    $config->header
                )) . '|',
            $reportData
        );
        $output = array_merge($output, $dataRows);

        /**
         * Bottom border
         */
        $output[] = str_repeat('-', $totalWidth);

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    private function alignText(string $text, int $width, TextAlignment $alignment): string
    {
        return match ($alignment) {
            TextAlignment::LEFT => $this->leftText($text, $width),
            TextAlignment::RIGHT => $this->rightText($text, $width),
            TextAlignment::CENTER => $this->centerText($text, $width),
        };
    }

    private function leftText(string $text, int $width): string
    {
        return $text . str_repeat(' ', $width - strlen($text));
    }

    private function rightText(string $text, int $width): string
    {
        return str_repeat(' ', $width - strlen($text)) . $text;
    }


    private function centerText(string $text, int $width): string
    {
        $padding = $width - strlen($text);
        $leftPad = (int)floor($padding / 2);
        $rightPad = (int)ceil($padding / 2);

        return str_repeat(' ', $leftPad) . $text . str_repeat(' ', $rightPad);
    }
}
