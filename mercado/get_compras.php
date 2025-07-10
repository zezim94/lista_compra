<?php
session_start();
include('db.php');

$lista_id_1 = isset($_GET['lista_id_1']) ? (int) $_GET['lista_id_1'] : null;
$lista_id_2 = isset($_GET['lista_id_2']) ? (int) $_GET['lista_id_2'] : null;

if (!$lista_id_1 || !$lista_id_2) {
    die('Erro: IDs das listas não fornecidos.');
}

// Buscar itens da lista 1
$stmt_lista_1 = $pdo->prepare("SELECT nome_item, preco FROM itens_lista WHERE lista_id = :lista_id");
$stmt_lista_1->execute(['lista_id' => $lista_id_1]);
$itens_lista_1 = $stmt_lista_1->fetchAll(PDO::FETCH_ASSOC);

// Buscar itens da lista 2
$stmt_lista_2 = $pdo->prepare("SELECT nome_item, preco FROM itens_lista WHERE lista_id = :lista_id");
$stmt_lista_2->execute(['lista_id' => $lista_id_2]);
$itens_lista_2 = $stmt_lista_2->fetchAll(PDO::FETCH_ASSOC);

// Combinar os itens das duas listas
$compras = [];
foreach ($itens_lista_1 as $item1) {
    $compras[$item1['nome_item']] = [
        'nome_item' => $item1['nome_item'],
        'preco_lista_1' => $item1['preco'],
        'preco_lista_2' => null
    ];
}

foreach ($itens_lista_2 as $item2) {
    if (isset($compras[$item2['nome_item']])) {
        $compras[$item2['nome_item']]['preco_lista_2'] = $item2['preco'];
    } else {
        $compras[$item2['nome_item']] = [
            'nome_item' => $item2['nome_item'],
            'preco_lista_1' => null,
            'preco_lista_2' => $item2['preco']
        ];
    }
}

// Retornar os dados em JSON
header('Content-Type: application/json');
echo json_encode(array_values($compras));
?>
