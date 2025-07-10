<?php
session_start();
include('db.php');

// Verifique se o mercado_id foi passado
if (isset($_GET['mercado_id'])) {
    $mercado_id = $_GET['mercado_id'];

    // Consultar as listas de compras para o mercado selecionado
    $stmt = $pdo->prepare("SELECT id, nome FROM listas_compras WHERE mercado_id = :mercado_id AND usuario_id = :usuario_id");
    $stmt->execute(['mercado_id' => $mercado_id, 'usuario_id' => $_SESSION['usuario_id']]);
    $listas = $stmt->fetchAll();

    // Retornar as listas no formato JSON
    echo json_encode($listas);
} else {
    echo json_encode([]);
}
?>
