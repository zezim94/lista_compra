<?php
session_start();

// Verificar se o administrador está logado
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 2) {
    header('Location: login.php');
    exit;
}

include'config/db.php';

// Obter o ID do usuário a ser editado
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
    // Atualizar informações do usuário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    // Verificar se a senha foi informada
    if (!empty($_POST['senha']) && !empty($_POST['confirma_senha'])) {
        $senha = $_POST['senha'];
        $confirma_senha = $_POST['confirma_senha'];

        // Verificar se as senhas coincidem
        if ($senha != $confirma_senha) {
            echo "As senhas não coincidem.";
            exit;
        }

        // Hashear a senha antes de salvar no banco
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Atualizar no banco de dados, incluindo a nova senha
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, status = :status, senha = :senha WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'status' => $status, 'senha' => $senhaHash, 'id' => $id]);
    } else {
        // Se a senha não foi fornecida, apenas atualize os dados sem mexer na senha
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, status = :status WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'status' => $status, 'id' => $id]);
    }

    header('Location: painel.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Editar Usuário</h2>

    <form action="editar_usuarios.php?id=<?php echo $usuario['id']; ?>" method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $usuario['nome']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $usuario['email']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="ativo" <?php echo ($usuario['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                <option value="inativo" <?php echo ($usuario['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
            </select>
        </div>
        <!-- Campos de Senha e Confirmação de Senha -->
        <div class="mb-3">
            <label for="senha" class="form-label">Nova Senha (Deixe em branco para não alterar)</label>
            <input type="password" class="form-control" id="senha" name="senha">
        </div>
        <div class="mb-3">
            <label for="confirma_senha" class="form-label">Confirmar Nova Senha</label>
            <input type="password" class="form-control" id="confirma_senha" name="confirma_senha">
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>

</body>
</html>
