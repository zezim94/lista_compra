<?php
session_start();

// Verificar se o administrador está logado
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 2) {
    header('Location: login.php');
    exit;
}

include'../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Buscar os dados do mercado
    $stmt = $pdo->prepare("SELECT * FROM mercados WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $mercado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mercado) {
        echo "Mercado não encontrado.";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $endereco = $_POST['endereco']; // Novo campo

        // Atualizar os dados do mercado
        $stmt = $pdo->prepare("UPDATE mercados SET nome = :nome, descricao = :descricao, endereco = :endereco WHERE id = :id");
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'endereco' => $endereco,
            'id' => $id
        ]);

        header('Location: mercado.php');
        exit;
    }

} else {
    echo "ID do mercado não fornecido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mercado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <h2>Editar Mercado</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $mercado['nome']; ?>"
                    required>
            </div>
            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco"
                    value="<?php echo $mercado['endereco']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="4"
                    required><?php echo $mercado['descricao']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>
    </div>

</body>

</html>