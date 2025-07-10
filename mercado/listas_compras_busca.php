<?php
session_start();
include('../config/db.php');

if (isset($_GET['mercado_id'])) {
    $mercado_id = (int) $_GET['mercado_id'];
    $stmt = $pdo->prepare("SELECT * FROM listas_compras WHERE usuario_id = :usuario_id AND mercado_id = :mercado_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id'], 'mercado_id' => $mercado_id]);
    $listas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($listas);
} else {
    echo json_encode([]);
}
?>
