<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TransactionsController;
use App\Controllers\RulesController;
use App\Controllers\CategoriesController;
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
    (new DashboardController)->index();
});

$router->get('/transactions', function () {
    (new Auth)();
    (new TransactionsController)->index();
});
$router->get('/transactions/create', function () {
    (new Auth)();
    (new TransactionsController)->create();
});
$router->get('/transactions/edit', function () {
    (new Auth)();
    (new TransactionsController)->edit();  // ?id=123
});

// Estos aún no usan controlador (no modificados)
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

// ===== CATEGORÍAS (vistas GET) ———— LIMPIAS Y SIN DUPLICADOS ————
$router->get('/categories', function () {
    (new Auth)();
    (new CategoriesController)->index();
});
$router->get('/categories/create', function () {
    (new Auth)();
    (new CategoriesController)->create();
});
$router->get('/categories/edit', function () {
    (new Auth)();
    (new CategoriesController)->edit(); // espera ?id=123
});

// ===== Reportes (atajos)
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

// ===== Acciones POST
$router->post('/settings/salary', function () {
    (new Auth)();
    (new DashboardController)->saveSalary();
});

$router->post('/transactions', function () {
    (new Auth)();
    (new TransactionsController)->store();
});
$router->post('/transactions/update', function () {
    (new Auth)();
    (new TransactionsController)->update();
});
$router->post('/transactions/delete', function () {
    (new Auth)();
    (new TransactionsController)->destroy();
});

$router->post('/rules/generate', function () {
    (new Auth)();
    (new RulesController)->generate();
});

// ===== Categorías POST (persistencia)
$router->post('/categories', function () {
    (new Auth)();
    (new CategoriesController)->store();
});
$router->post('/categories/update', function () {
    (new Auth)();
    (new CategoriesController)->update();
});

/*
 * Eliminado (comentado) por duplicado:
 *
 * // $router->get('/categories', ...);
 * // $router->get('/categories/create', ...);
 * // $router->post('/categories', ...);
 * // $router->get('/categories/edit', ...);
 * // $router->post('/categories/update', ...);
 *
 * Ya están definidos arriba, una sola vez.
 */
