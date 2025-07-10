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

    // Consultar o mercado a ser excluído
    $stmt = $pdo->prepare("SELECT * FROM mercados WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $mercado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mercado) {
        echo "Mercado não encontrado.";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Excluir o mercado
        $stmt = $pdo->prepare("DELETE FROM mercados WHERE id = :id");
        $stmt->execute(['id' => $id]);

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
    <title>Confirmar Exclusão de Mercado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Confirmar Exclusão de Mercado</h2>
    <p>Você tem certeza que deseja excluir o mercado "<?php echo $mercado['nome']; ?>"?</p>

    <form method="POST">
        <button type="submit" class="btn btn-danger">Excluir</button>
        <a href="mercado.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>
