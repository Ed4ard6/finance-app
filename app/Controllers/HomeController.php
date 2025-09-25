<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;   // ⬅️ Importa el middleware

class HomeController extends Controller
{
    public function index(): void
    {
        // Protege el dashboard: si no hay sesión -> redirige a /login
        Auth::requireLogin();

        // Ejemplo: prueba de conexión (opcional)
        $pdo  = Database::connection();
        $stmt = $pdo->query('SELECT NOW() as ahora');
        $row  = $stmt->fetch();

        $this->view('dashboard/index', [
            'titulo' => 'Panel de Finanzas',
            'ahora'  => $row['ahora'] ?? null,
        ]);
    }
}
