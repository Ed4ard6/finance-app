<?php
use App\Core\Session;

$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = $config['base_url'];

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

/**
 * Consideramos autenticado si existe user_id en la sesión.
 * Si usas tu wrapper Session::get('user'), lo mantenemos como preferencia.
 */
$user = Session::get('user');
if (!$user && !empty($_SESSION['user_id'])) {
    $user = [
        'id'     => (int) $_SESSION['user_id'],
        'nombre' => $_SESSION['user_name'] ?? 'Usuario',
        'email'  => $_SESSION['user_email'] ?? null,
    ];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $titulo ?? 'Finanzas' ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/styles.css">
  <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/img/favicon.png">
</head>

<body class="<?= $pageClass ?? '' ?>">
  <header class="main-header">
    <div class="logo">
      <a href="<?= $baseUrl ?>/">
        <h2>Finanzas</h2>
      </a>
    </div>

    <nav class="nav">
      <?php if (!$user): ?>
        <!-- Menú público -->
        <a href="<?= $baseUrl ?>/login">Iniciar Sesión</a>
        <a href="<?= $baseUrl ?>/register">Registrarse</a>
        <a href="<?= $baseUrl ?>/demo">Demo</a>
      <?php else: ?>
        <!-- Menú privado -->
        <a href="<?= $baseUrl ?>/dashboard">Dashboard</a>
        <a href="<?= $baseUrl ?>/logout">Cerrar Sesión</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
