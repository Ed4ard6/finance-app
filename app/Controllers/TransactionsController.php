<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class TransactionsController extends Controller
{
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location: /login?redirect='.$_SERVER['REQUEST_URI']); exit; }

        $pdo = Database::connection();

        // --------- Entrada de filtros ---------
        // Mes rápido (YYYY-MM)
        $ym   = $_GET['ym']   ?? date('Y-m');
        $view = $_GET['view'] ?? 'all'; // all | expense | income

        // Rango (dd/mm/yyyy); si llega completo, IGNORA $ym
        $fromStr = $_GET['from'] ?? '';
        $toStr   = $_GET['to']   ?? '';

        $from = null; $to = null;
        if ($fromStr && $toStr) {
            $from = $this->dmyToYmd($fromStr);
            $to   = $this->dmyToYmd($toStr);
        }

        // Construcción base de fechas
        if ($from && $to) {
            $start = $from;
            // fin exclusivo (+1 día)
            $toDate = new \DateTime($to);
            $toDate->modify('+1 day');
            $end = $toDate->format('Y-m-d');
        } else {
            // mes rápido
            if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
            [$y, $m] = explode('-', $ym);
            $start = sprintf('%04d-%02d-01', (int)$y, (int)$m);
            $end   = date('Y-m-d', strtotime("$start +1 month"));
        }

        // Filtro de tipo
        $where  = 't.user_id = :uid AND t.date_at >= :start AND t.date_at < :end AND t.status = 1';
        $params = [':uid'=>$userId, ':start'=>$start, ':end'=>$end];

        if ($view === 'expense') {
            $where .= " AND c.kind IN ('expense','debt','saving')";
        } elseif ($view === 'income') {
            $where .= " AND c.kind = 'income'";
        }

        // Filtros por categorías (por tipo), llegan como CSV de IDs
        $catExpense = $this->csvToArray($_GET['cat_expense'] ?? '');
        $catDebt    = $this->csvToArray($_GET['cat_debt']    ?? '');
        $catSaving  = $this->csvToArray($_GET['cat_saving']  ?? '');
        $catIncome  = $this->csvToArray($_GET['cat_income']  ?? '');

        $catAll = array_merge($catExpense, $catDebt, $catSaving, $catIncome);
        if ($catAll) {
            // Aplica IN por category_id
            $placeholders = [];
            foreach ($catAll as $i => $id) {
                $ph = ":cat$i";
                $placeholders[] = $ph;
                $params[$ph] = (int)$id;
            }
            $where .= " AND t.category_id IN (".implode(',', $placeholders).")";
        }

        // Consulta (orden más reciente primero)
        $sql = "
            SELECT t.id, t.date_at, t.amount, t.description,
                   c.id AS category_id, c.name AS category_name, c.kind AS category_kind
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE $where
            ORDER BY t.date_at DESC, t.id DESC
        ";
        $stm = $pdo->prepare($sql);
        $stm->execute($params);
        $rows = $stm->fetchAll();

        $sumIncome = 0.0; $sumExpense = 0.0;
        foreach ($rows as $r) {
            if (($r['category_kind'] ?? '') === 'income')  $sumIncome  += (float)$r['amount'];
            if (in_array(($r['category_kind'] ?? ''), ['expense','debt','saving'])) $sumExpense += (float)$r['amount'];
        }

        // Mes bonito (para UI)
        // Si hay rango, muestro el mes de inicio como referencia
        $refDate = $from ? new \DateTime($from) : new \DateTime($start);
        if (class_exists(\IntlDateFormatter::class)) {
            $fmt = new \IntlDateFormatter('es_CO', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $refDate->getTimezone(), \IntlDateFormatter::GREGORIAN, 'LLLL yyyy');
            $mesBonito = ucfirst($fmt->format($refDate));
        } else {
            $mesBonito = ucfirst(date('F Y', $refDate->getTimestamp()));
        }

        // Categorías (para modal de filtros)
        $cats = [
            'expense' => $this->fetchCats($pdo, $userId, ['expense']),
            'debt'    => $this->fetchCats($pdo, $userId, ['debt']),
            'saving'  => $this->fetchCats($pdo, $userId, ['saving']),
            'income'  => $this->fetchCats($pdo, $userId, ['income']),
        ];

        return $this->view('transactions/index', [
            'titulo'      => 'Transacciones',
            'pageClass'   => 'page-transactions',
            'rows'        => $rows,
            'ym'          => $ym,
            'view'        => $view,
            'mesBonito'   => $mesBonito,
            'sumIncome'   => $sumIncome,
            'sumExpense'  => $sumExpense,
            'from'        => $fromStr,
            'to'          => $toStr,
            'cats'        => $cats,
            'catSel'      => [
                'expense' => $catExpense,
                'debt'    => $catDebt,
                'saving'  => $catSaving,
                'income'  => $catIncome,
            ],
        ]);
    }

    public function create()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location: /login?redirect='.$_SERVER['REQUEST_URI']); exit; }

        $pdo = Database::connection();
        $this->ensureDefaultCategories($pdo, $userId);

        $type = $_GET['type'] ?? 'expense';
        $type = in_array($type, ['expense','income']) ? $type : 'expense';

        $catsExpense = $pdo->prepare("
            SELECT id, name, kind
            FROM categories
            WHERE user_id=:uid AND kind IN ('expense','debt','saving') AND (is_archived IS NULL OR is_archived=0)
            ORDER BY FIELD(kind,'expense','debt','saving'), sort_order, name
        ");
        $catsExpense->execute([':uid'=>$userId]);
        $catsExpense = $catsExpense->fetchAll();

        $catsIncome = $pdo->prepare("
            SELECT id, name, kind
            FROM categories
            WHERE user_id=:uid AND kind='income' AND (is_archived IS NULL OR is_archived=0)
            ORDER BY sort_order, name
        ");
        $catsIncome->execute([':uid'=>$userId]);
        $catsIncome = $catsIncome->fetchAll();

        return $this->view('transactions/create', [
            'titulo'      => 'Agregar transacción',
            'pageClass'   => 'page-transactions-create',
            'type'        => $type,
            'catsExpense' => $catsExpense,
            'catsIncome'  => $catsIncome,
            'today'       => date('Y-m-d'),
        ]);
    }

    public function store()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location: /login?redirect='.$_SERVER['REQUEST_URI']); exit; }

        $pdo = Database::connection();

        $category_id = (int)($_POST['category_id'] ?? 0);
        $amountRaw   = trim($_POST['amount'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date_at     = $_POST['date_at'] ?? date('Y-m-d');

        $amount = $this->parseAmount($amountRaw);

        $catStmt = $pdo->prepare("SELECT id, kind FROM categories WHERE id=:id AND user_id=:uid");
        $catStmt->execute([':id'=>$category_id, ':uid'=>$userId]);
        $cat = $catStmt->fetch();

        $errors = [];
        if (!$cat)                                        $errors[] = 'Selecciona una categoría válida.';
        if ($amount <= 0)                                 $errors[] = 'El monto debe ser mayor a 0.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_at)) $errors[] = 'Fecha inválida (YYYY-MM-DD).';

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: /transactions/create?type=' . (($cat['kind'] ?? 'expense') === 'income' ? 'income' : 'expense'));
            exit;
        }

        if ($cat['kind'] !== 'income' && $amount > 0) $amount = -$amount;

        $stmt = $pdo->prepare("
            INSERT INTO transactions (user_id, category_id, date_at, description, amount, status, source)
            VALUES (:uid, :cat, :date_at, :descr, :amount, 1, 'manual')
        ");
        $stmt->execute([
            ':uid'     => $userId,
            ':cat'     => $category_id,
            ':date_at' => $date_at,
            ':descr'   => $description,
            ':amount'  => $amount,
        ]);

        $_SESSION['flash_success'] = 'Transacción registrada.';
        $ym = substr($date_at, 0, 7);
        header("Location: /transactions?ym=$ym&view=all");
        exit;
    }

    public function edit()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location: /login'); exit; }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { header('Location:/transactions'); exit; }

        $pdo = Database::connection();
        $tx = $pdo->prepare("
            SELECT t.*, c.kind AS category_kind
            FROM transactions t
            LEFT JOIN categories c ON c.id=t.category_id
            WHERE t.id=:id AND t.user_id=:uid
        ");
        $tx->execute([':id'=>$id, ':uid'=>$userId]);
        $tx = $tx->fetch();
        if (!$tx) { header('Location:/transactions'); exit; }

        $catsExpense = $pdo->prepare("
            SELECT id, name, kind FROM categories
            WHERE user_id=:uid AND kind IN ('expense','debt','saving') AND (is_archived IS NULL OR is_archived=0)
            ORDER BY FIELD(kind,'expense','debt','saving'), sort_order, name
        "); $catsExpense->execute([':uid'=>$userId]); $catsExpense=$catsExpense->fetchAll();

        $catsIncome = $pdo->prepare("
            SELECT id, name, kind FROM categories
            WHERE user_id=:uid AND kind='income' AND (is_archived IS NULL OR is_archived=0)
            ORDER BY sort_order, name
        "); $catsIncome->execute([':uid'=>$userId]); $catsIncome=$catsIncome->fetchAll();

        $type = ($tx['category_kind']==='income') ? 'income' : 'expense';

        return $this->view('transactions/edit', [
            'titulo'      => 'Editar transacción',
            'pageClass'   => 'page-transactions-edit',
            'tx'          => $tx,
            'type'        => $type,
            'catsExpense' => $catsExpense,
            'catsIncome'  => $catsIncome,
        ]);
    }

    public function update()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location:/login'); exit; }

        $pdo = Database::connection();

        $id         = (int)($_POST['id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $amountRaw  = trim($_POST['amount'] ?? '');
        $description= trim($_POST['description'] ?? '');
        $date_at    = $_POST['date_at'] ?? date('Y-m-d');

        $amount = $this->parseAmount($amountRaw);

        $c = $pdo->prepare("SELECT id, kind FROM categories WHERE id=:cid AND user_id=:uid");
        $c->execute([':cid'=>$categoryId, ':uid'=>$userId]);
        $cat = $c->fetch();

        if (!$id || !$cat || $amount<=0) {
            $_SESSION['flash_error']='Datos inválidos.'; header('Location:/transactions'); exit;
        }

        if ($cat['kind'] !== 'income' && $amount > 0) $amount = -$amount;

        $u = $pdo->prepare("
            UPDATE transactions
               SET category_id=:cid, date_at=:date_at, description=:descr, amount=:amount
             WHERE id=:id AND user_id=:uid
        ");
        $u->execute([
            ':cid'=>$categoryId, ':date_at'=>$date_at, ':descr'=>$description, ':amount'=>$amount,
            ':id'=>$id, ':uid'=>$userId
        ]);

        $_SESSION['flash_success']='Transacción actualizada.';
        $ym = substr($date_at,0,7);
        header("Location:/transactions?ym=$ym&view=all");
        exit;
    }

    public function destroy()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location:/login'); exit; }

        $id = (int)($_POST['id'] ?? 0);
        if ($id<=0) { header('Location:/transactions'); exit; }

        $pdo = Database::connection();
        $pdo->prepare("DELETE FROM transactions WHERE id=:id AND user_id=:uid")->execute([':id'=>$id, ':uid'=>$userId]);

        $_SESSION['flash_success']='Transacción eliminada.';
        header('Location:/transactions'); exit;
    }

    // ---------- Helpers ----------

    private function csvToArray(string $csv): array
    {
        if (!$csv) return [];
        $parts = array_filter(array_map('trim', explode(',', $csv)), fn($v)=>$v !== '');
        return array_values(array_unique(array_map('intval', $parts)));
    }

    private function dmyToYmd(string $dmy): ?string
    {
        // dd/mm/yyyy -> yyyy-mm-dd
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dmy)) return null;
        [$d,$m,$y] = explode('/',$dmy);
        return sprintf('%04d-%02d-%02d',(int)$y,(int)$m,(int)$d);
    }

    private function parseAmount(string $raw): float
    {
        // admite: "1.200.000", "1,200,000", "1200000", "1200.50"
        $clean = preg_replace('/[^\d,\.]/', '', $raw);
        // si tiene ambos separadores, asumimos que el último es decimal
        if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
            $clean = str_replace('.', '', $clean);      // miles
            $clean = str_replace(',', '.', $clean);     // decimales
        } else {
            // si solo tiene comas, convierto a punto
            if (strpos($clean, ',') !== false && strpos($clean, '.') === false) {
                $clean = str_replace(',', '.', $clean);
            }
        }
        return (float)$clean;
    }

    private function fetchCats(\PDO $pdo, int $uid, array $kinds)
    {
        $in = implode("','", array_map('strval', $kinds));
        $sql = "SELECT id, name FROM categories
                WHERE user_id=:uid AND kind IN ('$in') AND (is_archived IS NULL OR is_archived=0)
                ORDER BY sort_order, name";
        $q = $pdo->prepare($sql); $q->execute([':uid'=>$uid]);
        return $q->fetchAll();
    }

    private function ensureDefaultCategories(\PDO $pdo, int $userId): void
    {
        $q = $pdo->prepare("SELECT kind, COUNT(*) n FROM categories WHERE user_id=:uid AND (is_archived IS NULL OR is_archived=0) GROUP BY kind");
        $q->execute([':uid'=>$userId]);
        $have = [];
        foreach ($q->fetchAll() as $r) $have[$r['kind']] = (int)$r['n'];

        $toInsert = [];

        if (empty($have['expense'])) {
            $expense = ['Comida','Transporte','Servicios','Renta','Salud','Entretenimiento','Educación','Impuestos','Gastos generales','Corte'];
            $i=1; foreach ($expense as $name) $toInsert[] = ['name'=>$name, 'kind'=>'expense', 'sort'=>$i++ ];
        }
        if (empty($have['income'])) {
            $income = ['Sueldo','Freelance','Intereses','Otros ingresos'];
            $i=1; foreach ($income as $name) $toInsert[] = ['name'=>$name, 'kind'=>'income', 'sort'=>$i++ ];
        }
        if (empty($have['debt'])) {
            $debt = ['Créditos y deudas','Tarjeta de crédito','Smart → Banco de Bogotá'];
            $i=1; foreach ($debt as $name) $toInsert[] = ['name'=>$name, 'kind'=>'debt', 'sort'=>$i++ ];
        }
        if (empty($have['saving'])) {
            $saving = ['Ahorro general','Ahorro viaje'];
            $i=1; foreach ($saving as $name) $toInsert[] = ['name'=>$name, 'kind'=>'saving', 'sort'=>$i++ ];
        }

        if (!$toInsert) return;

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO categories
            (user_id, parent_id, name, kind, color_hex, is_variable, sort_order, is_archived)
            VALUES (:uid, NULL, :name, :kind, '#888888', 0, :sort, 0)
        ");
        foreach ($toInsert as $row) {
            $stmt->execute([
                ':uid'  => $userId,
                ':name' => $row['name'],
                ':kind' => $row['kind'],
                ':sort' => $row['sort'],
            ]);
        }
        $pdo->commit();
    }
}
