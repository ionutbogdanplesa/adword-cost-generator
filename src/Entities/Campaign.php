<?php

namespace AdWords\Entities;

use AdWords\Shared\Utils\DateUtil;
use DateTimeImmutable;
use Exception;

class Campaign
{
    /**
     * Contains the budget history of the campaign
     * @var array Budget[]
     */
    private array $budgetHistory = [];
    /**
     * Contains the generated costs of the campaign
     * @var array Cost[]
     */
    private array $costs = [];
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;

    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function setBudget(Budget $budget): void
    {
        $budgetDate = $budget->getDate()->format(DateUtil::DATE_FORMAT);
        $this->budgetHistory[$budgetDate] ??= $budget;
    }

    public function addCost(Cost $cost): void
    {
        $date = $cost->getDateTime()->format(DateUtil::DATE_FORMAT);
        $this->costs[$date] ??= [];
        $this->costs[$date][] = $cost;
    }

    /**
     * Get the budget history for a specific date
     * @param DateTimeImmutable $date
     * @return Budget
     */

    public function getBudgetHistoryByDate(DateTimeImmutable $date): Budget
    {
        return $this->budgetHistory[$date->format(DateUtil::DATE_FORMAT)];
    }

    /**
     * Get the generated costs for a specific date
     * @param DateTimeImmutable $date
     * @return array Cost[]
     */

    public function getCostsByDate(DateTimeImmutable $date): array {
        return $this->costs[$date->format(DateUtil::DATE_FORMAT)] ?? [];
    }

    /**
     * This method will return the budget at the given date and time
     * @param DateTimeImmutable $date
     * @return int
     */

    public function getBudgetAt(DateTimeImmutable $date): int {
        return $this->getBudgetHistoryByDate($date)->getBudgetAtDateTime($date);
    }

    public function getBudgetHistory(): array {
        return $this->budgetHistory;
    }

    /**
     * This method will return the total daily costs at the given date
     * @param DateTimeImmutable $date
     * @return float
     */

    public function getTotalDailyCosts(DateTimeImmutable $date): float {
        return array_reduce(
            $this->costs[$date->format(DateUtil::DATE_FORMAT)] ?? [],
            static fn($carry, Cost $cost) => $carry + $cost->getAmount(),
            0
        );
    }

    public function getStartDate(): DateTimeImmutable {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable {
        return $this->endDate;
    }

    public function getCosts(): array {
        return $this->costs;
    }

    /**
     * Sort the costs by timestamp
     * @return void
     */

    public function sortCosts(): void {
        array_walk($this->costs, static function(&$dateCosts) {
            usort($dateCosts, static fn(Cost $a, Cost $b) => $a->getDateTime() <=> $b->getDateTime());
        });
    }
}
