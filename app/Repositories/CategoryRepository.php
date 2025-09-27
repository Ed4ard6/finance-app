<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class CategoryRepository
{
    public function getById(int $userId, int $id): ?array
    {
        $pdo = Database::connection();
        $sql = "SELECT * FROM categories WHERE user_id=:uid AND id=:id LIMIT 1";
        $st  = $pdo->prepare($sql);
        $st->execute([':uid'=>$userId, ':id'=>$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listByKind(int $userId, string $kind): array
    {
        $pdo = Database::connection();
        $sql = "SELECT id, name, parent_id, kind
                  FROM categories
                 WHERE user_id=:uid AND kind=:kind
              ORDER BY name ASC";
        $st = $pdo->prepare($sql);
        $st->execute([':uid'=>$userId, ':kind'=>$kind]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function detectSalaryCategory(int $userId): ?array
    {
        $pdo = Database::connection();
        $sql = "SELECT id, name, kind
                  FROM categories
                 WHERE user_id=:uid
                   AND kind='income'
                   AND (name LIKE '%salari%' OR name LIKE '%sueldo%')
              ORDER BY id ASC
                 LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([':uid'=>$userId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Crea una categoría y devuelve su id */
    public function create(int $userId, string $name, string $kind, ?int $parentId=null): int
    {
        $pdo = Database::connection();
        $sql = "INSERT INTO categories (user_id, name, kind, parent_id, created_at)
                VALUES (:uid, :name, :kind, :pid, NOW())";
        $pdo->prepare($sql)->execute([
            ':uid'=>$userId, ':name'=>$name, ':kind'=>$kind, ':pid'=>$parentId
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Garantiza que exista una categoría de salario utilizable.
     * Si no hay ninguna income, crea 'Salario'. Devuelve la fila.
     */
    public function ensureSalary(int $userId): array
    {
        $salary = $this->detectSalaryCategory($userId);

        if (!$salary) {
            $income = $this->listByKind($userId, 'income');
            if (empty($income)) {
                $id = $this->create($userId, 'Salario', 'income', null);
                $salary = ['id'=>$id, 'name'=>'Salario', 'kind'=>'income'];
            } else {
                // Si hay income pero ninguna llamada “Salario”, usa la primera como fallback
                $first  = $income[0];
                $salary = ['id'=>$first['id'], 'name'=>$first['name'], 'kind'=>'income'];
            }
        }
        return $salary;
    }
}
