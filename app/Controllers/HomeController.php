<?php

namespace App\Controllers;


use App\Core\Controller;
use App\Core\Database;


class HomeController extends Controller
{
    public function index(): void
    {
        // Ejemplo: prueba de conexión (opcional)
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT NOW() as ahora');
        $row = $stmt->fetch();


        $this->view('dashboard/index', [
            'titulo' => 'Panel de Finanzas',
            'ahora' => $row['ahora'] ?? null,
        ]);
    }
}
