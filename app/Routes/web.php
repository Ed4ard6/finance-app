<?php

use App\Core\Router;
use App\Controllers\HomeController;


$router = new Router();


$router->get('/', [HomeController::class, 'index']);


// Rutas futuras (auth, ingresos, gastos, metas):
// $router->get('/login', [AuthController::class, 'showLogin']);
// $router->post('/login', [AuthController::class, 'login']);


return $router;
