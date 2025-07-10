<?php
$host = 'localhost';
$db   = 'lista_compras';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    throw new PDOException("Erro na conexão com o banco de dados: " . $e->getMessage(), (int)$e->getCode());
}
