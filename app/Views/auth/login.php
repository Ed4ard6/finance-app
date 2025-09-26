<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
  <h1>Iniciar Sesión</h1>

  <?php if (!empty($errores)): ?>
    <div class="error-box">
      <?php foreach ($errores as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/login" class="space-y-4">
    <input type="email" name="email" placeholder="Correo electrónico"
           value="<?= htmlspecialchars($email ?? '') ?>" class="input">
    <input type="password" name="password" placeholder="Contraseña" class="input">
    <button type="submit" class="btn-primary">Entrar</button>
  </form>

  <p class="switch-auth">
    ¿No tienes cuenta? <a href="/register">Regístrate</a>
  </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
