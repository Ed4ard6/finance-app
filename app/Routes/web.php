<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TransactionsController;
use App\Controllers\RulesController;
use App\Middleware\Auth;

// ===== Públicas
$router->get('/',           [HomeController::class, 'index']);
$router->get('/login',      [AuthController::class, 'showLogin']);
$router->post('/login',     [AuthController::class, 'login']);
$router->get('/register',   [AuthController::class, 'showRegister']);
$router->post('/register',  [AuthController::class, 'register']);
$router->get('/logout',     [AuthController::class, 'logout']);
$router->post('/logout',    [AuthController::class, 'logout']);

// ===== Protegidas (vistas GET)
$router->get('/dashboard', function () {
    (new Auth)();
    (new DashboardController)->index(); // Panel general
});

$router->get('/transactions', function () {
    (new Auth)();
    // Si tienes un controlador para listar, úsalo.
    // (new TransactionsController)->index();
    require BASE_PATH . '/app/Views/transactions/index.php';
});

$router->get('/budgets', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/budgets/index.php';
});

$router->get('/rules', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/rules/index.php';
});

$router->get('/savings', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/savings/index.php';
});

$router->get('/debts', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/debts/index.php';
});

// Atajos de reportes que usa el header / dashboard
$router->get('/debts/compare', function () {
    (new Auth)();
    $titulo = 'Comparador de deudas'; $pageClass='page-debt-compare';
    require BASE_PATH . '/app/Views/reports/comparador_deudas.php';
});
$router->get('/reports/waterfall', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/reports/waterfall.php';
});
$router->get('/reports/calendar', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/reports/calendario.php';
});
$router->get('/planner', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/planner/quincena.php';
});
$router->get('/reports/monthly', function () {
    (new Auth)();
    require BASE_PATH . '/app/Views/reports/mensual.php';
});

// ===== Acciones POST (persistencia)
$router->post('/settings/salary', function () {
    (new Auth)();
    (new DashboardController)->saveSalary(); // guarda salario fijo (tabla salaries) y redirige
});

$router->post('/transactions', function () {
    (new Auth)();
    (new TransactionsController)->store(); // crea gasto/ingreso/ahorro/deuda
});

$router->post('/rules/generate', function () {
    (new Auth)();
    (new RulesController)->generate(); // placeholder
});
