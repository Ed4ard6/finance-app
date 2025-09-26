<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class SalaryRepository
{
    /** Último salario del usuario (por created_at) */
    public function latest(int $userId): ?array
    {
        $pdo = Database::connection();
        $sql = "SELECT id, amount, category_id, effective_from, status, created_at
                  FROM salaries
                 WHERE user_id=:uid AND status=1
              ORDER BY created_at DESC
                 LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([':uid'=>$userId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Inserta un nuevo registro de salario (histórico) */
    public function insert(int $userId, float $amount, ?int $categoryId=null, ?string $effectiveFrom=null): int
    {
        $pdo = Database::connection();
        $sql = "INSERT INTO salaries (user_id, amount, category_id, effective_from, status, created_at)
                VALUES (:uid, :amt, :cat, :efrom, 1, NOW())";
        $pdo->prepare($sql)->execute([
            ':uid'=>$userId,
            ':amt'=>$amount,
            ':cat'=>$categoryId,
            ':efrom'=>$effectiveFrom
        ]);
        return (int)$pdo->lastInsertId();
    }
}
