<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller
{
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=/dashboard');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $pdo    = Database::connection();

        // --- KPIs (ajusta nombres de tabla/campos si los tuyos difieren) ---
        // Supuesto: tabla "movimientos" con columnas: id, user_id, fecha (DATE/DATETIME), concepto, tipo ('income'|'expense'), monto (NUMERIC)
        $totals = ['ingresos' => 0, 'gastos' => 0];

        $sqlTotals = "
            SELECT
              COALESCE(SUM(CASE WHEN tipo = 'income'  THEN monto ELSE 0 END),0) AS ingresos,
              COALESCE(SUM(CASE WHEN tipo = 'expense' THEN monto ELSE 0 END),0) AS gastos
            FROM movimientos
            WHERE user_id = :uid
        ";
        try {
            $st = $pdo->prepare($sqlTotals);
            $st->execute([':uid' => $userId]);
            $totals = $st->fetch(PDO::FETCH_ASSOC) ?: $totals;
        } catch (\Throwable $e) {
            // si la tabla aún no existe, mostramos 0 sin romper la vista
            $totals = ['ingresos' => 0, 'gastos' => 0];
        }

        $ingresos = (float) $totals['ingresos'];
        $gastos   = (float) $totals['gastos'];
        $balance  = $ingresos - $gastos;

        // --- Últimos movimientos ---
        $movs = [];
        $sqlMovs = "
            SELECT id, fecha, concepto, tipo, monto
            FROM movimientos
            WHERE user_id = :uid
            ORDER BY fecha DESC, id DESC
            LIMIT 8
        ";
        try {
            $st = $pdo->prepare($sqlMovs);
            $st->execute([':uid' => $userId]);
            $movs = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $movs = [];
        }

        // --- Metas (opcional; ajusta a tus columnas reales) ---
        // Supuesto: tabla "metas" con: id, user_id, nombre, objetivo (target), ahorrado (saved), estado ('activa'|...)
        $goals = [];
        $sqlGoals = "
            SELECT id, nombre AS name, objetivo AS target_amount, ahorrado AS saved_amount
            FROM metas
            WHERE user_id = :uid AND estado = 'activa'
            ORDER BY id DESC
            LIMIT 3
        ";
        try {
            $st = $pdo->prepare($sqlGoals);
            $st->execute([':uid' => $userId]);
            $goals = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $goals = [];
        }

        // Render (usa tu carpeta real app/Views)
        $vars = compact('ingresos','gastos','balance','movs','goals');
        extract($vars);
        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
