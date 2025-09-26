<?php
use App\Core\Session;
$config = require BASE_PATH . '/app/Config/config.php';
$baseUrl = $config['base_url'];
$user = Session::get('user');
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $titulo ?? 'Finanzas' ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/styles.css">
  <link rel="icon" href="https://eduardomachacon.com/assets/img/favicon.png">
  <img src="https://eduardomachacon.com/assets/img/logo_1.png" alt="Logo">
</head>

<body>
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
        <a href="<?= $baseUrl ?>/">Dashboard</a>
        <a href="<?= $baseUrl ?>/logout">Cerrar Sesión</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
