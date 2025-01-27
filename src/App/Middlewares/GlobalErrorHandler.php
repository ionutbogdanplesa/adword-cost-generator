<?php

namespace AdWords\App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

class GlobalErrorHandler implements ErrorHandlerInterface
{
    public function __construct(private readonly App $app)
    {
    }

    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = $this->app->getResponseFactory()->createResponse();

        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        $errorResponse = [
            'success' => false,
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'type' => get_class($exception)
            ]
        ];

        if ($displayErrorDetails) {
            $errorResponse['error']['trace'] = $exception->getTraceAsString();
        }

        $response->getBody()->write(json_encode($errorResponse, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
