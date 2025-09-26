<?php
$titulo='Calendario / Heatmap'; $pageClass='page-calendar';
require BASE_PATH.'/app/Views/layouts/header.php';

// mock: gastos por día (1..30)
$gastos = [1=>0,2=>35000,3=>0,4=>12000,5=>520000,6=>0,7=>0,8=>40000,9=>0,10=>0,11=>80000,12=>50000,13=>0,14=>0,15=>150000,16=>0,17=>0,18=>30000,19=>0,20=>42000,21=>0,22=>0,23=>0,24=>0,25=>0,26=>0,27=>0,28=>0,29=>0,30=>0];
$max = max($gastos) ?: 1;
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
?>
<style>
.wrap{max-width:900px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.grid{display:grid;grid-template-columns:repeat(7,1fr);gap:8px}
.cell{height:64px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:600}
.legend{display:flex;gap:8px;align-items:center}
.box{width:20px;height:20px;border-radius:6px}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Calendario / Heatmap</h1>
  <div class="card">
    <div class="legend" style="margin-bottom:10px">
      <span class="box" style="background:#0b1220;border:1px solid #1f2937"></span> 0
      <span class="box" style="background:#1e293b"></span> bajo
      <span class="box" style="background:#3b82f6"></span> medio
      <span class="box" style="background:#60a5fa"></span> alto
    </div>
    <div class="grid">
      <?php for($d=1;$d<=30;$d++):
        $v = $gastos[$d]??0;
        $p = $v/$max;
        $bg = $p==0? '#0b1220' : ($p<.33?'#1e293b' : ($p<.66?'#3b82f6':'#60a5fa'));
      ?>
        <div class="cell" title="<?=$fmt($v)?>" style="background:<?=$bg?>;border:1px solid #1f2937"><?=$d?></div>
      <?php endfor ?>
    </div>
    <p style="margin-top:10px;color:#9ca3af">Pasa el mouse para ver el gasto del día.</p>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
