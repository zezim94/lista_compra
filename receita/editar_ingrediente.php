<?php 
session_start(); 
include'../config/db.php';

if (!isset($_SESSION['usuario_id'])) header('Location: login.php');

$id = $_GET['id'] ?? null; 
$rid = $_GET['rid'] ?? null;

$stmt = $pdo->prepare("SELECT ir.*, r.usuario_id FROM ingredientes_receita ir JOIN receitas r ON ir.receita_id=r.id WHERE ir.id=:id");
$stmt->execute(['id' => $id]);
$ing = $stmt->fetch();

if (!$ing || $ing['usuario_id'] != $_SESSION['usuario_id']) exit('Não autorizado.');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->prepare("UPDATE ingredientes_receita SET nome_ingrediente=:n, quantidade=:q, preco=:p WHERE id=:id")
        ->execute([
            'n' => $_POST['nome_ingrediente'],
            'q' => $_POST['quantidade'],
            'p' => $_POST['preco'],
            'id' => $id
        ]);
    header("Location: detalhes_receita.php?id=" . $rid);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Editar Ingrediente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .form-container {
            max-width: 500px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2 class="mb-4 text-center">Editar Ingrediente</h2>
        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="nome_ingrediente" class="form-label">Nome do Ingrediente</label>
                <input id="nome_ingrediente" name="nome_ingrediente" type="text" class="form-control" required
                    value="<?= htmlspecialchars($ing['nome_ingrediente']) ?>" />
            </div>

            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input id="quantidade" name="quantidade" type="number" step="0.01" class="form-control" required
                    value="<?= $ing['quantidade'] ?>" />
            </div>

            <div class="mb-3">
                <label for="preco" class="form-label">Preço Unitário (R$)</label>
                <input id="preco" name="preco" type="number" step="0.01" class="form-control" required
                    value="<?= $ing['preco'] ?>" />
            </div>

            <div class="btn-group mt-4">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="detalhes_receita.php?id=<?= $rid ?>" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
