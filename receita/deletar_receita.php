<?php session_start(); include('db.php');
if (!isset($_SESSION['usuario_id'])) header('Location: login.php');
$id = $_GET['id'] ?? null;
$pdo->prepare("DELETE r, ir FROM receitas r LEFT JOIN ingredientes_receita ir ON ir.receita_id=r.id WHERE r.id=:id AND r.usuario_id=:u")
    ->execute(['id'=>$id,'u'=>$_SESSION['usuario_id']]);
header("Location: listar_receitas.php");
