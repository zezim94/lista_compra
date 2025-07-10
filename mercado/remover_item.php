<?php
session_start();
include('db.php');

if (isset($_GET['id'])) {
    $item_id = (int) $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM itens_compras WHERE id = :id");
    $stmt->execute(['id' => $item_id]);

    // Redireciona de volta para a página de compras
    header('Location: compras.php');
    exit;
}

