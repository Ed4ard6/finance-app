<?php
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;

$router = new Router();

/** Landing pública */
$router->get('/', [HomeController::class, 'index']);

/** Dashboard (protegido) */
$router->get('/dashboard', [HomeController::class, 'dashboard']);

/** Autenticación */
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/logout',   [AuthController::class, 'logout']);

return $router;
