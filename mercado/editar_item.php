<?php
session_start();
include('db.php');

if (isset($_GET['id'])) {
    $item_id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM itens_compras WHERE id = :id");
    $stmt->execute(['id' => $item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_item = $_POST['nome_item'];
    $quantidade = $_POST['quantidade'];
    $preco = $_POST['preco'];

    $stmt = $pdo->prepare("UPDATE itens_compras SET nome_item = :nome_item, quantidade = :quantidade, preco = :preco WHERE id = :id");
    $stmt->execute(['nome_item' => $nome_item, 'quantidade' => $quantidade, 'preco' => $preco, 'id' => $item_id]);

    // Redireciona de volta para a página de compras
    header('Location: compras.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Item</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="nome_item" class="form-label">Nome do Item</label>
                <input type="text" class="form-control" id="nome_item" name="nome_item" value="<?= $item['nome_item'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?= $item['quantidade'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="preco" class="form-label">Preço</label>
                <input type="number" step="0.01" class="form-control" id="preco" name="preco" value="<?= $item['preco'] ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>
