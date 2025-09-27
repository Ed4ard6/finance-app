<?php
// Espera: $cat, $txs, $mesBonito, $rangeName
$fmt = fn($n) => 'COP ' . number_format((int)$n, 0, ',', '.');
?>
<div>
  <h3 style="margin:6px 0 10px 0">Transacciones — <?= htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($mesBonito) ?>, <?= htmlspecialchars($rangeName) ?>)</h3>
  <?php if (empty($txs)): ?>
    <p style="color:#9ca3af">No hay transacciones registradas en este período.</p>
  <?php else: ?>
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #1f2937;color:#9ca3af">Fecha</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #1f2937;color:#9ca3af">Descripción</th>
          <th style="text-align:right;padding:8px;border-bottom:1px solid #1f2937;color:#9ca3af">Monto</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($txs as $t): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #1f2937"><?= htmlspecialchars($t['date_at']) ?></td>
            <td style="padding:8px;border-bottom:1px solid #1f2937"><?= htmlspecialchars($t['description'] ?: '-') ?></td>
            <td style="padding:8px;border-bottom:1px solid #1f2937;text-align:right">
              <?= $fmt($t['amount']) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
