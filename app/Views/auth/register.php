<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
  <h1>Registro</h1>

  <?php if (!empty($errores)): ?>
    <div class="error-box">
      <?php foreach ($errores as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="success-box">
      <p><?= htmlspecialchars($success) ?></p>
    </div>
  <?php endif; ?>

  <form method="POST" action="/register" class="space-y-4">
    <input type="text" name="nombre" placeholder="Nombre completo"
           value="<?= htmlspecialchars($nombre ?? '') ?>" class="input">
    <input type="email" name="email" placeholder="Correo electrónico"
           value="<?= htmlspecialchars($email ?? '') ?>" class="input">
    <input type="password" name="password" placeholder="Contraseña" class="input">
    <input type="password" name="confirm" placeholder="Confirmar contraseña" class="input">
    <button type="submit" class="btn-primary">Registrarme</button>
  </form>

  <p class="switch-auth">
    ¿Ya tienes cuenta? <a href="/login">Inicia sesión</a>
  </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
