<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require __DIR__ . '/../app/core/Database.php';
require __DIR__ . '/../app/core/JsonResponse.php';
require __DIR__ . '/../app/core/Request.php';
require __DIR__ . '/../app/core/Auth.php';

require __DIR__ . '/../app/models/User.php';
require __DIR__ . '/../app/models/PasswordReset.php';
require __DIR__ . '/../app/models/City.php';
require __DIR__ . '/../app/models/WeatherRecord.php';

require __DIR__ . '/../app/services/OpenWeatherService.php';

require __DIR__ . '/../app/controllers/HomeController.php';
require __DIR__ . '/../app/controllers/AuthController.php';
require __DIR__ . '/../app/controllers/WeatherController.php';

if (!function_exists('starts_with')) {
    function starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

$config = require __DIR__ . '/../app/config/database.php';
$routes = require __DIR__ . '/../app/routes/web.php';
$apiRoutes = require __DIR__ . '/../app/routes/api.php';

$database = new Database($config);
$pdo = $database->connection();

$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

$path = $requestUriPath;
if ($baseDir !== '' && $baseDir !== '.' && starts_with($path, $baseDir)) {
    $path = substr($path, strlen($baseDir));
}

if ($path === '' || $path === false) {
    $path = '/';
}

$path = '/' . ltrim((string) $path, '/');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (starts_with($path, '/api/')) {
    $routeKey = $method . ' ' . $path;

    if (!isset($apiRoutes[$routeKey])) {
        JsonResponse::send(['error' => 'Endpoint não encontrado'], 404);
    }

    [$controllerName, $action] = $apiRoutes[$routeKey];
    $controller = new $controllerName($pdo);
    $controller->$action();
    exit;
}

if (!isset($routes[$path])) {
    http_response_code(404);
    echo '404 - Página não encontrada';
    exit;
}

[$controllerName, $method] = $routes[$path];

$controller = new $controllerName();
$controller->$method($pdo);
