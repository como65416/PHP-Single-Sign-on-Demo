<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

use ComocoSsoDemo\SsoServer\Middlewares\JsonBodyParserMiddleware;
use Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->add(new JsonBodyParserMiddleware);

/*
 * 登入用的API，登入成功取得token
 */
$app->post('/api/login', function (Request $request, Response $response, $args) {
    $parsed_body = $request->getParsedBody();

    $username = (isset($parsed_body['username'])) ? $parsed_body['username'] : '';
    $password = (isset($parsed_body['password'])) ? $parsed_body['password'] : '';

    // 驗證密碼
    $account = Capsule::table('account')->where('username', '=', $username)->first();
    if ($account == null || !password_verify($password, $account->password)) {
      $response->getBody()->write(json_encode([
          'result' => 'FAIL',
      ]));
      return $response->withHeader('Content-Type', 'application/json')
          ->withStatus(401);
    }

    // 簽發JWT token
    $key = getenv('KEY');
    $response->getBody()->write(json_encode([
        'result' => 'SUCCESS',
        'token' => JWT::encode(['username' => $username, 'exp' => time() + 21600], $key)
    ]));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
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
      ->select('id', 'name')
      ->whereIn('id', $site_ids)
      ->get();

    $response->getBody()->write(json_encode($sites->toArray()));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// 發送轉址URL
$app->get('/api/to-site', function (Request $request, Response $response, $args) {
    $query_params = $request->getQueryParams();
    $site_id = $query_params['siteId'];

    $key = getenv('KEY');
    $header_authorization = $request->getHeaderLine('Authorization');
    preg_match('/^Bearer[ ]+(.*?\..*?\..*?)$/', $header_authorization, $match);
    $token_content = JWT::decode($match[1], $key, array('HS256'));
    $username = $token_content->username;

    if (!Capsule::table('account_site_permission')->where('site_id', '=', $site_id)->where('username', '=', $username)->exists()) {
      $response->getBody()->write(json_encode([
          'result' => 'Permission denied',
      ]));
      return $response->withHeader('Content-Type', 'application/json')
          ->withStatus(403);
    }

    $site = Capsule::table('site')->where('id', '=', $site_id)->first();
    $cipher = 'AES-256-CBC';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt(json_encode([
      'username' => $username,
      'site_id' => $site_id
    ]), $cipher, $key, $options = 0, $iv);

    $code = bin2hex($iv) . "." . $encrypted;
    $response->getBody()->write(json_encode([
        'login_url' => $site->receive_code_url . "?login_code=" . $code
    ]));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// 驗證code是否正確
$app->post('/api/verify-code', function (Request $request, Response $response, $args) {
    $parsed_body = $request->getParsedBody();

    $code = $parsed_body['code'];
    $ticket = $parsed_body['ticket'];

    $key = getenv('KEY');
    $split_texts = explode('.', $code);
    $cipher = 'AES-256-CBC';
    $iv = hex2bin($split_texts[0]);
    $decrypted_data = json_decode(openssl_decrypt($split_texts[1], $cipher, $key, $options = 0, $iv));
    $site_id = (isset($decrypted_data->site_id)) ? $decrypted_data->site_id : null ;

    if (
        empty($decrypted_data) ||
        !Capsule::table('site')->where('id', '=', $site_id)->where('verify_code_ticket', '=', $ticket)->exists()
    ) {
        $response->getBody()->write(json_encode([
            'result' => 'Login fail',
        ]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    $response->getBody()->write(json_encode([
        'result' => 'Login Success',
        'usernmae' => $decrypted_data->username
    ]));
    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->run();