<?php
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;  // ⬅️ Rutas de autenticación

$router = new Router();

/** Dashboard (protegido) */
$router->get('/', [HomeController::class, 'index']);

/** Autenticación */
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/logout',   [AuthController::class, 'logout']);

return $router;
