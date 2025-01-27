<?php

namespace AdWords\Services;

use AdWords\Entities\Budget;
use AdWords\Entities\Campaign;
use AdWords\Entities\Cost;
use AdWords\Services\Base\BaseCampaignService;
use AdWords\Shared\Utils\DateUtil;
use AdWords\Shared\Utils\RandomGenerator;
use DateTimeImmutable;

class CostGeneratorService extends BaseCampaignService
{
    /**
     * How many costs can be generated per day
     */
    private const MAX_DAILY_GENERATED_COSTS = 10;
    /**
     * Upper limit for spending a daily budget
     */
    private const MAX_DAILY_BUDGET_MULTIPLIER = 2;

    private array $budgetsAndCostsGroupedByMonth;

    public function __construct(Campaign $campaign)
    {
        parent::__construct($campaign);
        /**
         * Group budgets and costs by month to avoid exceeding the monthly budget
         * and not having to iterate over the budget history every time we want to generate a cost
         */
        $this->calculateBudgetsAndCostsGroupedByMonth();
    }

    private function calculateBudgetsAndCostsGroupedByMonth(): void
    {
        $this->budgetsAndCostsGroupedByMonth = array_reduce(
            $this->getCampaign()->getBudgetHistory(),
            static function (array $carry, Budget $budget) {
                $month = $budget->getDate()->format('Y-m');
                $carry[$month] ??= [
                    'budget' => 0,
                    'cost' => 0
                ];
                $carry[$month]['budget'] += $budget->getMaxDailyBudget();
                return $carry;
            },
            []);
    }

    public function randomizeCosts(): void
    {
        $currentDate = $this->getCampaign()->getStartDate();
        $endDate = $this->getCampaign()->getEndDate();
        do {
            if ($this->shouldSkipDay($currentDate)) {
                continue;
            }
            $this->generateDailyCosts($currentDate);
        } while (($currentDate = $currentDate->modify('+1 day')) < $endDate);

        $this->getCampaign()->sortCosts();
    }

    private function shouldSkipDay(DateTimeImmutable $currentDate): bool
    {
        $currentMonth = $currentDate->format(DateUtil::YEAR_MONTH_FORMAT);
        $dailyBudget = $this->getCampaign()->getBudgetHistoryByDate($currentDate);
        $maxDailyBudgetAmount = $dailyBudget->getMaxDailyBudget();

        return $maxDailyBudgetAmount === 0 || $this->budgetsAndCostsGroupedByMonth[$currentMonth]['cost'] >= $this->budgetsAndCostsGroupedByMonth[$currentMonth]['budget'];
    }

    private function generateDailyCosts(DateTimeImmutable $currentDate): void
    {
        $maxDailyGeneratedCosts = RandomGenerator::int(1, self::MAX_DAILY_GENERATED_COSTS);
        $cumulatedDailyCostAmount = 0;
        for ($i = 0; $i < $maxDailyGeneratedCosts; $i++) {
            $costDatetime = $this->generateCostDatetime($currentDate);
            $dailyBudgetCostDetails = $this->calculateBudgetDetails(
                $costDatetime,
                $cumulatedDailyCostAmount
            );
            if ($dailyBudgetCostDetails['costLeftForTheMonth'] <= 0) {
                break;
            }
            $maxPossibleGeneratedCost = min(
                $dailyBudgetCostDetails['budgetLeftAtTimestamp'],
                $dailyBudgetCostDetails['costLeftForTheMonth']
            );

            if ($maxPossibleGeneratedCost <= 0) {
                continue;
            }

            $this->addCost(
                $costDatetime,
                $maxPossibleGeneratedCost,
                $cumulatedDailyCostAmount
            );
        }
    }

    private function addCost(DateTimeImmutable $costDatetime, float $maxPossibleGeneratedCost, float &$cumulatedDailyCostAmount): void
    {
        $monthString = $costDatetime->format(DateUtil::YEAR_MONTH_FORMAT);
        $generatedCostAmount = RandomGenerator::float($maxPossibleGeneratedCost);
        $cumulatedDailyCostAmount += $generatedCostAmount;
        $this->budgetsAndCostsGroupedByMonth[$monthString]['cost'] += $generatedCostAmount;

        $this->getCampaign()->addCost(new Cost($generatedCostAmount, $costDatetime));
    }

    private function calculateBudgetDetails(DateTimeImmutable $costDatetime, float $cumulatedDailyCostAmount): array
    {
        $currentMonth = $costDatetime->format(DateUtil::YEAR_MONTH_FORMAT);
        $budgetAtTimestamp = $this->getCampaign()->getBudgetAt($costDatetime);
        $maxDailyBudgetAllowedAtTimestamp = self::MAX_DAILY_BUDGET_MULTIPLIER * $budgetAtTimestamp;

        return [
            'budgetLeftAtTimestamp' => round($maxDailyBudgetAllowedAtTimestamp - $cumulatedDailyCostAmount, 2),
            'costLeftForTheMonth' => round(
                $this->budgetsAndCostsGroupedByMonth[$currentMonth]['budget'] -
                $this->budgetsAndCostsGroupedByMonth[$currentMonth]['cost'],
                2
            )
        ];
    }

    private function generateCostDatetime(DateTimeImmutable $currentDate): DateTimeImmutable
    {
        $hour = RandomGenerator::hourOfTheDay();
        $minute = RandomGenerator::minuteOfTheHour();

        return $currentDate->setTime($hour, $minute);
    }
}
