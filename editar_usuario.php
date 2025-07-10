<?php
session_start();
// Prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); // Redireciona para login se o usuário não estiver logado
    exit;
}

$nivel = $_SESSION['nivel'];

if ($nivel == 1) {
    // Usuário de nível 1: acesso restrito a algumas páginas
    // Redirecionar ou exibir erro
} elseif ($nivel == 2) {
    // Usuário de nível 2: acesso completo
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); // Redireciona para login
    exit;
}

include('db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "Você precisa estar logado para editar seu perfil.";
    exit;
}

// Obter os dados do usuário logado
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch();

// Se o usuário não existir, redireciona para a página de login
if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verificar se a senha foi alterada, e se sim, fazer o hash da nova senha
    if (!empty($senha)) {
        $senha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha, 'id' => $usuario_id]);
    } else {
        // Se a senha não foi alterada, apenas atualizamos o nome e e-mail
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'id' => $usuario_id]);
    }

    echo "<div class='alert alert-success'>Informações atualizadas com sucesso!</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }

        .header {
            background-color: #007bff;
            padding: 10px 0;
            color: white;
        }

        .header .navbar-brand {
            color: white;
            font-size: 1.5rem;
        }

        .header .navbar-nav .nav-link {
            color: white;
            font-size: 1.1rem;
        }

        .header .navbar-nav .nav-link:hover {
            color: #f1f1f1;
        }

        .panel-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
        }

        .footer-text a {
            text-decoration: none;
            color: #007bff;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Cabeçalho com Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark header">
    <div class="container">
        <a class="navbar-brand" href="#">Painel de Administração</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="index.php">Início</a></li>
                        <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="panel-container">
                <h2 class="text-center">Editar Perfil</h2>

                <!-- Formulário de edição -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="Nova senha (opcional)">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Salvar alterações</button>
                </form>

                <!-- Botão Voltar -->
                <div class="mt-3">
                    <button onclick="window.history.back();" class="btn btn-secondary w-100">Voltar</button>
                </div>

                <div class="footer-text mt-4">
                    <p>&copy; 2025 - Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS e Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>

</body>
</html>
