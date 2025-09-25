<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
  public static function findByEmail(string $email): ?array {
    $pdo=Database::connection();
    $st=$pdo->prepare('SELECT * FROM usuarios WHERE email=? LIMIT 1');
    $st->execute([$email]);
    $row=$st->fetch(PDO::FETCH_ASSOC);
    return $row?:null;
  }
  public static function create(string $nombre,string $email,string $password): int {
    $hash=password_hash($password,PASSWORD_BCRYPT);
    $pdo=Database::connection();
    $st=$pdo->prepare('INSERT INTO usuarios (nombre,email,password_hash) VALUES (?,?,?)');
    $st->execute([$nombre,strtolower(trim($email)),$hash]);
    return (int)$pdo->lastInsertId();
  }
}
