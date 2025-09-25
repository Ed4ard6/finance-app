<?php
namespace App\Core;

class Auth {
  public static function check(): bool { return (bool)Session::get('user'); }
  public static function requireLogin(): void {
    if(!self::check()){
      $base=(require BASE_PATH.'/app/Config/config.php')['base_url'];
      header('Location: '.$base.'/login'); exit;
    }
  }
}
