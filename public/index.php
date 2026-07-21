<?php

// Discard any PHP startup notices (e.g. "file created in system's temporary directory")
// that were emitted before this script ran and would break JSON API responses.
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

// Also suppress any future runtime errors from being displayed
ini_set('display_errors', '0');

// Permanently fix by setting upload_tmp_dir in php.ini to a valid writable path

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

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
