<?php
namespace AdWords\Repositories;

use AdWords\Entities\Budget;
use AdWords\Entities\Campaign;
use AdWords\Shared\Utils\DateUtil;
use DateTimeImmutable;

class CampaignRepository
{
    public function createCampaignFromBudgetHistory(
        array $budgetHistory,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): Campaign {
        $campaign = new Campaign($startDate, $endDate);
        /**
         * We need to keep track of the budget amount at the start of the day
         */
        $startBudgetAmount = 0;
        while ($startDate < $endDate) {
            $dayBudgetChanges = $budgetHistory[$startDate->format(DateUtil::DATE_FORMAT)] ?? [];
            /**
             * Sort the budget changes by time to make sure we always
             * have the last budget change for the next day
             */
            usort($dayBudgetChanges, static fn ($a, $b) =>
                strtotime($a['time']) <=> strtotime($b['time']));

            $campaign->setBudget(new Budget($startDate, $dayBudgetChanges, $startBudgetAmount));

            /**
             * Here we set the last known budget amount for the next day
             * only if changes are done during the day
             */
            if (count($dayBudgetChanges) > 0) {
                $startBudgetAmount = end($dayBudgetChanges)['amount'];
            }
            $startDate = $startDate->modify('+1 day');
        }
        return $campaign;
    }
}
