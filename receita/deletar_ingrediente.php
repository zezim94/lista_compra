<?php session_start(); include('db.php');
if (!isset($_SESSION['usuario_id'])) header('Location: login.php');
$id = $_GET['id'] ?? null; $rid = $_GET['rid'] ?? null;
$pdo->prepare("DELETE ir FROM ingredientes_receita ir JOIN receitas r ON ir.receita_id=r.id WHERE ir.id=:id AND r.usuario_id=:u")
    ->execute(['id'=>$id,'u'=>$_SESSION['usuario_id']]);
header("Location: detalhes_receita.php?id=".$rid);
