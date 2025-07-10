<?php
session_start();

// Prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Verificar se o usuário está logado, caso contrário, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); // Redireciona para login
    exit;
}

include'../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['usuario_id'])) {
        echo "Você precisa estar logado para criar uma lista de compras.";
        exit;
    }

    $mercado_id = $_POST['mercado_id'];
    $nome = $_POST['nome'];
    $data = $_POST['data'];
    $usuario_id = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("INSERT INTO listas_compras (mercado_id, nome, data, usuario_id) VALUES (:mercado_id, :nome, :data, :usuario_id)");
    $stmt->execute(['mercado_id' => $mercado_id, 'nome' => $nome, 'data' => $data, 'usuario_id' => $usuario_id]);

    echo "Lista de compras criada com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Lista de Compras</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
        }

        .container {
            margin-top: 50px;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            text-align: center;
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

        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
        }

        .btn-back:hover {
            background-color: #5a6268;
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
    <h1>Criar Lista de Compras</h1>
    <p>Preencha os dados abaixo para criar uma nova lista de compras</p>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-container">
                <!-- Botão Voltar -->
                <div class="mb-3">
                     <a href="../index.php" class="btn btn-secondary">Voltar</a>
                </div>

                <!-- Formulário de criação de lista de compras -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="mercado_id" class="form-label">Mercado</label>
                        <select class="form-select" name="mercado_id" id="mercado_id" required>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM mercados WHERE usuario_id = {$_SESSION['usuario_id']}");
                            while ($row = $stmt->fetch()) {
                                echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Lista</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control" id="data" name="data" required>
                    </div>

                    <button type="submit" class="btn btn-custom w-100">Criar Lista de Compras</button>
                </form>
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
