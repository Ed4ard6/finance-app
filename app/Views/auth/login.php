<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
  <h1 class="text-2xl font-bold mb-4">Iniciar Sesión</h1>

  <?php if (!empty($errores)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <?php foreach ($errores as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/login" class="space-y-4" autocomplete="off">
    <input type="email" name="email" placeholder="Correo electrónico"
           value="<?= !empty($success) ? '' : htmlspecialchars($email ?? '') ?>"
           autocomplete="off"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">

    <input type="password" name="password" placeholder="Contraseña"
           value=""
           autocomplete="new-password"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">

    <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:opacity-90 text-white font-bold py-2 px-4 rounded">
      Entrar
    </button>
  </form>

  <p class="switch-auth mt-4 text-sm text-gray-600">
    ¿No tienes cuenta? <a href="/register" class="text-blue-600 hover:underline">Regístrate</a>
  </p>
</div>

<?php if (!empty($success)): ?>
  <script>
    (function () {
      // Limpia el form si hay modal
      const form = document.querySelector('form[action="/login"]');
      if (form) form.reset();

      // Overlay + modal (centrado)
      const overlay = document.createElement('div');
      overlay.style.cssText = [
        'position:fixed','inset:0','z-index:2147483647',
        'background:rgba(2,6,23,.65)','backdrop-filter:blur(4px)',
        'display:flex','align-items:center','justify-content:center','padding:16px'
      ].join(';');

      overlay.innerHTML = `
        <div style="
          width:100%;max-width:460px;background:#0f172a;color:#e5e7eb;
          border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,.55);
          padding:24px;text-align:center;transform:scale(.98);opacity:0;
          transition:opacity .2s ease, transform .2s ease;">
          <div style="font-size:44px;line-height:1;margin-bottom:8px;">✅</div>
          <h2 style="font-size:22px;margin:0 0 8px;">¡Registro exitoso!</h2>
          <p style="opacity:.9;margin:0 0 16px;"><?= htmlspecialchars($success) ?></p>
          <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:8px;">
            <!-- IMPORTANTE: sin ?from=register para evitar bucle -->
            <a href="/login"
               style="padding:10px 16px;border-radius:10px;background:linear-gradient(90deg,#2563eb,#7c3aed);
                      color:#fff;text-decoration:none;font-weight:600;">Ir al Login</a>
            <a href="/"
               style="padding:10px 16px;border-radius:10px;background:#334155;color:#fff;text-decoration:none;font-weight:600;">Inicio</a>
          </div>
          <div style="margin-top:4px;font-size:12px;opacity:.75;">
            Redirigiendo a Login en <span id="reg-count">10</span> s…
          </div>
        </div>`;

      document.body.appendChild(overlay);
      requestAnimationFrame(() => {
        const card = overlay.firstElementChild;
        card.style.opacity = '1';
        card.style.transform = 'scale(1)';
      });

      // Contador 10s + redirect (a /login sin parámetros)
      let s = 10;
      const span = overlay.querySelector('#reg-count');
      const timer = setInterval(() => {
        s--;
        if (span) span.textContent = s;
        if (s <= 0) {
          clearInterval(timer);
          window.location.href = '/login';
        }
      }, 1000);

      // ESC → ir a /login sin parámetros
      document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
          document.removeEventListener('keydown', escHandler);
          window.location.href = '/login';
        }
      });
    })();
  </script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
