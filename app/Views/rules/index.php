<?php
$titulo='Reglas'; $pageClass='page-rules';
require BASE_PATH.'/app/Views/layouts/header.php';
$rules=[
  ['nombre'=>'Factura Claro','monto'=>42000,'freq'=>'Mensual','dia'=>'5'],
  ['nombre'=>'Internet','monto'=>50000,'freq'=>'Mensual','dia'=>'12'],
  ['nombre'=>'Ahorro 10%','monto'=>null,'freq'=>'Por quincena','dia'=>'H1/H2'],
];
$fmt = fn($n)=> $n===null?'-':"COP ".number_format($n,0,',','.');
?>
<style>
.wrap{max-width:1000px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:8px;text-align:left}
.btn{display:inline-flex;align-items:center;padding:6px 10px;border:1px solid #334155;border-radius:8px;background:#0b1220;color:#e5e7eb;text-decoration:none}
.btn:hover{background:#111827}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Reglas (recurrencias)</h1>
  <div class="card">
    <div style="margin-bottom:10px"><a class="btn" href="#">+ Nueva regla</a></div>
    <table class="table">
      <thead><tr><th>Nombre</th><th>Monto</th><th>Frecuencia</th><th>Día</th></tr></thead>
      <tbody>
        <?php foreach($rules as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['nombre'])?></td>
            <td><?=$fmt($r['monto'])?></td>
            <td><?=$r['freq']?></td>
            <td><?=$r['dia']?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
