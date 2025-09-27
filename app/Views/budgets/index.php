<?php
// Espera: $titulo, $pageClass, $rows, $ym, $mesBonito, $ymPrev, $ymNext, $range,
//         $totalBudget, $totalUsed, $totalAvail
require BASE_PATH . '/app/Views/layouts/header.php';

$fmt = fn($n) => 'COP ' . number_format((int)$n, 0, ',', '.');
$badge = 'display:inline-flex;align-items:center;padding:6px 10px;border-radius:8px;background:#111827;border:1px solid #334155;color:#cbd5e1;text-decoration:none;';
?>
<style>
.wrap{max-width:1100px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.bar{height:10px;width:100%;background:#1f2937;border-radius:8px;overflow:hidden}
.fill{height:100%}
.tbl{width:100%;border-collapse:collapse}
.tbl th,.tbl td{padding:10px;border-bottom:1px solid #1f2937}
.tbl th{color:#9ca3af;text-align:left}
.k{font-size:.85rem;color:#93a3af}
.right{display:flex;gap:10px;align-items:center}
.btn{padding:8px 12px;border:1px solid #334155;border-radius:8px;background:#111827;color:#e5e7eb;text-decoration:none;cursor:pointer}
.btn.primary{background:#2563eb;border-color:#1d4ed8}
.btn:hover{filter:brightness(1.05)}
.totals{display:flex;gap:16px;flex-wrap:wrap}
.totals .pill{background:#111827;border:1px solid #334155;border-radius:8px;padding:8px 10px}
a.link{color:#93c5fd;text-decoration:none}
a.link:hover{text-decoration:underline}
.modal{position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:2000}
.modal.open{display:flex}
.modal .box{background:#0b1220;border:1px solid #1f2937;border-radius:12px;max-width:800px;width:92%;max-height:80vh;overflow:auto}
.modal header{padding:10px 14px;border-bottom:1px solid #1f2937;display:flex;justify-content:space-between;align-items:center}
.modal .content{padding:10px 14px}
.modal .close{background:transparent;border:none;color:#e5e7eb;font-size:20px;cursor:pointer}
</style>

<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Presupuestos del mes</h1>

  <div class="row" style="margin-bottom:12px">
    <span style="<?= $badge ?>">Mes: <?= htmlspecialchars($mesBonito) ?></span>
    <a style="<?= $badge ?>" href="<?= $baseUrl ?>/budgets?ym=<?= $ymPrev ?>&range=<?= urlencode($range) ?>">← Mes anterior</a>
    <a style="<?= $badge ?>" href="<?= $baseUrl ?>/budgets?ym=<?= $ymNext ?>&range=<?= urlencode($range) ?>">Mes siguiente →</a>

    <!-- Filtro de período -->
    <span style="<?= $badge ?>">Período:
      <a class="link" href="<?= $baseUrl ?>/budgets?ym=<?= $ym ?>&range=m"   style="margin-left:6px;<?= $range==='m'?'text-decoration:underline':'' ?>">Mes</a> ·
      <a class="link" href="<?= $baseUrl ?>/budgets?ym=<?= $ym ?>&range=h1"  style="<?= $range==='h1'?'text-decoration:underline':'' ?>">01–15</a> ·
      <a class="link" href="<?= $baseUrl ?>/budgets?ym=<?= $ym ?>&range=h2"  style="<?= $range==='h2'?'text-decoration:underline':'' ?>">16–fin</a>
    </span>

    <a style="<?= $badge ?>" href="<?= $baseUrl ?>/budgets/bulk?ym=<?= $ym ?>">Editar en bloque</a>

    <!-- Copiar del mes anterior -->
    <form method="post" action="<?= $baseUrl ?>/budgets/copy-prev" class="right" style="margin-left:auto">
      <input type="hidden" name="ym" value="<?= htmlspecialchars($ym) ?>">
      <button class="btn" type="submit" title="Copiar presupuestos del mes anterior">Copiar del mes anterior</button>
    </form>
  </div>

  <!-- Totales -->
  <div class="totals" style="margin-bottom:12px">
    <div class="pill">Presupuesto total: <strong><?= $fmt($totalBudget) ?></strong></div>
    <div class="pill">Usado total: <strong><?= $fmt($totalUsed) ?></strong></div>
    <div class="pill">Disponible total: <strong><?= $fmt($totalAvail) ?></strong></div>
  </div>

  <div class="card">
    <table class="tbl" aria-label="Presupuestos">
      <thead>
        <tr>
          <th style="width:28%">Categoría</th>
          <th style="width:10%">Tipo</th>
          <th style="width:26%">Barra</th>
          <th style="width:12%">Presupuesto</th>
          <th style="width:12%">Usado</th>
          <th style="width:12%">Disponible</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r):
          $pres = (int)($r['budget_amount'] ?? 0);
          $used = (int)($r['used_amount'] ?? 0);
          $avail = $pres - $used;
          $pct = $pres > 0 ? max(0, min(100, round(($used / $pres) * 100))) : 0;
          $color = $pct < 80 ? '#34d399' : ($pct <= 100 ? '#f59e0b' : '#fb7185');
        ?>
          <tr>
            <td><?= htmlspecialchars($r['category_name']) ?></td>
            <td class="k">
              <?= $r['kind'] === 'expense' ? 'Gasto' : ($r['kind'] === 'saving' ? 'Ahorro' : 'Deuda') ?>
            </td>
            <td>
              <div class="bar"><div class="fill" style="width:<?= $pct ?>%; background: <?= $color ?>"></div></div>
            </td>
            <td><?= $fmt($pres) ?></td>
            <td>
              <?php if ($used > 0): ?>
                <a href="#"
                   class="link js-detail"
                   data-cid="<?= (int)$r['category_id'] ?>"
                   data-ym="<?= htmlspecialchars($ym) ?>"
                   data-range="<?= htmlspecialchars($range) ?>"
                   title="Ver detalle de transacciones">
                  <?= $fmt($used) ?> (<?= $pct ?>%)
                </a>
              <?php else: ?>
                <?= $fmt(0) ?> (0%)
              <?php endif; ?>
            </td>
            <td style="color:<?= $avail >= 0 ? '#93c5fd':'#fb7185' ?>"><?= $fmt($avail) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal drill-down -->
<div id="modal" class="modal" aria-modal="true" role="dialog">
  <div class="box">
    <header>
      <strong id="modal-title">Detalle</strong>
      <button class="close" aria-label="Cerrar" onclick="closeModal()">×</button>
    </header>
    <div class="content" id="modal-content">
      Cargando…
    </div>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('modal');
  const title = document.getElementById('modal-title');
  const content = document.getElementById('modal-content');

  function openModal(){ modal.classList.add('open'); }
  window.closeModal = function(){ modal.classList.remove('open'); }

  document.addEventListener('click', async (e)=>{
    const a = e.target.closest('.js-detail');
    if (!a) return;
    e.preventDefault();
    const cid = a.dataset.cid, ym = a.dataset.ym, range = a.dataset.range;
    title.textContent = 'Detalle';
    content.textContent = 'Cargando…';
    openModal();
    try {
      const res = await fetch('<?= $baseUrl ?>/budgets/detail?ym='+encodeURIComponent(ym)+'&range='+encodeURIComponent(range)+'&cid='+encodeURIComponent(cid));
      content.innerHTML = await res.text();
    } catch(err){
      content.textContent = 'No se pudo cargar el detalle.';
    }
  });

  // Cerrar al hacer click fuera de la caja
  modal.addEventListener('click', (e)=>{ if (e.target === modal) closeModal(); });
})();
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
