<?php

use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Dotenv\Dotenv;

// dotenv 設定
$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

// 資料庫設定
$capsuleManager = new CapsuleManager;
$capsuleManager->addConnection([
    'driver'    => 'mysql',
    'host'      => 'mysql57',
    'database'  => 'single_signon',
    'username'  => 'root',
    'password'  => 'demo_password',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsuleManager->setAsGlobal();
$capsuleManager->bootEloquent();
