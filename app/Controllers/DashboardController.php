<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\TransactionRepository;

class DashboardController extends Controller
{
    /** Vista del panel general con datos reales */
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=/dashboard'); exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $ym     = $_GET['ym'] ?? date('Y-m');

        $repo = new TransactionRepository();

        // KPIs + distribución
        $kpis = $repo->getMonthlyKpis($userId, $ym);
        $dist = $repo->getExpenseDistributionByParent($userId, $ym);

        // Detecta categoría salario si existe
        $salaryCategoryId = $repo->getSalaryCategoryId($userId);
        $hasSalary        = $repo->hasSalaryForMonth($userId, $ym, $salaryCategoryId);

        // Variables para el layout
        $titulo    = 'Panel general';
        $pageClass = 'page-dashboard';

        // Enviamos a la vista
        require BASE_PATH . '/app/Views/dashboard/index.php';
    }

    /** POST /dashboard/salary → crear/actualizar salario del mes */
    public function upsertSalary()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=/dashboard'); exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $ym     = $_POST['ym'] ?? date('Y-m');
        $amount = (float)($_POST['amount'] ?? 0);
        $catId  = (int)($_POST['category_id'] ?? 0);

        // Si no llega category_id por formulario, intenta detectar automáticamente
        if ($catId <= 0) {
            $repo = new TransactionRepository();
            $auto = $repo->getSalaryCategoryId($userId);
            if ($auto) $catId = $auto;
        }

        if ($amount <= 0 || $catId <= 0) {
            $_SESSION['flash_error'] = 'Monto o categoría inválidos para el salario.';
            header('Location: /dashboard?ym='.$ym); exit;
        }

        (new TransactionRepository())->upsertMonthlySalary($userId, $ym, $amount, $catId);

        $_SESSION['flash_success'] = 'Salario del mes actualizado correctamente.';
        header('Location: /dashboard?ym='.$ym); exit;
    }
}
