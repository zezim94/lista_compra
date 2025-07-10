<?php
session_start();

// Prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Verificar se o usuário está logado, caso contrário, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include'config/db.php';

// Recuperar os dados do usuário logado
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch();

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Se a senha foi informada, criptografar a nova senha
    if (!empty($senha)) {
        $senha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha, 'id' => $usuario_id]);
    } else {
        // Se não for informada senha, não atualizar o campo senha
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'id' => $usuario_id]);
    }

    // Exibir mensagem de sucesso
    echo "<div class='alert alert-success'>Perfil atualizado com sucesso!</div>";
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
        }

        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 30px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn-custom:hover {
            background-color: #0056b3;
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

        /* Responsividade */
        @media (max-width: 767px) {
            .card {
                margin-top: 20px;
            }

            .card-body {
                padding: 15px;
            }

            .btn-custom {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Editar Perfil</h3>

                        <!-- Formulário de edição de perfil -->
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Nova Senha (Deixe em branco para não alterar)</label>
                                <input type="password" class="form-control" name="senha">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-custom">Salvar Alterações</button>
                            </div>
                        </form>

                        <div class="footer-text mt-3">
                            <a href="index.php">Voltar à página inicial</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
