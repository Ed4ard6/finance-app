<?php
namespace App\Core;

class Session {
  public static function start(): void {
    if (session_status() === PHP_SESSION_NONE) {
      session_set_cookie_params(['httponly'=>true,'secure'=>false,'samesite'=>'Lax']);
      session_start();
    }
  }
  public static function set(string $k, mixed $v): void { $_SESSION[$k]=$v; }
  public static function get(string $k, mixed $d=null): mixed { return $_SESSION[$k]??$d; }
  public static function forget(string $k): void { unset($_SESSION[$k]); }
  public static function destroy(): void {
    $_SESSION=[];
    if (ini_get('session.use_cookies')) {
      $p=session_get_cookie_params();
      setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
    }
    session_destroy();
  }
}
