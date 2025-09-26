<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    /** GET / */
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // Si está autenticado → directo al Dashboard
        if (!empty($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        // Si NO está autenticado → muestra landing pública
        // Ajusta la ruta si tu vista está en otra carpeta/nombre.
        require __DIR__ . '/../Views/home/index.php';
    }
}
