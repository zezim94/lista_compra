<?php
session_start();

// Verificar se o administrador está logado
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 2) {
    header('Location: login.php');
    exit;
}

include'config/db.php';

// Obter o ID do usuário a ser excluído
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar os dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado.";
        exit;
    }
} else {
    echo "ID do usuário não fornecido.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Excluir o usuário
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header('Location: painel.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Confirmar Exclusão de Usuário</h2>
    <p>Você tem certeza que deseja excluir o usuário "<?php echo $usuario['nome']; ?>"?</p>

    <form action="excluir_usuario.php?id=<?php echo $usuario['id']; ?>" method="POST">
        <button type="submit" class="btn btn-danger">Excluir</button>
        <a href="painel_adm.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>
