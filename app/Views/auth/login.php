<h2>Iniciar sesión</h2>
<?php if(!empty($errores)): ?><ul style="color:red;"><?php foreach($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul><?php endif; ?>
<form method="post" action="<?= $baseUrl ?>/login">
  <label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  <label>Contraseña</label><input type="password" name="password" required>
  <button type="submit">Entrar</button>
</form>
<p>¿No tienes cuenta? <a href="<?= $baseUrl ?>/register">Regístrate</a></p>
