<?php
  // Activa el fondo claro exclusivo para el dashboard
  $pageClass = 'page-dashboard';
  require __DIR__ . '/../layouts/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-6">
  <!-- Encabezado -->
  <div class="page-header mb-6">
    <h1 class="page-header__title">Dashboard</h1>
    <p class="page-header__subtitle">
      Hola, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>. Este es el resumen de tus finanzas.
    </p>
  </div>

  <!-- KPIs -->
  <div class="grid grid-3 mb-6">
    <div class="card">
      <div class="kpi__title">Balance</div>
      <div class="kpi__value <?= ($balance ?? 0) >= 0 ? 'kpi__value--pos' : 'kpi__value--neg' ?>">
        <?= number_format($balance ?? 0, 0, ',', '.') ?> COP
      </div>
      <div class="text-muted fs-85 mt-2">Ingresos - Gastos</div>
    </div>

    <div class="card">
      <div class="kpi__title">Ingresos</div>
      <div class="kpi__value kpi__value--pos">
        <?= number_format($ingresos ?? 0, 0, ',', '.') ?> COP
      </div>
      <a href="/ingresos/crear" class="btn btn--primary mt-2">Agregar ingreso</a>
    </div>

    <div class="card">
      <div class="kpi__title">Gastos</div>
      <div class="kpi__value kpi__value--neg">
        <?= number_format($gastos ?? 0, 0, ',', '.') ?> COP
      </div>
      <a href="/gastos/crear" class="btn btn--dark mt-2">Agregar gasto</a>
    </div>
  </div>

  <!-- Metas -->
  <section class="card mb-6">
    <div class="block-head">
      <div class="block-head__title">Metas</div>
      <div class="block-head__actions">
        <a href="/metas" class="btn btn--ghost">Ver todas</a>
        <a href="/metas/crear" class="btn btn--primary">Nueva meta</a>
      </div>
    </div>

    <?php if (!empty($goals)): ?>
      <div class="grid">
        <?php foreach ($goals as $g):
          $target = (float)($g['target_amount'] ?? 0);
          $saved  = (float)($g['saved_amount']  ?? 0);
          $pct    = $target > 0 ? min(100, round(($saved / $target) * 100)) : 0;
        ?>
          <div class="card">
            <div class="fw-600"><?= htmlspecialchars($g['name'] ?? 'Meta') ?></div>
            <div class="text-muted fs-90" style="margin-bottom:.5rem;">
              <?= number_format($saved, 0, ',', '.') ?> / <?= number_format($target, 0, ',', '.') ?> COP
            </div>
            <div class="progress">
              <div class="progress__bar" style="width: <?= $pct ?>%"></div>
            </div>
            <div class="text-muted fs-85 mt-2"><?= $pct ?>% completado</div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="page-header__subtitle">
        Aún no tienes metas activas. <a href="/metas/crear">Crea tu primera meta</a>.
      </p>
    <?php endif; ?>
  </section>

  <!-- Últimos movimientos -->
  <section class="card">
    <div class="block-head">
      <div class="block-head__title">Últimos movimientos</div>
      <div class="block-head__actions">
        <a class="btn btn--dark" href="/movimientos">Ver todo</a>
        <a class="btn btn--primary" href="/movimientos/crear">Nuevo</a>
      </div>
    </div>

    <?php if (!empty($movs)): ?>
      <div>
        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Tipo</th>
              <th class="table__num">Monto</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($movs as $m): ?>
              <tr>
                <td><?= htmlspecialchars(isset($m['fecha']) ? date('Y-m-d', strtotime($m['fecha'])) : '') ?></td>
                <td><?= htmlspecialchars($m['concepto'] ?? '') ?></td>
                <td>
                  <?php if (($m['tipo'] ?? '') === 'income'): ?>
                    <span class="badge badge--income">Ingreso</span>
                  <?php else: ?>
                    <span class="badge badge--expense">Gasto</span>
                  <?php endif; ?>
                </td>
                <td class="table__num">
                  <?php
                    $sign  = (($m['tipo'] ?? '') === 'income') ? '+' : '-';
                    $monto = (float)($m['monto'] ?? 0);
                    echo $sign . number_format($monto, 0, ',', '.');
                  ?> COP
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="page-header__subtitle">
        Sin movimientos recientes. Registra tu primer <a href="/movimientos/crear">movimiento</a>.
      </p>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
