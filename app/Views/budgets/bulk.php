<?php
// Espera: $titulo, $pageClass, $rows, $ym, $mesBonito, $ymPrev, $ymNext
require BASE_PATH . '/app/Views/layouts/header.php';

$fmt = fn($n) => $n === null ? '' : number_format((float)$n, 0, ',', '.');

$kLabel = function(string $k) {
  return $k === 'expense' ? 'Gasto' : ($k === 'saving' ? 'Ahorro' : 'Deuda');
};
?>
<style>
.wrap{max-width:1100px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:8px;background:#111827;border:1px solid #334155}
a.badge{color:#93c5fd;text-decoration:none}
a.badge:hover{background:#0b1220;border-color:#3b82f6}
table.tbl{width:100%;border-collapse:collapse}
.tbl th,.tbl td{padding:10px;border-bottom:1px solid #1f2937}
.tbl th{color:#9ca3af;text-align:left;font-weight:600}
.kind{font-size:.85rem;color:#93a3af}
.input{width:140px;padding:6px 8px;background:#0b1220;border:1px solid #334155;border-radius:8px;color:#e5e7eb}
.input:focus{outline:none;border-color:#3b82f6}
.actions{display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:12px}
.btn{padding:8px 12px;border:1px solid #334155;border-radius:8px;background:#111827;color:#e5e7eb;text-decoration:none;cursor:pointer}
.btn.primary{background:#2563eb;border-color:#1d4ed8}
.btn:hover{filter:brightness(1.05)}
.note{color:#93a3af}
</style>

<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Editar presupuestos — <?= htmlspecialchars($mesBonito) ?></h1>

  <div class="row" style="margin-bottom:12px; gap:10px;">
    <span class="badge">Mes: <?= htmlspecialchars($mesBonito) ?></span>
    <a class="badge" href="/budgets/bulk?ym=<?= $ymPrev ?>">← Mes anterior</a>
    <a class="badge" href="/budgets/bulk?ym=<?= $ymNext ?>">Mes siguiente →</a>
    <a class="badge" href="/budgets?ym=<?= $ym ?>">Volver a vista</a>
  </div>

  <form method="post" action="/budgets/bulk">
    <input type="hidden" name="ym" value="<?= htmlspecialchars($ym) ?>">

    <div class="card">
      <?php if (empty($rows)): ?>
        <p class="note">No hay categorías activas para este usuario.</p>
      <?php else: ?>
        <table class="tbl" aria-label="Edición de presupuestos por categoría">
          <thead>
            <tr>
              <th style="width:50%">Categoría</th>
              <th style="width:20%">Tipo</th>
              <th style="width:30%">Presupuesto (COP)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['category_name']) ?></td>
                <td class="kind"><?= htmlspecialchars($kLabel($r['kind'])) ?></td>
                <td>
                  <input
                    class="input"
                    type="text"
                    inputmode="numeric"
                    name="amount[<?= (int)$r['category_id'] ?>]"
                    value="<?= htmlspecialchars($fmt($r['budget_amount'])) ?>"
                    placeholder="ej. 200.000">
                  <span class="note">vacío o 0 = borrar</span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="actions">
          <a class="btn" href="/budgets?ym=<?= $ym ?>">Cancelar</a>
          <button class="btn primary" type="submit">Guardar cambios</button>
        </div>
      <?php endif; ?>
    </div>
  </form>

  <p class="note" style="margin-top:12px">
    Tip: puedes escribir con puntos o comas (ej. <em>350.000</em> o <em>350,000</em>); se guardan como enteros de pesos.
  </p>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
