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

// Protegidas
$router->get('/dashboard', function () {
    (new Auth)();
    (new DashboardController)->index(); // Panel general
});

// Transacciones
$router->get('/transactions', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/transactions/index.php';
});

// Presupuestos
$router->get('/budgets', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/budgets/index.php';
});

// Reglas
$router->get('/rules', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/rules/index.php';
});

// Ahorros
$router->get('/savings', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/savings/index.php';
});

// Deudas
$router->get('/debts', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/debts/index.php';
});

// Comparador de deudas
$router->get('/debts/compare', function () {
    (new Auth)();
    $titulo = 'Comparador de deudas'; $pageClass='page-debt-compare';
    require BASE_PATH . '/app/Views/reports/comparador_deudas.php';
});

// Cascada del mes (Waterfall)
$router->get('/reports/waterfall', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/reports/waterfall.php';
});

// Calendario / Mapa de calor
$router->get('/reports/calendar', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/reports/calendario.php';
});

// Planificador de quincena
$router->get('/planner', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/planner/quincena.php';
});

// Reporte del mes (narrado)
$router->get('/reports/monthly', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/reports/mensual.php';
});
