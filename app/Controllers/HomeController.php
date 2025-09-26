<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;

class HomeController extends Controller
{
    // Landing pública
    public function index(): void
    {
        $this->view('home/index', [
            'titulo' => 'Finanzas - Inicio',
        ]);
    }

    // Dashboard (requiere login)
    public function dashboard(): void
    {
        Auth::requireLogin();

        $pdo  = Database::connection();
        $stmt = $pdo->query('SELECT NOW() as ahora');
        $row  = $stmt->fetch();

        $this->view('dashboard/index', [
            'titulo' => 'Panel de Finanzas',
            'ahora'  => $row['ahora'] ?? null,
        ]);
    }
}
