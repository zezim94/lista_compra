<?php
$host = 'localhost';
$db   = 'lista_compras';
$user = 'root';
$pass = '';

$host = getenv('DB_HOST') ?: 'dpg-d1k1asali9vc738uq7tg-a.oregon-postgres.render.com';
$db   = getenv('DB_NAME') ?: 'postgre_rtu3';  // seu database name
$user = getenv('DB_USER') ?: 'postgre_rtu3_user';
$pass = getenv('DB_PASS') ?: 'xjeodmeyfwpfautlcqiegjhet';
$port = getenv('DB_PORT') ?: '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define o schema padrão para lista_compra (substitui 'public')
    $pdo->exec("SET search_path TO lista_compra, public");
    
} catch (PDOException $e) {

    throw new PDOException("Erro na conexão com o banco de dados: " . $e->getMessage(), (int)$e->getCode());

    die("Erro na conexão com o banco de dados: " . $e->getMessage());

}
