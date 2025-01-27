<?php

namespace AdWords\Services;

use AdWords\Entities\Campaign;
use AdWords\Entities\Cost;
use AdWords\Services\Base\BaseCampaignService;
use AdWords\Shared\DTO\ReportFormatterConfig;
use AdWords\Shared\Enums\TextAlignment;
use AdWords\Shared\Interfaces\ReportFormatterInterface;
use AdWords\Shared\Interfaces\ReportOutputInterface;
use AdWords\Shared\Utils\DateUtil;

class CampaignReportService extends BaseCampaignService
{
    private ReportFormatterInterface $formatter;
    private ReportOutputInterface $output;

    public function __construct(
        Campaign $campaign,
        ReportFormatterInterface $formatter,
        ReportOutputInterface $output
    )
    {
        parent::__construct($campaign);
        $this->formatter = $formatter;
        $this->output = $output;
    }

    public function generateDailyReport(): void
    {
        echo "Generating daily budget/cost report" . PHP_EOL;
        $report = [];
        $currentDate = $this->getCampaign()->getStartDate();

        while ($currentDate < $this->getCampaign()->getEndDate()) {
            $report[] = [
                'date' => $currentDate->format(DateUtil::DATE_FORMAT),
                'budget' => $this->getCampaign()->getBudgetHistoryByDate($currentDate)->getMaxDailyBudgetSet(),
                'costs' => $this->getCampaign()->getTotalDailyCosts($currentDate)
            ];

            $currentDate = $currentDate->modify('+1 day');
        }
        $config = new ReportFormatterConfig(
            header: ['date', 'budget', 'costs'],
        );

        $formattedReport = $this->formatter->format($report, $config);
        $this->output->output($formattedReport);
    }

    public function generateDetailedDailyCostReport(): void
    {
        echo "Generating detailed cost generated daily report" . PHP_EOL;
        $report = [];
        $currentDate = $this->getCampaign()->getStartDate();

        while ($currentDate < $this->getCampaign()->getEndDate()) {
            $dateString = $currentDate->format(DateUtil::DATE_FORMAT);
            $dailyCosts = $this->getCampaign()->getCostsByDate($currentDate);
            $report[] = [
                'date' => $dateString,
                'daily_costs' => array_reduce(
                    $dailyCosts,
                    static fn($carry, Cost $cost) => "{$carry} {$cost->getAmount()} ({$cost->getDateTime()->format('H:i')})",
                    ''
                ),
            ];

            $currentDate = $currentDate->modify('+1 day');
        }
        $config = new ReportFormatterConfig(
            header: ['date', 'daily_costs'],
            textAlignment: TextAlignment::LEFT,
            includeHeader: true,
        );

        $formattedReport = $this->formatter->format($report, $config);
        $this->output->output($formattedReport);
    }
}
