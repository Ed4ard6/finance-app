<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use PDO;

class AuthController extends Controller
{
    /** GET /register */
    public function showRegister()
    {
        // Si ya está logueado → al dashboard
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!empty($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $errores = [];
        $success = null; // el modal vive en /login
        $nombre  = null;
        $email   = null;

        require __DIR__ . '/../Views/auth/register.php';
    }

    /** POST /register */
    public function register()
    {
        // Si ya está logueado → al dashboard (bloquea registro estando autenticado)
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!empty($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $errores = [];
        $success = null;
        $nombre  = $_POST['nombre'] ?? '';
        $email   = $_POST['email']  ?? '';
        $pass    = $_POST['password'] ?? '';
        $confirm = $_POST['confirm']  ?? '';

        $validator = new Validator($_POST, [
            'nombre'   => 'required|min:3|max:100',
            'email'    => 'required|email|max:150',
            'password' => 'required|min:6|max:255',
            'confirm'  => 'required',
        ]);

        if (!$validator->passes()) {
            foreach ($validator->errors() as $msgs) {
                foreach ($msgs as $msg) $errores[] = $msg;
            }
        }
        if ($pass !== $confirm) {
            $errores[] = 'La confirmación no coincide con la contraseña.';
        }

        if (empty($errores)) {
            try {
                $pdo = Database::connection();

                // Verificar duplicado
                $check = $pdo->prepare("SELECT id FROM users WHERE email = :e");
                $check->execute([':e' => $email]);

                if ($check->fetch()) {
                    $errores[] = "El correo ya está registrado.";
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO users (nombre, email, password_hash)
                         VALUES (:n, :e, :p)"
                    );
                    $stmt->execute([
                        ':n' => $nombre,
                        ':e' => $email,
                        ':p' => password_hash($pass, PASSWORD_BCRYPT),
                    ]);

                    $_SESSION['success'] = "✅ Usuario registrado con éxito.";
                    header('Location: /login');
                    exit;
                }
            } catch (\Throwable $e) {
                $errores[] = "Error al registrar el usuario: " . $e->getMessage();
            }
        }

        require __DIR__ . '/../Views/auth/register.php';
    }

    /** GET /login */
    public function showLogin()
    {
        // Si ya está logueado → al dashboard
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!empty($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $errores = [];
        $success = null;
        $email   = null;

        // Flash de éxito tras registro
        if (!empty($_SESSION['success'])) {
            $success = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        // Evitar BFCache
        header('Cache-Control: no-store, no-cache, must-revalidate');

        require __DIR__ . '/../Views/auth/login.php';
    }

    /** POST /login */
    public function login()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $errores = [];
        $success = null;
        $email   = $_POST['email'] ?? '';

        $validator = new Validator($_POST, [
            'email'    => 'required|email|max:150',
            'password' => 'required|min:6|max:255',
        ]);

        if ($validator->passes()) {
            try {
                $pdo = Database::connection();

                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :e");
                $stmt->execute([':e' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || !password_verify($_POST['password'], $user['password_hash'])) {
                    $errores[] = "Credenciales incorrectas.";
                } else {
                    $_SESSION['user_id']   = (int) $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_email']= $user['email'] ?? null;

                    header('Location: /dashboard');
                    exit;
                }
            } catch (\Throwable $e) {
                $errores[] = "Error al iniciar sesión: " . $e->getMessage();
            }
        } else {
            foreach ($validator->errors() as $msgs) {
                foreach ($msgs as $msg) $errores[] = $msg;
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    /** GET|POST /logout */
    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // Vaciar y destruir sesión
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        header('Location: /login');
        exit;
    }
}
