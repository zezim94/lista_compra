<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['usuario_id']) || !isset($_GET['compra_id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Requisição inválida']);
    exit;
}

$compra_id = $_GET['compra_id'];
$usuario_id = $_SESSION['usuario_id'];

// Verifica se a compra pertence ao usuário
$stmt = $pdo->prepare("
    SELECT * FROM compras 
    WHERE id = :compra_id AND usuario_id = :usuario_id
");
$stmt->execute([
    'compra_id' => $compra_id,
    'usuario_id' => $usuario_id
]);
$compra = $stmt->fetch();

if (!$compra) {
    http_response_code(403);
    echo json_encode(['erro' => 'Compra não encontrada']);
    exit;
}

// Recupera os itens da compra
$stmt = $pdo->prepare("
    SELECT nome_item, quantidade, preco 
    FROM itens_compras 
    WHERE compra_id = :compra_id
");
$stmt->execute(['compra_id' => $compra_id]);
$itens = $stmt->fetchAll();

foreach ($itens as $item) {
    $_SESSION['itens_temp'][] = [
        'nome_item' => $item['nome_item'],
        'quantidade' => $item['quantidade'],
        'preco' => $item['preco'],
        'mercado_id' => $compra['mercado_id'],
        'lista_id' => $compra['lista_compra_id']
    ];
}

echo json_encode(['sucesso' => true]);
exit;
?>
