<?php
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');

$titulo    = $titulo    ?? 'Categorías';
$pageClass = $pageClass ?? 'page-categories-index';

$labels = [
  'expense' => 'Gastos',
  'income'  => 'Ingresos',
  'saving'  => 'Ahorro',
  'debt'    => 'Deudas',
];
?>
<style>
  .page-categories-index .wrap{max-width:1100px;margin:0 auto}
  .page-categories-index h1{font-size:26px;margin:12px 0;color:#e5e7eb}
  .page-categories-index .btn{
    display:inline-flex;align-items:center;justify-content:center;
    padding:8px 12px;border-radius:8px;border:1px solid #334155;
    background:#0b1220;color:#e5e7eb;text-decoration:none
  }
  .page-categories-index .btn:hover{background:#111827}
  .page-categories-index .actions-top{display:flex;gap:8px;margin-bottom:12px}
  .page-categories-index .card{
    background:#0f172a;border:1px solid #1f2937;border-radius:12px;
    padding:16px;color:#e5e7eb;margin-bottom:18px
  }
  .page-categories-index .card h2{margin:0 0 10px;font-size:20px}

  .page-categories-index .table .head,
  .page-categories-index .table .row{
    display:grid;grid-template-columns: 1fr 120px 120px;
    gap:10px;align-items:center;border-bottom:1px solid #1f2937;padding:10px 8px;
  }
  .page-categories-index .table .head{color:#93a5be}
  .page-categories-index .estado{justify-self:end}
  .page-categories-index .acciones{justify-self:end;display:flex;gap:8px}
  .page-categories-index .estado.activa{color:#a7f3d0}
  .page-categories-index .estado.inactiva{color:#fca5a5}

  .page-categories-index .pill{
    display:inline-flex;align-items:center;gap:8px;
    padding:6px 10px;border-radius:9999px;background:#111827;
    border:1px solid #334155;color:#cbd5e1;text-decoration:none
  }
  .page-categories-index .pill.inactiva{opacity:.55}
  .page-categories-index .dot{width:10px;height:10px;border-radius:9999px;background:var(--c,#888)}
</style>

<div class="wrap">
  <h1>Categorías</h1>
  <div class="actions-top">
    <a class="btn" href="<?= $baseUrl ?>/categories/create">+ Nueva categoría</a>
  </div>

  <?php foreach (['expense','income','saving','debt'] as $kind): ?>
    <div class="card">
      <h2><?= $labels[$kind] ?></h2>

      <div class="table">
        <div class="head">
          <div>Nombre</div>
          <div class="estado">Estado</div>
          <div class="acciones">Acciones</div>
        </div>

        <?php if (empty($groups[$kind])): ?>
          <div class="row">
            <div style="color:#93a5be">— Sin categorías —</div>
            <div></div><div></div>
          </div>
        <?php else: foreach ($groups[$kind] as $c):
              $inactive = !empty($c['is_archived']);
        ?>
          <div class="row">
            <div>
              <span class="pill <?= $inactive?'inactiva':'' ?>">
                <span class="dot" style="--c:<?= htmlspecialchars($c['color_hex']) ?>"></span>
                <?= htmlspecialchars($c['name']) ?>
              </span>
            </div>
            <div class="estado <?= $inactive?'inactiva':'activa' ?>">
              <?= $inactive ? 'Inactiva' : 'Activa' ?>
            </div>
            <div class="acciones">
              <a class="btn" href="<?= $baseUrl ?>/categories/edit?id=<?= (int)$c['id'] ?>">Editar</a>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
