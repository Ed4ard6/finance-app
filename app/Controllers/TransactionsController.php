<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\TransactionRepository;
use App\Repositories\CategoryRepository;

class TransactionsController extends Controller
{
    public function store()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/dashboard'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $catId  = (int)($_POST['category_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $dateAt = $_POST['date_at'] ?? date('Y-m-d');
        $descr  = trim($_POST['description'] ?? '');

        if ($catId <= 0 || $amount == 0) {
            $_SESSION['flash_error'] = 'Selecciona categoría y monto válidos.';
            header('Location: /dashboard'); exit;
        }

        $catRepo  = new CategoryRepository();
        $category = $catRepo->getById($userId, $catId);
        if (!$category) {
            $_SESSION['flash_error'] = 'Categoría no válida.';
            header('Location: /dashboard'); exit;
        }

        // Ajusta el signo: income +, expense/saving/debt -
        if ($category['kind'] !== 'income' && $amount > 0) $amount = -$amount;

        (new TransactionRepository())->create($userId, $catId, $amount, $dateAt, $descr);

        $_SESSION['flash_success'] = 'Transacción creada.';
        header('Location: /dashboard'); exit;
    }
}
