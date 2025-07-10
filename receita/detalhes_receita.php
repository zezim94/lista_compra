<?php
session_start();
include'../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$receita_id = $_GET['id'] ?? null;

if (!$receita_id) {
    echo "Receita não encontrada.";
    exit;
}

// Verifica se a receita pertence ao usuário
$stmt = $pdo->prepare("SELECT * FROM receitas WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute(['id' => $receita_id, 'usuario_id' => $usuario_id]);
$receita = $stmt->fetch();

if (!$receita) {
    echo "Receita inválida ou não autorizada.";
    exit;
}

// Adicionar novo ingrediente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_ingrediente'])) {
    $nome = trim($_POST['nome_ingrediente']);
    $quantidade = floatval($_POST['quantidade']);
    $preco = floatval($_POST['preco']);

    if ($nome && $quantidade > 0 && $preco >= 0) {
        $stmt = $pdo->prepare("INSERT INTO ingredientes_receita (receita_id, nome_ingrediente, quantidade, preco) VALUES (:receita_id, :nome, :quantidade, :preco)");
        $stmt->execute([
            'receita_id' => $receita_id,
            'nome' => $nome,
            'quantidade' => $quantidade,
            'preco' => $preco
        ]);
        header("Location: detalhes_receita.php?id=" . $receita_id);
        exit;
    }
}

// Pega os ingredientes da receita
$stmt = $pdo->prepare("SELECT * FROM ingredientes_receita WHERE receita_id = :receita_id");
$stmt->execute(['receita_id' => $receita_id]);
$ingredientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Receita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2><?= htmlspecialchars($receita['nome']) ?></h2>
    <p class="text-muted">Criada em: <?= date('d/m/Y', strtotime($receita['data_criacao'])) ?></p>

    <?php
    $total = 0;
    foreach ($ingredientes as $ing) {
        $total += $ing['quantidade'] * $ing['preco'];
    }
    ?>
    <h4 class="text-success">💰 Total da Receita: R$ <?= number_format($total, 2, ',', '.') ?></h4>

    <!-- Formulário de novo ingrediente -->
    <div class="card p-3 my-4">
        <h5>Adicionar Ingrediente</h5>
        <form method="POST">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="nome_ingrediente" class="form-control" placeholder="Ingrediente" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="number" step="0.01" name="quantidade" class="form-control" placeholder="Quantidade" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="number" step="0.01" name="preco" class="form-control" placeholder="Preço Unitário" required>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-success w-100">Adicionar</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($ingredientes)): ?>
        <div class="alert alert-warning">Nenhum ingrediente adicionado a esta receita.</div>
    <?php else: ?>
        <table class="table table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Ingrediente</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Subtotal (R$)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredientes as $ing): ?>
                    <?php $subtotal = $ing['quantidade'] * $ing['preco']; ?>
                    <tr>
                        <td><?= htmlspecialchars($ing['nome_ingrediente']) ?></td>
                        <td><?= $ing['quantidade'] ?></td>
                        <td><?= number_format($ing['preco'], 2, ',', '.') ?></td>
                        <td><?= number_format($subtotal, 2, ',', '.') ?></td>
                        <td>
                            <a href="editar_ingrediente.php?id=<?= $ing['id'] ?>&rid=<?= $receita_id ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="deletar_ingrediente.php?id=<?= $ing['id'] ?>&rid=<?= $receita_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary">
                    <td colspan="3"><strong>Total da Receita</strong></td>
                    <td colspan="2"><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <a href="listar_receitas.php" class="btn btn-secondary mt-3">← Voltar para Receitas</a>
</div>

</body>
</html>
