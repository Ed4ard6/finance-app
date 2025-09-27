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

        $ym   = $_GET['ym']   ?? date('Y-m');
        $view = $_GET['view'] ?? 'all';

        [$y, $m] = explode('-', $ym);
        $start = sprintf('%04d-%02d-01', (int)$y, (int)$m);
        $end   = date('Y-m-d', strtotime("$start +1 month"));

        $where  = 't.user_id = :uid AND t.date_at >= :start AND t.date_at < :end AND t.status = 1';
        $params = [':uid'=>$userId, ':start'=>$start, ':end'=>$end];

        if ($view === 'expense') {
            $where .= " AND c.kind IN ('expense','debt','saving')";
        } elseif ($view === 'income') {
            $where .= " AND c.kind = 'income'";
        }

        $sql = "
            SELECT t.id, t.date_at, t.amount, t.description,
                   c.name AS category_name, c.kind AS category_kind
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE $where
            ORDER BY t.date_at ASC, t.id ASC
        ";
        $stm = $pdo->prepare($sql);
        $stm->execute($params);
        $rows = $stm->fetchAll();

        $sumIncome = 0.0; $sumExpense = 0.0;
        foreach ($rows as $r) {
            if (($r['category_kind'] ?? '') === 'income')  $sumIncome  += (float)$r['amount'];
            if (in_array(($r['category_kind'] ?? ''), ['expense','debt','saving'])) $sumExpense += (float)$r['amount'];
        }

        $dt = new \DateTime($start);
        if (class_exists(\IntlDateFormatter::class)) {
            $fmt = new \IntlDateFormatter('es_CO', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $dt->getTimezone(), \IntlDateFormatter::GREGORIAN, 'LLLL yyyy');
            $mesBonito = ucfirst($fmt->format($dt));
        } else {
            $mesBonito = ucfirst(date('F Y', $dt->getTimestamp()));
        }

        return $this->view('transactions/index', [
            'titulo'     => 'Transacciones',
            'pageClass'  => 'page-transactions',
            'rows'       => $rows,
            'ym'         => $ym,
            'view'       => $view,
            'mesBonito'  => $mesBonito,
            'sumIncome'  => $sumIncome,
            'sumExpense' => $sumExpense,
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
            WHERE user_id=:uid AND kind IN ('expense','debt','saving') AND is_archived=0
            ORDER BY FIELD(kind,'expense','debt','saving'), sort_order, name
        ");
        $catsExpense->execute([':uid'=>$userId]);
        $catsExpense = $catsExpense->fetchAll();

        $catsIncome = $pdo->prepare("
            SELECT id, name, kind
            FROM categories
            WHERE user_id=:uid AND kind='income' AND is_archived=0
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

    /** Convierte cualquier string de monto en ENTERO de pesos (sin separar miles). */
    private function parseCOP(string $raw): int {
        $raw = trim($raw);
        if ($raw === '') return 0;
        $neg = str_contains($raw, '-');
        // deja solo dígitos
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') return 0;
        $n = (int)$digits;
        return $neg ? -$n : $n;
    }

    /** Fuerza el signo correcto según el tipo (income/saving => +, expense/debt => -). */
    private function normalizeByType(int $amount, string $kind): int {
        $kind = strtolower($kind);
        return in_array($kind, ['expense','debt'], true) ? -abs($amount) : abs($amount);
    }

    public function store()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = \App\Core\Auth::currentUserId();
        if (!$userId) { header('Location: /login?redirect='.$_SERVER['REQUEST_URI']); exit; }

        $pdo = Database::connection();

        // nombres de campos tolerantes (por si el form dice "kind" o "type")
        $kind        = $_POST['type'] ?? $_POST['kind'] ?? 'expense';
        $category_id = (int)($_POST['category_id'] ?? 0);
        $date_at     = $_POST['date_at'] ?? date('Y-m-d');
        $description = trim($_POST['description'] ?? '');
        $rawAmount   = $_POST['amount'] ?? '0';

        // 1) Parseo robusto + 2) Signo coherente
        $amountPesos = $this->parseCOP($rawAmount);
        $amount      = $this->normalizeByType($amountPesos, $kind);

        // validar categoría del usuario
        $catStmt = $pdo->prepare("SELECT id, kind FROM categories WHERE id=:id AND user_id=:uid");
        $catStmt->execute([':id'=>$category_id, ':uid'=>$userId]);
        $cat = $catStmt->fetch();

        $errors = [];
        if (!$cat)                                        $errors[] = 'Selecciona una categoría válida.';
        if ($amount === 0)                                $errors[] = 'El monto debe ser mayor a 0.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_at)) $errors[] = 'Fecha inválida (YYYY-MM-DD).';

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $redirType = (($cat['kind'] ?? 'expense') === 'income') ? 'income' : 'expense';
            header('Location: /transactions/create?type='.$redirType);
            exit;
        }

        $sql = "INSERT INTO transactions (user_id, category_id, date_at, description, amount, status, source)
                VALUES (:uid, :cat, :date_at, :descr, :amount, 1, 'manual')";
        $pdo->prepare($sql)->execute([
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

        // categorías
        $catsExpense = $pdo->prepare("
            SELECT id, name, kind FROM categories
            WHERE user_id=:uid AND kind IN ('expense','debt','saving') AND is_archived=0
            ORDER BY FIELD(kind,'expense','debt','saving'), sort_order, name
        "); $catsExpense->execute([':uid'=>$userId]); $catsExpense=$catsExpense->fetchAll();

        $catsIncome = $pdo->prepare("
            SELECT id, name, kind FROM categories
            WHERE user_id=:uid AND kind='income' AND is_archived=0
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

        $id          = (int)($_POST['id'] ?? 0);
        $kind        = $_POST['type'] ?? $_POST['kind'] ?? 'expense';
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $date_at     = $_POST['date_at'] ?? date('Y-m-d');
        $description = trim($_POST['description'] ?? '');
        $rawAmount   = $_POST['amount'] ?? '0';

        if ($id <= 0) { header('Location:/transactions'); exit; }

        // Transacción original
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id=:id AND user_id=:uid LIMIT 1");
        $stmt->execute([':id'=>$id, ':uid'=>$userId]);
        $orig = $stmt->fetch();
        if (!$orig) { $_SESSION['flash_error']='Transacción no encontrada.'; header('Location:/transactions'); exit; }

        // Validar categoría del usuario
        $c = $pdo->prepare("SELECT id, kind FROM categories WHERE id=:cid AND user_id=:uid");
        $c->execute([':cid'=>$categoryId, ':uid'=>$userId]);
        $cat = $c->fetch();
        if (!$cat) { $_SESSION['flash_error']='Selecciona una categoría válida.'; header('Location:/transactions'); exit; }

        // Monto robusto + signo coherente
        $amountPesos = $this->parseCOP($rawAmount);
        $amount      = $this->normalizeByType($amountPesos, $kind);

        // No-op update: no guardar si nada cambió
        $changed = false;
        $changed = $changed || ((int)$orig['category_id'] !== (int)$categoryId);
        $changed = $changed || ((string)$orig['date_at']     !== (string)$date_at);
        $changed = $changed || (trim((string)$orig['description']) !== $description);
        $changed = $changed || ((int)$orig['amount']      !== (int)$amount);

        if (!$changed) {
            $_SESSION['flash_info'] = 'No hiciste cambios.';
            $ym = substr($orig['date_at'],0,7);
            header("Location:/transactions?ym=$ym&view=all");
            exit;
        }

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

    private function ensureDefaultCategories(\PDO $pdo, int $userId): void
    {
        $q = $pdo->prepare("SELECT kind, COUNT(*) n FROM categories WHERE user_id=:uid AND is_archived=0 GROUP BY kind");
        $q->execute([':uid'=>$userId]);
        $have = [];
        foreach ($q->fetchAll() as $r) $have[$r['kind']] = (int)$r['n'];

        $toInsert = [];

        if (empty($have['expense'])) {
            $expense = ['Comida','Transporte','Servicios','Renta','Salud','Entretenimiento','Educación','Impuestos','Gastos generales'];
            $i=1; foreach ($expense as $name) $toInsert[] = ['name'=>$name, 'kind'=>'expense', 'sort'=>$i++ ];
        }
        if (empty($have['income'])) {
            $income = ['Salario','Freelance','Intereses','Otros ingresos'];
            $i=1; foreach ($income as $name) $toInsert[] = ['name'=>$name, 'kind'=>'income', 'sort'=>$i++ ];
        }
        if (empty($have['debt'])) {
            $debt = ['Créditos y deudas','Tarjeta de crédito'];
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
