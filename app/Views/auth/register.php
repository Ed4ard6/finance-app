<h2>Registro</h2>
<?php if(!empty($errores)): ?><ul style="color:red;"><?php foreach($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul><?php endif; ?>
<form method="post" action="<?= $baseUrl ?>/register">
  <label>Nombre</label><input type="text" name="nombre" value="<?= htmlspecialchars($nombre ?? '') ?>" required>
  <label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  <label>Contraseña</label><input type="password" name="password" required>
  <label>Confirmar contraseña</label><input type="password" name="confirm" required>
  <button type="submit">Crear cuenta</button>
</form>
<p>¿Ya tienes cuenta? <a href="<?= $baseUrl ?>/login">Inicia sesión</a></p>
