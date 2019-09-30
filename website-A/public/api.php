<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

use ComocoSsoDemo\WebsiteA\Middlewares\JsonBodyParserMiddleware;
use Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client as GuzzleHttp;

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->add(new JsonBodyParserMiddleware);

// 發送轉址URL
$app->post('/api/login-by-sso-ticket', function (Request $request, Response $response, $args) {
    $parsed_body = $request->getParsedBody();

    $ticket = (isset($parsed_body['ticket'])) ? $parsed_body['ticket'] : '';

    $guzzle_http = new GuzzleHttp;
    $guzzle_response = $guzzle_http->request('POST', 'http://web:9011/api/verify-ticket', [
        'http_errors' => false,
        'form_params' => [
            'ticket' => $ticket,
            'code' => 'RfmUtfRoeu',
        ]
    ]);

    if ($guzzle_response->getStatusCode() != 200) {
        $response->getBody()->write(json_encode([
            'result' => 'Login fail',
        ]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    // 簽發此網站的 JWT token
    $key = getenv('KEY');
    $response->getBody()->write(json_encode([
        'result' => 'SUCCESS',
        'token' => JWT::encode([
            'username' => json_decode($guzzle_response->getBody())->username,
            'exp' => time() + 1800
        ], $key)
    ]));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->run();