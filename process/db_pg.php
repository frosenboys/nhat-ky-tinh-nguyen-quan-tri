<?php
$pg_host = "14.161.126.117";
$pg_port = "5432";
$pg_db   = "nktn";
$pg_user = "admin";
$pg_pass = "thptbinhlong123";

// KHÔNG dùng sslmode=require nếu server không bật SSL
// Nếu chưa bật SSL thì để sslmode=disable
$pg_dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_db;sslmode=disable";

try {
    $pg = new PDO($pg_dsn, $pg_user, $pg_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $pg = null;
    echo "PostgreSQL connection failed: " . $e->getMessage();
}
?>
