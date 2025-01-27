<?php
namespace AdWords\Shared\Parsers;

use AdWords\Shared\Interfaces\BudgetHistoryParserInterface;
use AdWords\Shared\Utils\DateUtil;
use InvalidArgumentException;

class JsonBudgetHistoryParser implements BudgetHistoryParserInterface
{
    public function parse(string $filepath): array
    {
        $content = file_get_contents($filepath);
        if ($content === false) {
            throw new InvalidArgumentException("Could not read file: $filepath");
        }

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON format: ' . json_last_error_msg());
        }

        return $this->validationAndBudgetCreation($data);
    }

    public function validationAndBudgetCreation($data): array
    {
        $budgetHistory = [];
        foreach ($data as $date => $entries) {
            $this->validateDate($date);

            foreach ($entries as $index => $entry) {
                $this->validateEntry($entry, $date, $index);

                if (!isset($budgetHistory[$date])) {
                    $budgetHistory[$date] = [];
                }

                $budgetHistory[$date][] = [
                    'time' => $entry['time'],
                    'amount' => (int)$entry['amount']
                ];
            }
        }
        return $budgetHistory;
    }

    private function validateDate(string $date): void
    {
        if (!DateUtil::createFromString($date, DateUtil::DATE_FORMAT)) {
            throw new InvalidArgumentException("Invalid date format: $date. Expected ". DateUtil::DATE_FORMAT);
        }
    }

    private function validateEntry(array $entry, string $date, int $index): void
    {
        if (!isset($entry['time'], $entry['amount'])) {
            throw new InvalidArgumentException(
                "Missing required fields on date $date, entry $index"
            );
        }

        if (!DateUtil::createFromString($entry['time'], DateUtil::HOUR_MINUTE_FORMAT)) {
            throw new InvalidArgumentException(
                "Invalid time format on date $date, entry $index. Expected H:i"
            );
        }

        if (!is_numeric($entry['amount']) || floor((float)$entry['amount']) != $entry['amount']) {
            throw new InvalidArgumentException(
                "Amount must be an integer on date $date, entry $index"
            );
        }
    }
}
