<?php

namespace ComocoSsoDemo\SsoServer\Services;

use Illuminate\Database\Capsule\Manager as Capsule;

class ResponseService
{
    public static function generateJsonResponse($response, array $content, $status_code = 200)
    {
        $response->getBody()->write(json_encode($content));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status_code);
    }
}
