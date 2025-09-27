<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class TransactionRepository
{
    public function getMonthlyKpis(int $userId, string $ym): array
    {
        $pdo = Database::connection();
        $sql = "SELECT
                  SUM(CASE WHEN t.amount>0 THEN t.amount ELSE 0 END) AS ingresos,
                  -SUM(CASE WHEN t.amount<0 THEN t.amount ELSE 0 END) AS egresos,
                  SUM(t.amount) AS balance
                FROM transactions t
               WHERE t.user_id=:uid AND t.status=1
                 AND DATE_FORMAT(t.date_at,'%Y-%m')=:ym";
        $st = $pdo->prepare($sql);
        $st->execute([':uid'=>$userId, ':ym'=>$ym]);
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'ingresos' => (float)($row['ingresos'] ?? 0),
            'egresos'  => (float)($row['egresos']  ?? 0),
            'balance'  => (float)($row['balance']  ?? 0),
        ];
    }

    public function getExpenseDistributionByParent(int $userId, string $ym): array
    {
        $pdo = Database::connection();
        $sql = "SELECT
                  CASE WHEN cp.id IS NULL THEN c.name ELSE cp.name END AS categoria_padre,
                  SUM(CASE WHEN t.amount<0 THEN -t.amount ELSE 0 END) AS total
                FROM transactions t
                JOIN categories c ON c.id=t.category_id
           LEFT JOIN categories cp ON cp.id=c.parent_id
               WHERE t.user_id=:uid AND t.status=1
                 AND c.kind IN ('expense','saving','debt')
                 AND DATE_FORMAT(t.date_at,'%Y-%m')=:ym
            GROUP BY
                  CASE WHEN cp.id IS NULL THEN c.id ELSE cp.id END,
                  CASE WHEN cp.id IS NULL THEN c.name ELSE cp.name END
            ORDER BY total DESC";
        $st = $pdo->prepare($sql);
        $st->execute([':uid'=>$userId, ':ym'=>$ym]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Crea/actualiza la transacción de salario del mes a partir del salario actual */
    public function ensureMonthlySalaryTransaction(int $userId, string $ym, float $amount, int $categoryId): void
    {
        $pdo = Database::connection();

        $find = "SELECT id FROM transactions
                  WHERE user_id=:uid AND status=1
                    AND category_id=:cat AND amount>0
                    AND DATE_FORMAT(date_at,'%Y-%m')=:ym
                  LIMIT 1";
        $st = $pdo->prepare($find);
        $st->execute([':uid'=>$userId, ':cat'=>$categoryId, ':ym'=>$ym]);
        $id = $st->fetchColumn();

        if ($id) {
            // ¡Sin updated_at!
            $upd = "UPDATE transactions
                       SET amount=:amt, description='Salario'
                     WHERE id=:id AND user_id=:uid";
            $pdo->prepare($upd)->execute([':amt'=>$amount, ':id'=>$id, ':uid'=>$userId]);
        } else {
            $ins = "INSERT INTO transactions
                   (user_id, category_id, amount, date_at, description, status, created_at)
                   VALUES (:uid,:cat,:amt,STR_TO_DATE(CONCAT(:ym,'-01'),'%Y-%m-%d'),
                           'Salario',1,NOW())";
            $pdo->prepare($ins)->execute([
                ':uid'=>$userId, ':cat'=>$categoryId, ':amt'=>$amount, ':ym'=>$ym
            ]);
        }
    }

    /** Crear movimiento genérico (lo dejamos para otros formularios) */
    public function create(int $userId, int $categoryId, float $amount, string $dateAt, string $description=''): int
    {
        $pdo = Database::connection();
        $sql = "INSERT INTO transactions
                (user_id, category_id, amount, date_at, description, status, created_at)
                VALUES (:uid,:cat,:amt,:date_at,:descr,1,NOW())";
        $pdo->prepare($sql)->execute([
            ':uid'=>$userId, ':cat'=>$categoryId, ':amt'=>$amount,
            ':date_at'=>$dateAt, ':descr'=>$description
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function softDelete(int $id, int $userId): void
    {
        $pdo = Database::connection();
        // ¡Sin deleted_at!
        $sql = "UPDATE transactions
                   SET status=0
                 WHERE id=:id AND user_id=:uid AND status=1";
        $pdo->prepare($sql)->execute([':id'=>$id, ':uid'=>$userId]);
    }
}
