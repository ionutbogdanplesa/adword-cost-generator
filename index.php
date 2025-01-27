<?php

require_once __DIR__ . '/vendor/autoload.php';

use AdWords\Services\ArgumentParserService;
use AdWords\Services\CostGeneratorService;
use AdWords\Services\CampaignReportService;
use AdWords\Repositories\CampaignRepository;
use AdWords\Shared\Enums\OutputType;
use AdWords\Shared\Enums\ReportType;
use AdWords\Shared\Formatters\ConsoleReportFormatter;
use AdWords\Shared\Formatters\CsvReportFormatter;
use AdWords\Shared\Mocks\BudgetHistoryMock;
use AdWords\Shared\Outputs\ConsoleOutput;
use AdWords\Shared\Outputs\FileOutput;
use AdWords\Shared\Utils\DateUtil;

try {
    $argParser = new ArgumentParserService();
    $args = $argParser->parse($argv);

    [$formatter, $output] = match ($args['outputType']) {
        OutputType::CONSOLE => [
            new ConsoleReportFormatter(),
            new ConsoleOutput()
        ],
        OutputType::FILE => [
            new CsvReportFormatter(),
            new FileOutput($args['filepath'])
        ],
    };

    $repository = new CampaignRepository();
    $startDate = $args['startDate'] ?? DateUtil::createFromString('2019-01-01', DateUtil::DATE_FORMAT);
    $endDate = $startDate->modify('+3 months');

    /**
     * Use provided budget history or fall back to mock
     */
    $budgetHistory = $args['budgetHistory'] ?? BudgetHistoryMock::get();

    $campaign = $repository->createCampaignFromBudgetHistory($budgetHistory, $startDate, $endDate);

    $costGenerator = new CostGeneratorService($campaign);
    $costGenerator->randomizeCosts();

    echo "Campaign created with budget history" . PHP_EOL;

    $reportService = new CampaignReportService($campaign, $formatter, $output);
    match ($args['reportType']) {
        ReportType::DAILY => $reportService->generateDailyReport(),
        ReportType::DETAILED => $reportService->generateDetailedDailyCostReport(),
        ReportType::BOTH => [
            $reportService->generateDailyReport(),
            $reportService->generateDetailedDailyCostReport()
        ],
    };

} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Usage: php index.php [--start-date=Y-m-d] [--input=path/to/file(.csv|.json)] [--output=console|file] [--filepath=path/to/file] [--report=daily|detailed|both]" . PHP_EOL;
    echo "Note: 'both' report type is only available with console output" . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
