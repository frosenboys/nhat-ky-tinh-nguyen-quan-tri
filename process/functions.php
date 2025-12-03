<?php
  function alert($type, $message) {
    echo json_encode([
        "status" => $type,
        "message" => $message
    ]);
    exit();
  }

  function fetchUser(){
    global $mysql;
    $id = $_SESSION["user"]["id"];
    $stmt = $mysql->prepare("SELECT id, fullName, email, adminLevel, createdAt FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(["id" => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
  }
?>