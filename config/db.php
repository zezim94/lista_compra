<?php
$host = getenv('DB_HOST') ?: 'dpg-d1k1asali9vc738uq7tg-a.oregon-postgres.render.com';
$db   = getenv('DB_NAME') ?: 'postgre_rtu3';  // seu database name
$user = getenv('DB_USER') ?: 'postgre_rtu3_user';
$pass = getenv('DB_PASS') ?: 'xjeodmeyfwpfautlcqiegjhet';
$port = getenv('DB_PORT') ?: '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define o schema padrão para lista_compra (substitui 'public')
    $pdo->exec("SET search_path TO lista_compra, public");
    
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
