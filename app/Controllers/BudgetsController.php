<?php
namespace App\Controllers;

use App\Repositories\BudgetRepository;

class BudgetsController
{
    /** Vista principal con totales, período, drill-down */
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { header('Location: /login?redirect=/budgets'); exit; }

        // Mes actual o ?ym=YYYY-MM
        $ym = isset($_GET['ym']) && preg_match('/^\d{4}-\d{2}$/', $_GET['ym'])
            ? $_GET['ym']
            : date('Y-m');

        // Período: m=mes completo (default), h1=01–15, h2=16–30/31
        $range = $_GET['range'] ?? 'm';
        if (!in_array($range, ['m', 'h1', 'h2'], true)) $range = 'm';

        // Rango de fechas [start, end)
        [$firstDay, $nextMonth] = $this->monthBounds($ym);
        [$start, $end] = $this->rangeBounds($ym, $range);

        $repo = new BudgetRepository();
        $rows = $repo->listWithUsage($userId, $ym, $start, $end);

        // Totales
        $totalBudget = 0;
        $totalUsed   = 0;
        foreach ($rows as $r) {
            $totalBudget += (int)($r['budget_amount'] ?? 0);
            $totalUsed   += (int)($r['used_amount']   ?? 0);
        }
        $totalAvail = $totalBudget - $totalUsed;

        $titulo    = 'Presupuestos';
        $pageClass = 'page-budgets';
        $mesBonito = $this->mesBonito($ym);
        $ymPrev    = date('Y-m', strtotime($ym . '-01 -1 month'));
        $ymNext    = date('Y-m', strtotime($ym . '-01 +1 month'));

        require BASE_PATH . '/app/Views/budgets/index.php';
    }

    /** Copiar del mes anterior (POST) */
    public function copyPrev(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { header('Location: /login?redirect=/budgets'); exit; }

        $ym = $_POST['ym'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $ym)) { http_response_code(400); exit('Mes inválido'); }

        $repo = new BudgetRepository();
        $repo->copyFromPreviousMonth($userId, $ym);

        header('Location: /budgets?ym=' . urlencode($ym));
        exit;
    }

    /** Drill-down: lista de transacciones para una categoría en el rango */
    public function detail(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { http_response_code(401); exit('No autorizado'); }

        $ym   = $_GET['ym'] ?? date('Y-m');
        $cid  = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
        $range = $_GET['range'] ?? 'm';

        if ($cid <= 0 || !preg_match('/^\d{4}-\d{2}$/', $ym)) {
            http_response_code(400); exit('Parámetros inválidos');
        }

        [$start, $end] = $this->rangeBounds($ym, $range);

        $repo = new BudgetRepository();
        $cat  = $repo->getCategory($userId, $cid);
        if (!$cat) { http_response_code(404); exit('Categoría no encontrada'); }

        $txs = $repo->transactionsByCategoryInRange($userId, $cid, $start, $end);
        $mesBonito = $this->mesBonito($ym);
        $rangeName = $this->rangeName($range);

        // Render parcial simple
        require BASE_PATH . '/app/Views/budgets/detail.php';
    }

    // ---------- Edición en bloque (ya tenías) ----------

    public function bulk(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { header('Location: /login?redirect=/budgets/bulk'); exit; }

        $ym = isset($_GET['ym']) && preg_match('/^\d{4}-\d{2}$/', $_GET['ym'])
            ? $_GET['ym']
            : date('Y-m');

        $repo = new BudgetRepository();
        $rows = $repo->listForBulk($userId, $ym);

        $titulo    = 'Editar presupuestos del mes';
        $pageClass = 'page-budgets-bulk';
        $mesBonito = $this->mesBonito($ym);
        $ymPrev    = date('Y-m', strtotime($ym . '-01 -1 month'));
        $ymNext    = date('Y-m', strtotime($ym . '-01 +1 month'));

        require BASE_PATH . '/app/Views/budgets/bulk.php';
    }

    public function bulkSave(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { header('Location: /login?redirect=/budgets/bulk'); exit; }

        $ym = $_POST['ym'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}$/', $ym)) { http_response_code(400); exit('Mes inválido'); }

        $amounts = $_POST['amount'] ?? [];
        if (!is_array($amounts)) $amounts = [];

        require_once BASE_PATH . '/app/Support/money.php';

        $toUpsert = [];
        $toDelete = [];

        foreach ($amounts as $cid => $raw) {
            $cid = (int)$cid;
            if ($cid <= 0) continue;

            $n = trim((string)$raw);
            if ($n === '') { $toDelete[] = $cid; continue; }

            $amt = parse_cop($n);
            $amt = abs($amt);
            if ($amt === 0) { $toDelete[] = $cid; }
            else            { $toUpsert[] = [$cid, $amt]; }
        }

        $repo = new BudgetRepository();
        $repo->saveBulk($userId, $ym, $toUpsert, $toDelete);

        header('Location: /budgets?ym=' . urlencode($ym));
        exit;
    }

    // ---------- helpers ----------

    public function mesBonito(string $ym): string
    {
        $ts = strtotime($ym . '-01');
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return ucfirst($meses[(int)date('n',$ts)-1]) . ' ' . date('Y',$ts);
    }

    /** Devuelve [primer día del mes, primer día del mes siguiente] (YYYY-MM-DD) */
    private function monthBounds(string $ym): array
    {
        $first = $ym . '-01';
        $next  = date('Y-m-d', strtotime($first . ' +1 month'));
        return [$first, $next];
    }

    /** Devuelve [start,end) para m|h1|h2 */
    private function rangeBounds(string $ym, string $range): array
    {
        [$first, $next] = $this->monthBounds($ym);
        if ($range === 'h1') {
            $start = $first;
            $end   = date('Y-m-d', strtotime($first . ' +15 days')); // 01–15
        } elseif ($range === 'h2') {
            $start = date('Y-m-d', strtotime($first . ' +15 days')); // 16–fin
            $end   = $next;
        } else {
            $start = $first; $end = $next;
        }
        return [$start, $end];
    }

    private function rangeName(string $range): string
    {
        return $range === 'h1' ? '01–15' : ($range === 'h2' ? '16–fin' : 'Mes completo');
    }
}
