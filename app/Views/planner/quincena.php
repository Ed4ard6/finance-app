<?php
$titulo='Quincena Planner'; $pageClass='page-planner';
require BASE_PATH.'/app/Views/layouts/header.php';

$ingresoH1 = 750000; $ingresoH2 = 750000;
$pagos = [
  'H1'=>[
    ['nombre'=>'Factura Claro','monto'=>42000],
    ['nombre'=>'Internet','monto'=>50000],
    ['nombre'=>'Comida','monto'=>200000],
  ],
  'H2'=>[
    ['nombre'=>'Cuota auto','monto'=>520000],
    ['nombre'=>'Ahorro 10%','monto'=>150000],
  ],
];
$sum = fn($arr)=> array_sum(array_map(fn($x)=>$x['monto'],$arr));
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
$restoH1 = $ingresoH1 - $sum($pagos['H1']);
$restoH2 = $ingresoH2 - $sum($pagos['H2']);
?>
<style>
.wrap{max-width:1000px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:8px;text-align:left}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;background:#111827;border:1px solid #334155}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Quincena Planner</h1>
  <div class="grid">
    <div class="card">
      <h3>1ª quincena</h3>
      <div class="badge">Ingreso: <?=$fmt($ingresoH1)?></div>
      <table class="table" style="margin-top:8px">
        <thead><tr><th>Pago</th><th>Monto</th></tr></thead>
        <tbody>
          <?php foreach($pagos['H1'] as $p): ?>
            <tr><td><?=$p['nombre']?></td><td><?=$fmt($p['monto'])?></td></tr>
          <?php endforeach ?>
          <tr><td><b>Te queda</b></td><td><b><?=$fmt($restoH1)?></b></td></tr>
        </tbody>
      </table>
    </div>
    <div class="card">
      <h3>2ª quincena</h3>
      <div class="badge">Ingreso: <?=$fmt($ingresoH2)?></div>
      <table class="table" style="margin-top:8px">
        <thead><tr><th>Pago</th><th>Monto</th></tr></thead>
        <tbody>
          <?php foreach($pagos['H2'] as $p): ?>
            <tr><td><?=$p['nombre']?></td><td><?=$fmt($p['monto'])?></td></tr>
          <?php endforeach ?>
          <tr><td><b>Te queda</b></td><td><b><?=$fmt($restoH2)?></b></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
