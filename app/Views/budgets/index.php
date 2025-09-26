<?php
$titulo='Presupuestos'; $pageClass='page-budgets';
require BASE_PATH.'/app/Views/layouts/header.php';

$cats=[
  ['nombre'=>'Comida','pres'=>400000,'gasto'=>260000],
  ['nombre'=>'Servicios','pres'=>220000,'gasto'=>180000],
  ['nombre'=>'Ahorro','pres'=>250000,'gasto'=>150000],
  ['nombre'=>'Otros','pres'=>80000,'gasto'=>40000],
];
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
$w = function($used,$pres){ return max(2,min(100,round(($used/max(1,$pres))*100))); };
?>
<style>
.wrap{max-width:1100px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.row{display:flex;gap:8px;flex-wrap:wrap}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;background:#111827;border:1px solid #334155}
.bar{height:10px;width:100%;background:#1f2937;border-radius:8px;overflow:hidden}
.fill{height:100%;background:#60a5fa}
.semaforo{padding:2px 8px;border-radius:999px;border:1px solid #334155}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Presupuestos del mes</h1>
  <div class="row" style="margin-bottom:12px">
    <span class="badge">Mes: Septiembre 2025</span>
    <a class="badge" href="#">Editar en bloque</a>
  </div>
  <div class="card">
    <?php foreach($cats as $c):
      $pct=$w($c['gasto'],$c['pres']);
      $estado = $pct<80?'#34d399':($pct<=100?'#f59e0b':'#fb7185');
    ?>
      <div style="display:grid;grid-template-columns:160px 1fr 160px 160px;gap:12px;align-items:center;margin:10px 0">
        <div><?=$c['nombre']?></div>
        <div class="bar"><div class="fill" style="width:<?=$pct?>%"></div></div>
        <div>Presupuesto: <?=$fmt($c['pres'])?></div>
        <div>Usado: <span style="color:<?=$estado?>"><?=$fmt($c['gasto'])?> (<?=$pct?>%)</span></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
