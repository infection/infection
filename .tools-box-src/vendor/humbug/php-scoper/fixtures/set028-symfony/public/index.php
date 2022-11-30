<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\App\Kernel;
use _HumbugBoxb47773b41c19\Symfony\Component\Debug\Debug;
use _HumbugBoxb47773b41c19\Symfony\Component\Dotenv\Dotenv;
use _HumbugBoxb47773b41c19\Symfony\Component\HttpFoundation\Request;
require __DIR__ . '/../vendor/autoload.php';
if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    if (!\class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new Dotenv())->load(__DIR__ . '/../.env');
}
$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $env);
if ($debug) {
    \umask(00);
    Debug::enable();
}
if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? \false) {
    Request::setTrustedProxies(\explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}
if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? \false) {
    Request::setTrustedHosts(\explode(',', $trustedHosts));
}
$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
