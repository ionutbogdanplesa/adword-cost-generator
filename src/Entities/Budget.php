<?php

namespace AdWords\Entities;

use AdWords\Shared\Utils\DateUtil;
use DateTimeImmutable;

class Budget
{
    /**
     * The start amount is the amount of the budget at the start of the day (00:00)
     * which is inherited from previous budget
     * @var int $startAmount
     */
    private int $startAmount;
    /**
     * Max daily budget is the maximum amount of the budget during the day
     * @var int $maxDailyBudget
     */
    private int $maxDailyBudget;
    /**
     * We store all the changes in the budget as an array
     * @var array $changes
     */
    private array $changes;

    public function __construct(private readonly DateTimeImmutable $date, array $dayBudgetChanges, int $startAmount)
    {
        $this->startAmount = $startAmount;
        $this->changes = $dayBudgetChanges;
        $this->checkChangesForStartAmount();
        $this->setMaxDailyBudget();

    }

    private function checkChangesForStartAmount(): void
    {
        foreach ($this->changes as $change) {
            if ($change['time'] === '00:00') {
                $this->startAmount = $change['amount'];
            }
        }
    }

    private function setMaxDailyBudget(): void
    {
        $this->maxDailyBudget = max(array_merge(array_column($this->changes, 'amount'), [$this->startAmount]));
    }

    public function getMaxDailyBudget(): int
    {
        return $this->maxDailyBudget;
    }

    public function getMaxDailyBudgetSet(): int
    {
        return count($this->changes) === 0 ? $this->maxDailyBudget : max(array_column($this->changes, 'amount'));
    }

    public function getBudgetAtDateTime(DateTimeImmutable $date): int
    {
        /**
         * Iterate over all the changes in the budget backwords because we want to catch the last change
         */
        foreach (array_reverse($this->changes) as $change) {
            $changeTime = DateUtil::createFromString("{$date->format(DateUtil::DATE_FORMAT)} {$change['time']}:00");

            if ($date >= $changeTime) {
                /**
                 * If the date is greater than or equal to the change time, return the amount
                 */

                return $change['amount'];
            }
        }
        /**
         * If no changes were found, return the start amount of the budget
         */

        return $this->startAmount;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }
}
