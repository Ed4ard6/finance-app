<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Middleware\Auth;

// Públicas
$router->get('/',           [HomeController::class, 'index']);
$router->get('/login',      [AuthController::class, 'showLogin']);
$router->post('/login',     [AuthController::class, 'login']);
$router->get('/register',   [AuthController::class, 'showRegister']);
$router->post('/register',  [AuthController::class, 'register']);

// Logout: acepta GET y POST
$router->get('/logout',     [AuthController::class, 'logout']);
$router->post('/logout',    [AuthController::class, 'logout']);

// Protegida
$router->get('/dashboard', function () {
    (new Auth)();
    (new DashboardController)->index();
});
