<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
  <h1 class="text-2xl font-bold mb-4">Registro</h1>

  <?php if (!empty($errores)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <?php foreach ($errores as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/register" class="space-y-4" autocomplete="off">
    <input type="text" name="nombre" placeholder="Nombre"
           value="<?= htmlspecialchars($nombre ?? '') ?>"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">

    <input type="email" name="email" placeholder="Correo electrónico"
           value="<?= htmlspecialchars($email ?? '') ?>"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">

    <input type="password" name="password" placeholder="Contraseña"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">

    <input type="password" name="confirm" placeholder="Confirmar contraseña"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">

    <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:opacity-90 text-white font-bold py-2 px-4 rounded">
      Registrarme
    </button>
  </form>

  <p class="switch-auth mt-4 text-sm text-gray-600">
    ¿Ya tienes cuenta? <a href="/login" class="text-blue-600 hover:underline">Inicia sesión</a>
  </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
