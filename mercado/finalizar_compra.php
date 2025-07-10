<?php
session_start();
include('db.php');

if (!isset($_SESSION['usuario_id']) || !isset($_POST['finalizar']) || empty($_SESSION['itens_temp'])) {
    header("Location: compras.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$itens = $_SESSION['itens_temp'];
$lista_id = $itens[0]['lista_id'];
$mercado_id = $itens[0]['mercado_id'];

// 1. Criar uma nova compra com data_compra agora e total 0 (temporário)
$stmt = $pdo->prepare("
    INSERT INTO compras (usuario_id, lista_compra_id, mercado_id, data_compra, total)
    VALUES (:usuario_id, :lista_id, :mercado_id, NOW(), 0)
");
$stmt->execute([
    'usuario_id' => $usuario_id,
    'lista_id' => $lista_id,
    'mercado_id' => $mercado_id
]);
$compra_id = $pdo->lastInsertId();

// 2. Inserir os itens
foreach ($itens as $item) {
    $stmt = $pdo->prepare("
        INSERT INTO itens_compras (compra_id, nome_item, quantidade, preco, data)
        VALUES (:compra_id, :nome_item, :quantidade, :preco, NOW())
    ");
    $stmt->execute([
        'compra_id' => $compra_id,
        'nome_item' => $item['nome_item'],
        'quantidade' => $item['quantidade'],
        'preco' => $item['preco']
    ]);
}

// 3. Calcular o total da compra somando quantidade * preco dos itens inseridos
$stmt = $pdo->prepare("
    SELECT SUM(quantidade * preco) AS total_compra
    FROM itens_compras
    WHERE compra_id = :compra_id
");
$stmt->execute(['compra_id' => $compra_id]);
$total_compra = $stmt->fetchColumn();

// 4. Atualizar o total na tabela compras
$stmt = $pdo->prepare("UPDATE compras SET total = :total WHERE id = :compra_id");
$stmt->execute([
    'total' => $total_compra,
    'compra_id' => $compra_id
]);

// 5. Limpar a sessão de itens temporários
unset($_SESSION['itens_temp']);

// 6. Redirecionar para ver a compra
header("Location: compras_historico.php?compra_id=$compra_id");
exit;
?>