<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\CategoryRepository;

class CategoriesController extends Controller
{
    /** Listado agrupado por tipo (Gasto, Ingreso, Ahorro, Deuda) */
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/categories'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $pdo = Database::connection();

        // Mostramos activas e inactivas; activas primero
        $sql = "SELECT id, user_id, parent_id, name, kind, color_hex, is_variable, is_archived, sort_order
                FROM categories
                WHERE user_id = :uid
                ORDER BY
                  CASE WHEN is_archived = 1 THEN 2 ELSE 1 END,      -- activas primero
                  CASE kind
                    WHEN 'expense' THEN 1
                    WHEN 'income'  THEN 2
                    WHEN 'saving'  THEN 3
                    WHEN 'debt'    THEN 4
                    ELSE 5
                  END,
                  sort_order, name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll();

        // Agrupar
        $groups = ['expense'=>[], 'income'=>[], 'saving'=>[], 'debt'=>[]];
        foreach ($rows as $r) {
            $k = $r['kind'] ?? 'expense';
            $groups[$k][] = $r;
        }

        $this->view('categories/index', [
            'titulo'    => 'Categorías',
            'pageClass' => 'page-categories-index',
            'groups'    => $groups,
        ]);
    }

    /** Form crear */
    public function create()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/categories/create'); exit; }

        $this->view('categories/create', [
            'titulo'    => 'Nueva categoría',
            'pageClass' => 'page-categories-create',
        ]);
    }

    /** Guardar nueva */
    public function store()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/categories'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $name   = trim($_POST['name'] ?? '');
        $kind   = $_POST['kind'] ?? 'expense';
        $isVar  = isset($_POST['is_variable']) ? 1 : 0;

        // Color (de los swatches o “Otro…”)
        $choice = $_POST['color_choice'] ?? null;
        $color  = $_POST['color_hex'] ?? '#888888';
        if ($choice && $choice !== '__other') $color = $choice;
        if ($color && $color[0] !== '#') $color = '#'.$color;

        if ($name === '') {
            $_SESSION['flash_error'] = 'El nombre es obligatorio.';
            header('Location: /categories/create'); exit;
        }

        $pdo = Database::connection();
        $sql = "INSERT INTO categories (user_id, name, kind, color_hex, is_variable, is_archived, sort_order)
                VALUES (:uid, :name, :kind, :color, :isv, 0, 0)";
        $pdo->prepare($sql)->execute([
            ':uid'=>$userId, ':name'=>$name, ':kind'=>$kind, ':color'=>$color, ':isv'=>$isVar
        ]);

        $_SESSION['flash_success'] = 'Categoría creada.';
        header('Location: /categories'); exit;
    }

    /** Form editar */
    public function edit()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/categories'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $id     = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { header('Location: /categories'); exit; }

        $repo = new CategoryRepository();
        $category = $repo->getById($userId, $id);
        if (!$category) {
            $_SESSION['flash_error'] = 'Categoría no encontrada.';
            header('Location: /categories'); exit;
        }

        $this->view('categories/edit', [
            'titulo'    => 'Editar categoría',
            'pageClass' => 'page-categories-edit',
            'category'  => $category,
        ]);
    }

    /** Actualizar */
    public function update()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/categories'); exit; }

        $userId = (int)$_SESSION['user_id'];
        $id     = (int)($_POST['id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $kind   = $_POST['kind'] ?? 'expense';
        $isVar  = isset($_POST['is_variable']) ? 1 : 0;

        // Activa/Inactiva (is_archived)
        $isActive    = isset($_POST['is_active']) ? 1 : 0;
        $isArchived  = $isActive ? 0 : 1;

        // Color
        $choice = $_POST['color_choice'] ?? null;
        $color  = $_POST['color_hex'] ?? '#888888';
        if ($choice && $choice !== '__other') $color = $choice;
        if ($color && $color[0] !== '#') $color = '#'.$color;

        if ($id <= 0 || $name === '') {
            $_SESSION['flash_error'] = 'Datos incompletos.';
            header('Location: /categories'); exit;
        }

        // Asegurar pertenencia al usuario
        $pdo = Database::connection();
        $chk = $pdo->prepare("SELECT id FROM categories WHERE id = :id AND user_id = :uid LIMIT 1");
        $chk->execute([':id'=>$id, ':uid'=>$userId]);
        if (!$chk->fetch()) {
            $_SESSION['flash_error'] = 'No puedes editar esta categoría.';
            header('Location: /categories'); exit;
        }

        $sql = "UPDATE categories
                SET name=:name, kind=:kind, color_hex=:color, is_variable=:isv, is_archived=:ia
                WHERE id=:id AND user_id=:uid";
        $pdo->prepare($sql)->execute([
            ':name'=>$name, ':kind'=>$kind, ':color'=>$color, ':isv'=>$isVar, ':ia'=>$isArchived,
            ':id'=>$id, ':uid'=>$userId
        ]);

        $_SESSION['flash_success'] = 'Categoría actualizada.';
        header('Location: /categories'); exit;
    }
}
