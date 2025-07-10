<?php
session_start();
include'../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar receitas do usuário
$stmt = $pdo->prepare("
    SELECT r.id, r.nome, r.data_criacao,
           COALESCE(SUM(ir.quantidade * ir.preco), 0) AS total
    FROM receitas r
    LEFT JOIN ingredientes_receita ir ON r.id = ir.receita_id
    WHERE r.usuario_id = :usuario_id
    GROUP BY r.id
    ORDER BY r.data_criacao DESC
");
$stmt->execute(['usuario_id' => $usuario_id]);
$receitas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Receitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Minhas Receitas</h2>

    <a href="criar_receita.php" class="btn btn-primary mb-3">+ Nova Receita</a>

    <?php if (empty($receitas)): ?>
        <div class="alert alert-info">Nenhuma receita criada ainda.</div>
    <?php else: ?>
        <table class="table table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Data</th>
                    <th>Total (R$)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receitas as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nome']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_criacao'])) ?></td>
                        <td><?= number_format($r['total'], 2, ',', '.') ?></td>
                        <td>
                            <a href="detalhes_receita.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

</body>
</html>
