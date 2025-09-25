<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller {
  public function showLogin(): void { $this->view('auth/login'); }
  public function showRegister(): void { $this->view('auth/register'); }

  public function register(): void {
    $nombre=trim($_POST['nombre']??'');
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $confirm=$_POST['confirm']??'';
    $errores=[];
    if($nombre===''||$email===''||$password==='') $errores[]='Todos los campos son obligatorios';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errores[]='Email inválido';
    if($password!==$confirm) $errores[]='Las contraseñas no coinciden';
    if(User::findByEmail($email)) $errores[]='El email ya está registrado';
    if($errores){ $this->view('auth/register',compact('errores','nombre','email')); return; }
    $id=User::create($nombre,$email,$password);
    Session::set('user',['id'=>$id,'nombre'=>$nombre,'email'=>$email]);
    $base=(require BASE_PATH.'/app/Config/config.php')['base_url'];
    header('Location: '.$base.'/'); exit;
  }

  public function login(): void {
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $user=User::findByEmail($email);
    if(!$user || !password_verify($password,$user['password_hash'])){
      $errores=['Credenciales inválidas'];
      $this->view('auth/login',compact('errores','email')); return;
    }
    Session::set('user',['id'=>$user['id'],'nombre'=>$user['nombre'],'email'=>$user['email']]);
    $base=(require BASE_PATH.'/app/Config/config.php')['base_url'];
    header('Location: '.$base.'/'); exit;
  }

  public function logout(): void {
    Session::destroy();
    $base=(require BASE_PATH.'/app/Config/config.php')['base_url'];
    header('Location: '.$base.'/login'); exit;
  }
}
