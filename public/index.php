<?php

use AdWords\App\Middlewares\Cors;
use AdWords\App\Middlewares\GlobalErrorHandler;
use AdWords\Shared\Parsers\JsonBudgetHistoryParser;
use AdWords\Shared\Utils\DateUtil;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use AdWords\Services\CostGeneratorService;
use AdWords\Repositories\CampaignRepository;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

/**
 * Add g;lobal errors and CORS Middleware
 */

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(new GlobalErrorHandler($app));

$app->add(new Cors());

$app->post('/generate-costs', function (Request $request, Response $response) {

    $data = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

    if (!isset($data['startDate'])) {
        throw new InvalidArgumentException('Start date is required');
    }

    if (!isset($data['budgetHistory'])) {
        throw new InvalidArgumentException('Budget history is required');
    }

    $startDate = DateUtil::createFromString($data['startDate'], DateUtil::DATE_FORMAT);
    $endDate = $startDate->modify('+3 months');

    /**
     * Parse and validate budget history
     */
    $jsonBudgetHistoryParser = new JsonBudgetHistoryParser();
    $budgetHistory = $jsonBudgetHistoryParser->validationAndBudgetCreation($data['budgetHistory']);

    $repository = new CampaignRepository();
    $campaign = $repository->createCampaignFromBudgetHistory($budgetHistory, $startDate, $endDate);

    $costGenerator = new CostGeneratorService($campaign);
    $costGenerator->randomizeCosts();

    $response->getBody()->write(json_encode([
        'success' => true,
        'data' => [
            'generatedCosts' => $campaign->getCosts(),
        ]
    ], JSON_THROW_ON_ERROR));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);

});

$app->run();
