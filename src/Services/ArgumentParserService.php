<?php
namespace AdWords\Services;

use AdWords\Shared\Enums\InputFormat;
use AdWords\Shared\Enums\OutputType;
use AdWords\Shared\Enums\ReportType;
use AdWords\Shared\Parsers\CsvBudgetHistoryParser;
use AdWords\Shared\Parsers\JsonBudgetHistoryParser;
use AdWords\Shared\Utils\DateUtil;
use InvalidArgumentException;
use ValueError;

class ArgumentParserService
{
    public function parse(array $argv): array
    {
        $args = $this->parseArgv($argv);
        /**
         * Default values
         */
        $outputType = OutputType::CONSOLE;
        $filepath = null;
        $reportType = ReportType::BOTH;
        $startDate = null;
        $budgetHistory = null;

        if (isset($args['input'])) {
            if (!file_exists($args['input'])) {
                throw new InvalidArgumentException("Input file not found: {$args['input']}");
            }

            $format = InputFormat::fromFilename($args['input']);
            $parser = match($format) {
                InputFormat::CSV => new CsvBudgetHistoryParser(),
                InputFormat::JSON => new JsonBudgetHistoryParser(),
            };

            $budgetHistory = $parser->parse($args['input']);
        }

        if (isset($args['start-date'])) {
            $startDate = DateUtil::createFromString("{$args['start-date']} 00:00:00");
            if (!$startDate) {
                throw new InvalidArgumentException("Invalid start date format. Use ". DateUtil::DATE_FORMAT);
            }
        }

        if (isset($args['output'])) {
            try {
                $outputType = OutputType::from(strtolower($args['output']));
            } catch (ValueError $e) {
                throw new InvalidArgumentException(
                    "Invalid output type. Valid values are: " . implode(', ', array_column(OutputType::cases(), 'value'))
                );
            }
        }

        if (isset($args['report'])) {
            try {
                $reportType = ReportType::from(strtolower($args['report']));
            } catch (ValueError $e) {
                throw new InvalidArgumentException(
                    "Invalid report type. Valid values are: " . implode(', ', array_column(ReportType::cases(), 'value'))
                );
            }
        }

        if ($outputType === OutputType::FILE) {
            if (!isset($args['filepath'])) {
                throw new InvalidArgumentException('Filepath is required when output type is file');
            }
            if ($reportType === ReportType::BOTH) {
                throw new InvalidArgumentException('Cannot output both reports to file. Please specify either daily or detailed report');
            }
            $filepath = $args['filepath'];
        }

        return [
            'outputType' => $outputType,
            'filepath' => $filepath,
            'reportType' => $reportType,
            'budgetHistory' => $budgetHistory,
            'startDate' => $startDate
        ];
    }

    private function parseArgv(array $argv): array
    {
        $args = [];
        $argvCount = count($argv);

        for ($i = 1; $i < $argvCount; $i++) {
            if (!str_starts_with($argv[$i], '--')) {
                continue;
            }

            $arg = substr($argv[$i], 2);
            $parts = explode('=', $arg);

            if (count($parts) !== 2) {
                throw new InvalidArgumentException("Invalid argument format: {$argv[$i]}");
            }

            [$key, $value] = $parts;
            $args[$key] = $value;
        }

        return $args;
    }
}
