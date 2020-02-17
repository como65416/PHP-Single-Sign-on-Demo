<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

use ComocoSsoDemo\SsoServer\ErrorHandler;
use ComocoSsoDemo\SsoServer\Middlewares\JsonBodyParserMiddleware;
use ComocoSsoDemo\SsoServer\Services\ResponseService;
use ComocoSsoDemo\SsoServer\Services\MemberService;
use Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->add(new JsonBodyParserMiddleware);

$errorHandler = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/*
 * 登入用的API，登入成功取得token
 */
$app->post('/api/login', function (Request $request, Response $response, $args) {
    $parsed_body = $request->getParsedBody();

    $username = $parsed_body['username'] ?? '';
    $password = $parsed_body['password'] ?? '';

    // 驗證密碼
    if (!MemberService::validateAccountPassword($username, $password)) {
        throw new HttpUnauthorizedException($request);
    }

    // 簽發JWT token
    $key = getenv('KEY');
    $token = JWT::encode(['username' => $username, 'exp' => time() + 21600], $key);
    return ResponseService::generateJsonResponse($response, ['token' => $token]);
});

$app->get('/api/available-sites', function (Request $request, Response $response, $args) {
    $parsed_body = $request->getParsedBody();

    $key = getenv('KEY');
    $header_authorization = $request->getHeaderLine('Authorization');
    preg_match('/^Bearer[ ]+(.*?\..*?\..*?)$/', $header_authorization, $match);
    $token_content = JWT::decode($match[1], $key, array('HS256'));
    $username = $token_content->username;

    $account_site_permissions = Capsule::table('account_site_permission')->where('username', '=', $username)->get();
    $site_ids = array_column($account_site_permissions->toArray(), 'site_id');
    $sites = Capsule::table('site')
      ->select('name', 'host', 'home_page_path', 'logout_path')
      ->whereIn('id', $site_ids)
      ->get();

    $response->getBody()->write(json_encode($sites->toArray()));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// 發送轉址URL
$app->get('/api/to-site', function (Request $request, Response $response, $args) {
    $query_params = $request->getQueryParams();
    $website_url = $query_params['website_url'];

    $url_info = parse_url($website_url);
    $host = $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '');

    $site = Capsule::table('site')->where('host', '=', $host)->first();

    $key = getenv('KEY');
    $header_authorization = $request->getHeaderLine('Authorization');
    preg_match('/^Bearer[ ]+(.*?\..*?\..*?)$/', $header_authorization, $match);
    $token_content = JWT::decode($match[1], $key, array('HS256'));
    $username = $token_content->username;

    if (
      $site == null ||
      !Capsule::table('account_site_permission')->where('site_id', '=', $site->id)->where('username', '=', $username)->exists()
    ) {
      $response->getBody()->write(json_encode([
          'result' => 'Permission denied',
      ]));
      return $response->withHeader('Content-Type', 'application/json')
          ->withStatus(403);
    }

    $cipher = 'AES-256-CBC';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt(json_encode([
      'username' => $username,
      'site_id' => $site->id,
      'expired_at' => time() + 3,
    ]), $cipher, $key, $options = 0, $iv);

    $ticket = bin2hex($iv) . "." . $encrypted;
    $response->getBody()->write(json_encode([
        'login_url' => "http://" . $site->host . $site->receive_code_path . "?login_ticket=" . urlencode($ticket) . '&redirect_path=' . urlencode($url_info['path'])
    ]));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// 給子站驗證ticket是否正確的API
$app->post('/api/verify-ticket', function (Request $request, Response $response, $args) {
    $parsed_body = $request->getParsedBody();

    $code = $parsed_body['code'];
    $ticket = $parsed_body['ticket'];

    $key = getenv('KEY');
    $split_texts = explode('.', $ticket);
    $cipher = 'AES-256-CBC';
    $iv = hex2bin($split_texts[0]);
    $decrypted_data = json_decode(openssl_decrypt($split_texts[1], $cipher, $key, $options = 0, $iv));
    $site_id = (isset($decrypted_data->site_id)) ? $decrypted_data->site_id : null ;

    if (
        empty($decrypted_data) ||
        $decrypted_data->expired_at < time() ||
        !Capsule::table('site')->where('id', '=', $site_id)->where('verify_ticket_code', '=', $code)->exists()
    ) {
        $response->getBody()->write(json_encode([
            'result' => 'Verify fail',
        ]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    $response->getBody()->write(json_encode([
        'result' => 'Verify Success',
        'username' => $decrypted_data->username
    ]));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->run();