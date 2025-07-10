<?php
session_start();
include'config/db.php';

$_SESSION['login_time'] = time();  // Armazenando o timestamp da hora do login


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Preparar a consulta para buscar o usuário com o e-mail fornecido
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    // Verificar se o usuário existe e se a senha está correta
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Armazenar informações do usuário na sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['nivel'] = $usuario['nivel']; // Armazenar o nível na sessão

        // Redirecionar para a página inicial após login bem-sucedido
        header('Location: painel_adm.php'); // Ou outra página específica
        exit;
    } else {
        // Caso as credenciais estejam incorretas
        echo "Credenciais inválidas.";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
        }

        .login-container {
            margin-top: 100px;
            max-width: 400px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
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
            .login-container {
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
                <div class="login-container">
                    <h2>Entrar</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-block">Entrar</button>
                        </div>
                    </form>
                    <div class="footer-text">
                        <p>Não tem uma conta? <a href="cadastro_usuario.php">Cadastrar-se</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>

</body>

</html>
