<?php
$titulo='Ahorros y metas'; $pageClass='page-savings';
require BASE_PATH.'/app/Views/layouts/header.php';

$meta = ['nombre'=>'Fondo de emergencia','objetivo'=>8000000,'ahorrado'=>1250000,'fecha'=>'2025-03-01'];
$porc = max(0,min(100,round(($meta['ahorrado']/max(1,$meta['objetivo']))*100)));
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
?>
<style>
.wrap{max-width:1000px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.donut{position:relative;width:140px;height:140px;border-radius:9999px;background:conic-gradient(#34d399 calc(var(--p,20)*1%),#0b1220 0)}
.donut::before{content:"";position:absolute;inset:12px;background:#0b1220;border-radius:9999px;box-shadow:inset 0 0 0 1px #111827}
.donut b{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#34d399;font-size:20px}
.row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;background:#111827;border:1px solid #334155}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Ahorros y metas</h1>
  <div class="card">
    <div class="row">
      <div class="donut" style="--p:<?=$porc?>"><b><?=$porc?>%</b></div>
      <div>
        <h3 style="margin:0 0 6px 0"><?=htmlspecialchars($meta['nombre'])?></h3>
        <div class="badge">Objetivo: <?=$fmt($meta['objetivo'])?></div>
        <div class="badge">Ahorrado: <?=$fmt($meta['ahorrado'])?></div>
        <div class="badge">Fecha objetivo: <?=date('m/Y',strtotime($meta['fecha']))?></div>
      </div>
    </div>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
