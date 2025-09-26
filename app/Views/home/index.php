<?php
  // Página pública. Si estás usando la lógica de redirección en HomeController,
  // un usuario autenticado no alcanzará a ver esta vista (lo manda a /dashboard).
  // Para los no autenticados, mostramos la landing con tu header y estilos.
  require __DIR__ . '/../layouts/header.php';
?>

<div class="landing">
  <h1>Bienvenido a Finanzas</h1>
  <p>
    Administra tus ingresos, gastos y metas de forma sencilla.<br>
    Una aplicación hecha para llevar el control de tus finanzas personales.
  </p>

  <?php
    // Si usas $baseUrl en tus rutas públicas, lo traemos del config ya cargado en header.
    // Si prefieres rutas absolutas, puedes dejar /login y /register directamente.
    $loginUrl    = ($baseUrl ?? '') . '/login';
    $registerUrl = ($baseUrl ?? '') . '/register';
  ?>
  <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">Iniciar Sesión</a>
  <a href="<?= htmlspecialchars($registerUrl) ?>" class="btn">Registrarse</a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
