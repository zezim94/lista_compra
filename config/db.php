<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'lista_compra';
$user = getenv('DB_USER') ?: 'seu_usuario';
$pass = getenv('DB_PASS') ?: 'sua_senha';
$port = getenv('DB_PORT') ?: '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em produção, logue isso!
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
