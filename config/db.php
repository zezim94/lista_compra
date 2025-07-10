<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'lista_compra';
$user = getenv('DB_USER') ?: 'postgre_rtu3_user';
$pass = getenv('DB_PASS') ?: 'WpvaJVjpVuMfD3PsjXQsljewH28jsnMl';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Mostre erro (em produção, grave em log!)
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
