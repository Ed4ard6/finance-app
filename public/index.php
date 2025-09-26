<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

use App\Core\Router;

// Sesión (si la usas globalmente)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// 1) Instanciar router
$router = new Router();

// 2) Cargar las rutas (aquí $router ya existe)
require BASE_PATH . '/app/Routes/web.php';

// 3) Despachar
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($method, $path);
