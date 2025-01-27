<?php
namespace AdWords\Shared\Parsers;

use AdWords\Shared\Interfaces\BudgetHistoryParserInterface;
use AdWords\Shared\Utils\DateUtil;
use InvalidArgumentException;

class CsvBudgetHistoryParser implements BudgetHistoryParserInterface
{
    private const EXPECTED_HEADERS = ['date', 'time', 'amount'];

    public function parse(string $filepath): array
    {
        $handle = fopen($filepath, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException("Could not open file: $filepath");
        }

        try {
            $budgetHistory = [];

            /**
             * Read headers and validate
             */
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new InvalidArgumentException('CSV file is empty');
            }

            $headers = array_map('strtolower', $headers);
            if ($headers !== self::EXPECTED_HEADERS) {
                throw new InvalidArgumentException(
                    'Invalid CSV headers. Expected: ' . implode(', ', self::EXPECTED_HEADERS)
                );
            }

            $lineNumber = 2;
            while (($data = fgetcsv($handle)) !== false) {
                $this->validateRow($data, $lineNumber);
                [$date, $time, $amount] = $data;

                if (!isset($budgetHistory[$date])) {
                    $budgetHistory[$date] = [];
                }

                $budgetHistory[$date][] = [
                    'time' => $time,
                    'amount' => (int) $amount
                ];

                $lineNumber++;
            }

            if ($lineNumber === 2) {
                throw new InvalidArgumentException('File contains no data rows');
            }

            return $budgetHistory;

        } finally {
            fclose($handle);
        }
    }

    private function validateRow(array $data, int $lineNumber): void
    {
        if (count($data) !== 3) {
            throw new InvalidArgumentException(
                "Invalid number of columns on line $lineNumber"
            );
        }

        $dateTime = DateUtil::createFromString($data[0] . ' ' . $data[1] . ':00');

        if (!$dateTime) {
            throw new InvalidArgumentException(
                "Invalid date/time format on line $lineNumber. " .
                "Date must be Y-m-d and time must be H:i"
            );
        }

        if (!is_numeric($data[2]) || floor((float)$data[2]) != $data[2]) {
            throw new InvalidArgumentException(
                "Amount must be an integer on line $lineNumber"
            );
        }
    }
}
