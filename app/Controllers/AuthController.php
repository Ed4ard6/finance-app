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
        $errores = [];
        $success = null;
        $nombre  = null;
        $email   = null;

        require __DIR__ . '/../Views/auth/register.php';
    }

    /** POST /register */
    public function register()
    {
        $errores = [];
        $success = null;
        $nombre  = $_POST['nombre'] ?? null;
        $email   = $_POST['email'] ?? null;

        $validator = new Validator($_POST, [
            'nombre'   => 'required|min:3|max:100',
            'email'    => 'required|email|max:150',
            'password' => 'required|min:6|max:255',
            'confirm'  => 'required|same:password',
        ]);

        if ($validator->passes()) {
            try {
                $pdo = Database::connection();

                // Verificar que no exista el email antes
                $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = :e");
                $check->execute([':e' => $email]);
                if ($check->fetch()) {
                    $errores[] = "El correo ya está registrado.";
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO usuarios (nombre, email, password_hash) 
                         VALUES (:n, :e, :p)"
                    );
                    $stmt->execute([
                        ':n' => $nombre,
                        ':e' => $email,
                        ':p' => password_hash($_POST['password'], PASSWORD_BCRYPT),
                    ]);

                    $success = "✅ Usuario registrado con éxito.";
                }
            } catch (\PDOException $e) {
                $errores[] = "Error al registrar el usuario: " . $e->getMessage();
            }
        } else {
            foreach ($validator->errors() as $msgs) {
                foreach ($msgs as $msg) {
                    $errores[] = $msg;
                }
            }
        }

        require __DIR__ . '/../Views/auth/register.php';
    }

    /** GET /login */
    public function showLogin()
    {
        $errores = [];
        $success = null;
        $email   = null;

        require __DIR__ . '/../Views/auth/login.php';
    }

    /** POST /login */
    public function login()
    {
        $errores = [];
        $success = null;
        $email   = $_POST['email'] ?? null;

        $validator = new Validator($_POST, [
            'email'    => 'required|email|max:150',
            'password' => 'required|min:6|max:255',
        ]);

        if ($validator->passes()) {
            try {
                $pdo = Database::connection();

                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :e");
                $stmt->execute([':e' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || !password_verify($_POST['password'], $user['password_hash'])) {
                    $errores[] = "Credenciales incorrectas.";
                } else {
                    // Iniciar sesión
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];

                    // Redirigir al dashboard
                    header('Location: /dashboard');
                    exit;
                }
            } catch (\PDOException $e) {
                $errores[] = "Error al iniciar sesión: " . $e->getMessage();
            }
        } else {
            foreach ($validator->errors() as $msgs) {
                foreach ($msgs as $msg) {
                    $errores[] = $msg;
                }
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    /** GET /logout */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: /login');
        exit;
    }
}
