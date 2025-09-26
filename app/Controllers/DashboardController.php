<?php
namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    /**
     * Panel general (dashboard oficial).
     * Mantiene solo el chequeo de autenticación y renderiza la vista.
     */
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=/dashboard');
            exit;
        }

        // Variables para el layout (opcional)
        $titulo    = 'Panel general';
        $pageClass = 'page-dashboard-mock';

        // Vista única del dashboard
        require BASE_PATH . '/app/Views/dashboard/index.php';
    }
}
