<?php
session_start();
include('db.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$compra_id = $_GET['compra_id'] ?? null;
if (!$compra_id) {
    echo "Compra não encontrada.";
    exit;
}

// Buscar dados da compra
$stmt = $pdo->prepare("
    SELECT c.id, c.data_compra, m.nome AS mercado, l.nome AS lista
    FROM compras c
    JOIN mercados m ON c.mercado_id = m.id
    JOIN listas_compras l ON c.lista_compra_id = l.id
    WHERE c.id = :compra_id AND c.usuario_id = :usuario_id
");
$stmt->execute([
    'compra_id' => $compra_id,
    'usuario_id' => $_SESSION['usuario_id']
]);
$compra = $stmt->fetch();

if (!$compra) {
    echo "Compra não encontrada ou não pertence a você.";
    exit;
}

// Buscar itens
$stmt = $pdo->prepare("SELECT * FROM itens_compras WHERE compra_id = :compra_id");
$stmt->execute(['compra_id' => $compra_id]);
$itens = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Compra Finalizada</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Compra Finalizada</h2>
    <p><strong>Mercado:</strong> <?= htmlspecialchars($compra['mercado']) ?></p>
    <p><strong>Lista:</strong> <?= htmlspecialchars($compra['lista']) ?></p>
    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($compra['data_compra'])) ?></p>

    <table id="itens" class="table table-striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $total_geral = 0; ?>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nome_item']) ?></td>
                    <td><?= $item['quantidade'] ?></td>
                    <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                    <td>
                        R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?>
                        <?php $total_geral += $item['preco'] * $item['quantidade']; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h5 class="mt-3">Total da Compra: R$ <?= number_format($total_geral, 2, ',', '.') ?></h5>

    <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#itens').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json"
            }
        });
    });
</script>
</body>
</html>
