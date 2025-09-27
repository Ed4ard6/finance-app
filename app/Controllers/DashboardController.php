<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\TransactionRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\SalaryRepository;

class DashboardController extends Controller
{
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/dashboard'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $ym     = $_GET['ym'] ?? date('Y-m');

        $txRepo   = new TransactionRepository();
        $catRepo  = new CategoryRepository();
        $salRepo  = new SalaryRepository();

        // Garantiza que exista una categoría income para salario
        $salaryCat   = $catRepo->ensureSalary($userId);

        // Trae el último salario (tabla salaries)
        $salary      = $salRepo->latest($userId); // puede ser null

        // Si existe salario, aplica/asegura la transacción del mes
        if ($salary && $salary['amount'] > 0) {
            $useCatId = $salary['category_id'] ?: (int)$salaryCat['id'];
            $txRepo->ensureMonthlySalaryTransaction($userId, $ym, (float)$salary['amount'], $useCatId);
        }

        // KPIs + distribución
        $kpis = $txRepo->getMonthlyKpis($userId, $ym);
        $dist = $txRepo->getExpenseDistributionByParent($userId, $ym);

        $titulo    = 'Panel general';
        $pageClass = 'page-dashboard';

        // Para la vista (mostrar el último salario guardado en el input)
        $currentSalaryAmount = $salary['amount'] ?? null;

        require BASE_PATH . '/app/Views/dashboard/index.php';
    }

    /** Guarda un nuevo registro en salaries (histórico) y vuelve al dashboard */
    public function saveSalary()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/dashboard'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $amount = (float)($_POST['salary_amount'] ?? 0);

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Ingresa un salario válido.';
            header('Location: /dashboard'); exit;
        }

        $catRepo = new CategoryRepository();
        $salRepo = new SalaryRepository();

        // Aseguramos que exista categoría de salario; guardamos nueva fila en salaries
        $salaryCat = $catRepo->ensureSalary($userId);
        $salRepo->insert($userId, $amount, (int)$salaryCat['id'], date('Y-m-d'));

        $_SESSION['flash_success'] = 'Salario guardado. Se aplicará automáticamente cada mes.';
        header('Location: /dashboard'); exit;
    }
}
