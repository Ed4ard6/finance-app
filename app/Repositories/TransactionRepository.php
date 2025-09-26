<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class TransactionRepository
{
    /** Devuelve KPIs del mes (ingresos, egresos, balance) */
    public function getMonthlyKpis(int $userId, string $ym): array
    {
        $pdo = Database::connection(); // << usa tu Database actual

        $sql = "
        SELECT
          SUM(CASE WHEN t.`amount`>0 THEN t.`amount` ELSE 0 END) AS ingresos,
          -SUM(CASE WHEN t.`amount`<0 THEN t.`amount` ELSE 0 END) AS egresos,
          SUM(t.`amount`) AS balance
        FROM `transactions` t
        WHERE t.`user_id`=:uid
          AND t.`status`=1          -- soft delete activo
          AND DATE_FORMAT(t.`date_at`,'%Y-%m')=:ym
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid'=>$userId, ':ym'=>$ym]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'ingresos' => (float)($row['ingresos'] ?? 0),
            'egresos'  => (float)($row['egresos']  ?? 0),
            'balance'  => (float)($row['balance']  ?? 0),
        ];
    }

    /** Distribución por categoría padre (solo gastos/ahorros/deudas) */
    public function getExpenseDistributionByParent(int $userId, string $ym): array
    {
        $pdo = Database::connection();

        $sql = "
        SELECT
          CASE WHEN cp.`id` IS NULL THEN c.`name` ELSE cp.`name` END AS categoria_padre,
          SUM(CASE WHEN t.`amount` < 0 THEN -t.`amount` ELSE 0 END) AS total
        FROM `transactions` t
        JOIN `categories` c       ON c.`id` = t.`category_id`
        LEFT JOIN `categories` cp ON cp.`id` = c.`parent_id`
        WHERE t.`user_id` = :uid
          AND t.`status`=1
          AND c.`kind` IN ('expense','saving','debt')
          AND DATE_FORMAT(t.`date_at`,'%Y-%m') = :ym
        GROUP BY
          CASE WHEN cp.`id` IS NULL THEN c.`id`   ELSE cp.`id`   END,
          CASE WHEN cp.`id` IS NULL THEN c.`name` ELSE cp.`name` END
        ORDER BY total DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid'=>$userId, ':ym'=>$ym]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Intenta detectar la categoría de salario del usuario:
     * - prioridad: una categoría de kind='income' cuyo nombre LIKE '%salari%'
     * - si no existe, devuelve null y el form pedirá el ID.
     */
    public function getSalaryCategoryId(int $userId): ?int
    {
        $pdo = Database::connection();

        $sql = "
        SELECT c.`id`
        FROM `categories` c
        WHERE c.`user_id`=:uid
          AND c.`kind`='income'
          AND (c.`name` LIKE '%salari%' OR c.`name` LIKE '%sueldo%')
        ORDER BY c.`id` ASC
        LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid'=>$userId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    /** ¿Ya hay salario del mes? */
    public function hasSalaryForMonth(int $userId, string $ym, ?int $salaryCategoryId = null): bool
    {
        $pdo = Database::connection();

        $whereCat = $salaryCategoryId ? "AND t.`category_id`=:catId" : "";
        $sql = "
        SELECT 1
        FROM `transactions` t
        JOIN `categories` c ON c.`id`=t.`category_id`
        WHERE t.`user_id`=:uid
          AND t.`status`=1
          AND t.`amount`>0
          $whereCat
          AND c.`kind`='income'
          AND DATE_FORMAT(t.`date_at`,'%Y-%m')=:ym
        LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $params = [':uid'=>$userId, ':ym'=>$ym];
        if ($salaryCategoryId) { $params[':catId'] = $salaryCategoryId; }
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Crea o actualiza el salario del mes.
     * Si existe, actualiza; si no, inserta. Usa soft delete en tu tabla (status=1).
     */
    public function upsertMonthlySalary(int $userId, string $ym, float $amount, int $categoryId): void
    {
        $pdo = Database::connection();

        $sqlFind = "
        SELECT t.`id`
        FROM `transactions` t
        WHERE t.`user_id`=:uid
          AND t.`status`=1
          AND t.`category_id`=:cat
          AND t.`amount`>0
          AND DATE_FORMAT(t.`date_at`,'%Y-%m')=:ym
        LIMIT 1";
        $stmt = $pdo->prepare($sqlFind);
        $stmt->execute([':uid'=>$userId, ':cat'=>$categoryId, ':ym'=>$ym]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $sqlUpd = "UPDATE `transactions`
                       SET `amount`=:amt, `updated_at`=NOW()
                       WHERE `id`=:id AND `user_id`=:uid";
            $pdo->prepare($sqlUpd)->execute([
                ':amt'=>$amount, ':id'=>$id, ':uid'=>$userId
            ]);
        } else {
            // Usa día 01 del mes; puedes ajustar a la fecha real de pago
            $sqlIns = "INSERT INTO `transactions`
                      (`user_id`,`category_id`,`amount`,`date_at`,`description`,`status`,`created_at`)
                      VALUES (:uid,:cat,:amt,STR_TO_DATE(CONCAT(:ym,'-01'),'%Y-%m-%d'),
                              'Salario',1,NOW())";
            $pdo->prepare($sqlIns)->execute([
                ':uid'=>$userId, ':cat'=>$categoryId, ':amt'=>$amount, ':ym'=>$ym
            ]);
        }
    }

    /** Soft delete genérico (NO borra, solo desactiva) */
    public function softDelete(int $id, int $userId): void
    {
        $pdo = Database::connection();

        $sql = "UPDATE `transactions`
                SET `status`=0, `deleted_at`=NOW()
                WHERE `id`=:id AND `user_id`=:uid AND `status`=1";
        $pdo->prepare($sql)->execute([':id'=>$id, ':uid'=>$userId]);
    }
}
