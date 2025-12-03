<?php
  $my_host = "localhost";
  $my_user = "root";
  $my_pass = "";
  $my_db   = "nktn";

  $my_dsn = "mysql:host=$my_host;dbname=$my_db;charset=utf8mb4";

  try {
      $mysql = new PDO($my_dsn, $my_user, $my_pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
  } catch (PDOException $e) {
      // KHÔNG DIE
      $mysql = null;
  }
?>
