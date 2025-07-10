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

include '../config/db.php';

// Função para gerar UUID v4
function gerar_uuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    // Gera UUID para o novo mercado
    $uuid = gerar_uuid();

    // Inserir o novo mercado com UUID como id
    $stmt = $pdo->prepare("INSERT INTO mercados (id, nome, endereco, descricao, usuario_id) VALUES (:id, :nome, :endereco, :descricao, :usuario_id)");
    $stmt->execute([
        'id' => $uuid,
        'nome' => $nome,
        'endereco' => $endereco,
        'descricao' => $descricao,
        'usuario_id' => $usuario_id
    ]);

    echo "<div class='alert alert-success mt-3'>Mercado criado com sucesso!</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Mercado</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .form-control,
        .form-textarea {
            margin-bottom: 15px;
        }

        .form-textarea {
            height: 150px;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }

        /* Responsividade */
        @media (max-width: 767px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <div class="header">
        <h1>Criar Mercado</h1>
        <p>Preencha o formulário abaixo para criar um novo mercado</p>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="form-container">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Mercado</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" required>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control form-textarea" id="descricao" name="descricao"
                                required></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Criar Mercado</button>
                    </form>
                    <!-- Botão Voltar -->
                    <div class="mt-3">
                        <a href="../index.php" class="btn btn-secondary">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Lista de Compras</p>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>

</body>

</html>