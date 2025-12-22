<?php
$pg_host = "";
$pg_port = "";
$pg_db   = "";
$pg_user = "";
$pg_pass = "";

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
