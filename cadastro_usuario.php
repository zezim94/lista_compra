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

// Verificar se o usuário está logado, caso contrário, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); // Redireciona para login
    exit;
}



include'config/db.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografar a senha

    // Preparar a consulta para inserir o novo usuário
    // Exemplo para criar um usuário com Nível 1 ou Nível 2
    $nivel = 1; // ou 2 dependendo do seu requisito
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)");
    $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha, 'nivel' => $nivel]);


    echo "<div class='alert alert-success' role='alert'>Usuário registrado com sucesso!</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
        }

        .register-container {
            margin-top: 100px;
            max-width: 400px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .register-container h2 {
            text-align: center;
            color: #333;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .form-control {
            border-radius: 5px;
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

        /* Garantir responsividade extra */
        @media (max-width: 767px) {
            .register-container {
                margin-top: 30px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <!-- A coluna vai ocupar 12 no celular e 6 no tablet e 4 no desktop -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="register-container">
                    <h2>Cadastro de Usuário</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-block">Cadastrar</button>
                        </div>
                        <div class="d-grid gap-2 mt-2">
                            <a href="index.php" class="btn btn-secondary btn-block">Voltar para Início</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>

</body>

</html>