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

// Obter mercados do banco de dados
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT * FROM mercados WHERE usuario_id = :usuario_id");
$stmt->execute(['usuario_id' => $usuario_id]);
$mercados = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Mercados</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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

        .table-container {
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
            .table-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <div class="header">
        <h1>Meus Mercados</h1>
        <p>Lista de mercados cadastrados</p>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="table-container">
                    <!-- Botão Voltar -->
                    <div class="mb-3">
                        <a href="../index.php" class="btn btn-secondary">Voltar</a>
                    </div>

                    <!-- Tabela de Mercados -->
                    <table id="mercados" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Endereço</th> <!-- Nova coluna -->
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mercados as $mercado): ?>
                                <tr>
                                    <td><?= $mercado['id']; ?></td>
                                    <td><?= $mercado['nome']; ?></td>
                                    <td><?= $mercado['descricao']; ?></td>
                                    <td><?= $mercado['endereco']; ?></td> <!-- Exibir endereço -->
                                    <td>
                                        <a href="editar_mercado.php?id=<?php echo $mercado['id']; ?>"class="btn btn-primary btn-sm">Editar</a>
                                        <a href="excluir_mercado.php?id=<?= $mercado['id']; ?>"
                                            class="btn btn-danger btn-sm">Deletar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
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

    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#mercados').DataTable({
                language: {
                    "sEmptyTable": "Nenhum registro encontrado",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                    "sLengthMenu": "_MENU_ resultados por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sSearch": "Pesquisar:",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "oPaginate": {
                        "sNext": "Próximo",
                        "sPrevious": "Anterior",
                        "sFirst": "Primeiro",
                        "sLast": "Último"
                    },
                    "oAria": {
                        "sSortAscending": ": Ordenar colunas de forma ascendente",
                        "sSortDescending": ": Ordenar colunas de forma descendente"
                    }
                }
            });
        });

    </script>

</body>

</html>