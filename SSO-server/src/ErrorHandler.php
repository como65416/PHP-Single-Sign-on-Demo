<?php

namespace ComocoSsoDemo\SsoServer;

use ComocoSsoDemo\SsoServer\Exceptions\BaseHttpException;
use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\ErrorHandler as BaseErrorHandler;
use Slim\Http\Response;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorRendererInterface;

class ErrorHandler extends BaseErrorHandler
{
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $payload = ['error' => $exception->getMessage()];
        if ($exception instanceof HttpException) {
            $response = $response->withStatus($exception->getCode());
        } else {
            $response = $response->withStatus(500);
        }

        $response->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        return $response;
    }
}
