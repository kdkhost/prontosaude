<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Força isolamento total do instalador caso o sistema ainda não esteja instalado.
// Isso impede que variáveis de ambiente globais do servidor local (ex: XAMPP/Laragon) 
// injetem conexões de banco de dados indesejadas antes do arquivo .env ser criado.
if (!file_exists(__DIR__.'/../storage/app/installed.lock')) {
    putenv('SESSION_DRIVER=file');
    $_ENV['SESSION_DRIVER'] = 'file';
    $_SERVER['SESSION_DRIVER'] = 'file';

    putenv('CACHE_STORE=file');
    $_ENV['CACHE_STORE'] = 'file';
    $_SERVER['CACHE_STORE'] = 'file';

    putenv('QUEUE_CONNECTION=sync');
    $_ENV['QUEUE_CONNECTION'] = 'sync';
    $_SERVER['QUEUE_CONNECTION'] = 'sync';
    
    putenv('DB_CONNECTION=sqlite'); // Previne conexão externa acidental
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
