<?php
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');

$fmt = function($n){
  $sign = $n < 0 ? '- ' : '';
  return $sign . 'COP ' . number_format(abs((float)$n), 0, ',', '.');
};
?>
<style>
.wrap{max-width:1200px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:10px 8px;text-align:left}
.text-red{color:#fb7185}.text-green{color:#34d399}
.kpill{display:inline-flex;gap:6px;align-items:center;border-radius:9999px;padding:6px 10px;background:#0b1220;border:1px solid #334155;font-size:14px}
.btn{display:inline-flex;align-items:center;padding:8px 12px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none}
.btn:hover{background:#111827}
.btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
.actions{display:flex;gap:6px}
</style>

<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Transacciones</h1>

  <?php
    $prevYm = date('Y-m', strtotime($ym . '-01 -1 month'));
    $nextYm = date('Y-m', strtotime($ym . '-01 +1 month'));
  ?>

  <div class="actions" style="margin-bottom:12px">
    <a class="btn btn-primary" href="<?= $baseUrl ?>/transactions/create?type=expense">+ Agregar</a>
    <a class="btn" href="<?= $baseUrl ?>/categories">Categorías</a>

    <a class="btn" href="<?= $baseUrl ?>/transactions?ym=<?= $prevYm ?>&view=<?= $view ?>">«</a>
    <span class="kpill">Filtro: <?= htmlspecialchars($mesBonito) ?></span>
    <a class="btn" href="<?= $baseUrl ?>/transactions?ym=<?= $nextYm ?>&view=<?= $view ?>">»</a>

    <form method="get" style="margin-left:auto;display:flex;gap:8px">
      <input type="month" name="ym" value="<?= htmlspecialchars($ym) ?>" class="btn" style="padding:8px 10px">
      <select name="view" class="btn">
        <option value="all"     <?= $view==='all'?'selected':'' ?>>Todas</option>
        <option value="expense" <?= $view==='expense'?'selected':'' ?>>Solo egresos</option>
        <option value="income"  <?= $view==='income'?'selected':''  ?>>Solo ingresos</option>
      </select>
      <button class="btn" type="submit">Aplicar</button>
    </form>
  </div>

  <div class="actions" style="margin-bottom:12px">
    <span class="kpill">Ingresos: <b style="margin-left:6px">COP <?= number_format($sumIncome, 0, ',', '.') ?></b></span>
    <span class="kpill">Gastos: <b style="margin-left:6px">COP <?= number_format($sumExpense, 0, ',', '.') ?></b></span>
  </div>

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Categoría</th>
          <th>Tipo</th>
          <th>Monto</th>
          <th>Descripción</th>
          <th style="width:120px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6">No hay registros.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php
            $kind = $r['category_kind'] ?? '';
            $tipo = ($kind === 'income') ? 'Ingreso'
                  : (($kind === 'expense') ? 'Gasto'
                  : (($kind === 'debt') ? 'Deuda'
                  : (($kind === 'saving') ? 'Ahorro' : '—')));
            $clsAmount = ((float)$r['amount'] < 0) ? 'text-red' : 'text-green';
          ?>
          <tr>
            <td><?= htmlspecialchars($r['date_at']) ?></td>
            <td><?= htmlspecialchars($r['category_name'] ?? '—') ?></td>
            <td><?= $tipo ?></td>
            <td class="<?= $clsAmount ?>"><?= $fmt($r['amount']) ?></td>
            <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
            <td>
              <div class="actions">
                <a class="btn" href="<?= $baseUrl ?>/transactions/edit?id=<?= (int)$r['id'] ?>">Editar</a>
                <form action="<?= $baseUrl ?>/transactions/delete" method="post" onsubmit="return confirm('¿Eliminar transacción?');">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="btn" type="submit">Borrar</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

