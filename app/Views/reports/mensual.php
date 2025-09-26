<?php
$titulo='Reporte mensual'; $pageClass='page-report-monthly';
require BASE_PATH.'/app/Views/layouts/header.php';

$ingresos=1600000; $gastos=1420000; $ahorro=180000;
$topUp=['Comida'=>-300000,'Servicios'=>-180000,'Transporte'=>-120000];
$deltaMes=-80000; // gastaste 80k más que mes anterior
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
?>
<style>
.wrap{max-width:900px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
p{color:#cbd5e1}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;background:#111827;border:1px solid #334155;margin-right:6px}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Reporte mensual (narrado)</h1>
  <div class="card">
    <p>
      En <b>septiembre</b> tus ingresos fueron <?=$fmt($ingresos)?> y tus gastos <?=$fmt($gastos)?>, dejando un
      ahorro de <?=$fmt($ahorro)?> (tasa ≈ <?=round(($ahorro/max(1,$ingresos))*100)?>%).
      Gastaste <?= $deltaMes>=0 ? 'más' : 'menos' ?> que agosto por <?=$fmt(abs($deltaMes))?>.
    </p>
    <p>
      Las categorías con mayor impacto fueron:
      <?php foreach($topUp as $k=>$v): ?>
        <span class="badge"><?=$k?>: <?=$fmt($v)?></span>
      <?php endforeach; ?>
    </p>
    <p>
      Manteniendo esta tendencia, alcanzarías tu meta de ahorro en <b>3 semanas</b>.
      Subir tu regla al <b>12%</b> adelantaría <b>~10 días</b>.
    </p>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
