<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class BudgetRepository
{
    /**
     * Lista categorías (expense/saving/debt) con:
     *  - presupuesto del mes (budgets.amount)
     *  - monto usado en el rango [start,end)
     */
    public function listWithUsage(int $userId, string $ym, string $start, string $end): array
    {
        $pdo = Database::connection();

        $sql = "
        SELECT
            c.`id`            AS category_id,
            c.`name`          AS category_name,
            c.`kind`,
            b.`amount`        AS budget_amount,
            COALESCE(SUM(CASE WHEN t.`amount` < 0 THEN -t.`amount` ELSE 0 END), 0) AS used_amount
        FROM `categories` c
        LEFT JOIN `budgets` b
               ON b.`user_id`     = c.`user_id`
              AND b.`category_id` = c.`id`
              AND b.`year_month`  = :ym
        LEFT JOIN `transactions` t
               ON t.`user_id`     = :uid_tx
              AND t.`category_id` = c.`id`
              AND t.`status`      = 1
              AND t.`deleted_at`  IS NULL
              AND t.`date_at`    >= :start
              AND t.`date_at`    <  :end
        WHERE c.`user_id`     = :uid_cat
          AND c.`is_archived` = 0
          AND c.`kind` IN ('expense','saving','debt')
        GROUP BY c.`id`, c.`name`, c.`kind`, b.`amount`
        ORDER BY c.`name` ASC";

        $st = $pdo->prepare($sql);
        $st->execute([
            ':ym'      => $ym,
            ':uid_tx'  => $userId,
            ':start'   => $start,
            ':end'     => $end,
            ':uid_cat' => $userId,
        ]);

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function hasBudgetsForMonth(int $userId, string $ym): bool
    {
        $pdo = Database::connection();
        $st  = $pdo->prepare("SELECT COUNT(*) FROM `budgets` WHERE `user_id`=:u AND `year_month`=:ym");
        $st->execute([':u'=>$userId, ':ym'=>$ym]);
        return ((int)$st->fetchColumn()) > 0;
    }

    /** Categorías para edición en bloque */
    public function listForBulk(int $userId, string $ym): array
    {
        $pdo = Database::connection();

        $sql = "
        SELECT
            c.`id`   AS category_id,
            c.`name` AS category_name,
            c.`kind`,
            b.`amount` AS budget_amount
        FROM `categories` c
        LEFT JOIN `budgets` b
               ON b.`user_id`     = c.`user_id`
              AND b.`category_id` = c.`id`
              AND b.`year_month`  = :ym
        WHERE c.`user_id` = :uid
          AND c.`is_archived` = 0
          AND c.`kind` IN ('expense','saving','debt')
        ORDER BY c.`name` ASC";

        $st = $pdo->prepare($sql);
        $st->execute([':uid' => $userId, ':ym' => $ym]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Upsert/borrado en bloque */
    public function saveBulk(int $userId, string $ym, array $toUpsert, array $toDelete): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $sqlDel = "DELETE FROM `budgets`
                           WHERE `user_id`=? AND `year_month`=? AND `category_id` IN ($in)";
                $params = array_merge([$userId, $ym], array_map('intval', $toDelete));
                $pdo->prepare($sqlDel)->execute($params);
            }

            if (!empty($toUpsert)) {
                $sqlIns = "INSERT INTO `budgets`
                           (`user_id`,`year_month`,`category_id`,`amount`)
                           VALUES (:uid, :ym, :cid, :amt)
                           ON DUPLICATE KEY UPDATE `amount` = :amt_dup";
                $stmt = $pdo->prepare($sqlIns);

                foreach ($toUpsert as [$cid, $amt]) {
                    $stmt->execute([
                        ':uid'     => $userId,
                        ':ym'      => $ym,
                        ':cid'     => (int)$cid,
                        ':amt'     => (int)$amt,
                        ':amt_dup' => (int)$amt,
                    ]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Copia presupuestos del mes anterior hacia $ym (upsert uno a uno) */
    public function copyFromPreviousMonth(int $userId, string $ym): void
    {
        $pdo = Database::connection();
        $srcYm = date('Y-m', strtotime($ym . '-01 -1 month'));

        $st = $pdo->prepare("SELECT `category_id`, `amount`
                               FROM `budgets`
                              WHERE `user_id`=:u AND `year_month`=:ym");
        $st->execute([':u'=>$userId, ':ym'=>$srcYm]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($rows)) return;

        $pdo->beginTransaction();
        try {
            $sql = "INSERT INTO `budgets` (`user_id`,`year_month`,`category_id`,`amount`)
                    VALUES (:u,:ym,:c,:a)
                    ON DUPLICATE KEY UPDATE `amount`=:a2";
            $ins = $pdo->prepare($sql);

            foreach ($rows as $r) {
                $ins->execute([
                    ':u'  => $userId,
                    ':ym' => $ym,
                    ':c'  => (int)$r['category_id'],
                    ':a'  => (int)$r['amount'],
                    ':a2' => (int)$r['amount'],
                ]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Info de una categoría del usuario */
    public function getCategory(int $userId, int $categoryId): ?array
    {
        $pdo = Database::connection();
        $st = $pdo->prepare("SELECT `id`,`name`,`kind` FROM `categories`
                             WHERE `user_id`=:u AND `id`=:c LIMIT 1");
        $st->execute([':u'=>$userId, ':c'=>$categoryId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Transacciones por categoría en rango [start,end) */
    public function transactionsByCategoryInRange(int $userId, int $categoryId, string $start, string $end): array
    {
        $pdo = Database::connection();
        $sql = "SELECT `id`,`date_at`,`description`,`amount`
                  FROM `transactions`
                 WHERE `user_id`=:u
                   AND `category_id`=:c
                   AND `status`=1
                   AND `deleted_at` IS NULL
                   AND `date_at`>=:s AND `date_at`<:e
              ORDER BY `date_at` DESC, `id` DESC";
        $st = $pdo->prepare($sql);
        $st->execute([':u'=>$userId, ':c'=>$categoryId, ':s'=>$start, ':e'=>$end]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
