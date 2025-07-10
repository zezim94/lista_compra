<?php
session_start();

// Prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Verificar se o administrador está logado
if (!isset($_SESSION['nivel'])) {
    header('Location: login.php'); // Redireciona para o login se não estiver logado
    exit;
}

// Verificar o nível do usuário (apenas nível 2 tem acesso a esta página)
if ($_SESSION['nivel'] != 2) {
    echo "<script>alert('Você não tem permissão para acessar esta página.'); window.location.href='logout.php';</script>";
    exit; // Ou redirecionar para outra página, como index.php ou uma página de erro
}

// Obter nome do administrador logado
$nomeAdmin = $_SESSION['nome']; // Supondo que o nome do admin esteja armazenado na sessão

// Conexão com o banco de dados (substitua pelas suas credenciais)
include'config/db.php';

// Consultar os usuários cadastrados
$stmtUsuarios = $pdo->prepare("SELECT id, nome, email, status FROM usuarios");
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// Consultar os mercados cadastrados
$stmtMercados = $pdo->prepare("SELECT * FROM mercados");
$stmtMercados->execute();
$mercados = $stmtMercados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- Font Awesome CSS (para ícones) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

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

        @media (max-width: 767px) {
            .container {
                margin-top: 30px;
            }
        }

        /* Estilos DataTable */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: none;
            background-color: #007bff;
            color: white;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #0056b3;
        }

        .dataTables_length select {
            border: 1px solid #ddd;
            padding: 5px;
        }

        .dataTables_filter input {
            border: 1px solid #ddd;
            padding: 5px;
        }

        /* Estilo para a data e hora */
        .date-time {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn-actions {
            margin: 0 5px;
        }

        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }

        .panel-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
            margin-left: 15px;
            /* Adicionando margem à esquerda */
            margin-right: 15px;
            /* Adicionando margem à direita */
        }

        .table-responsive {
            margin-top: 20px;
            /* Margem superior para separar da seção anterior */
        }

        .table {
            width: 100%;
            /* Certifica-se que a tabela ocupe toda a largura disponível dentro do seu contêiner */
        }

        .table-responsive {
            overflow-x: auto;
            /* Garante que a tabela seja rolável se for muito larga */
        }

        /* Ajuste da tabela para telas pequenas */
        @media (max-width: 767px) {
            .table-responsive {
                margin-left: 10px;
                margin-right: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Cabeçalho com Navbar Dropdown -->
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

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <!-- Começo do painel com margens laterais -->
                <div class="panel-container">
                    <h2>Bem-vindo, <?php echo $nomeAdmin; ?>!</h2>

                    <!-- Exibir Data e Hora em Tempo Real -->
                    <p class="date-time" id="dataHora"></p>

                    <div class="mt-4">
                        <h4>Usuários Cadastrados</h4>
                        <!-- Contêiner com margens laterais -->
                        <div class="table-responsive">
                            <table id="usuarios" class="display table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo $usuario['id']; ?></td>
                                            <td><?php echo $usuario['nome']; ?></td>
                                            <td><?php echo $usuario['email']; ?></td>
                                            <td><?php echo $usuario['status']; ?></td>
                                            <td>
                                                <!-- Botão Editar -->
                                                <a href="editar_usuarios.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-actions">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Botão Excluir -->
                                                <a href="excluir_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-actions">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4>Mercados Cadastrados</h4>
                        <!-- Contêiner com margens laterais -->
                        <div class="table-responsive">
                            <table id="mercados" class="display table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mercados as $mercado): ?>
                                        <tr>
                                            <td><?php echo $mercado['id']; ?></td>
                                            <td><?php echo $mercado['nome']; ?></td>
                                            <td><?php echo $mercado['descricao']; ?></td>
                                            <td>
                                                <!-- Botão Editar -->
                                                <a href="mercado/editar_mercado.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-actions">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Botão Excluir -->
                                                <a href="mercado/excluir_mercado.php?id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-actions">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Fim do painel com margens laterais -->
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS e DataTable JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Inicializar DataTable -->
    <script>
        $(document).ready(function() {
            $('#usuarios').DataTable({
                "paging": true,
                "searching": true,
                "lengthChange": true,
                "pageLength": 10,
                "language": {
                    "sEmptyTable": "Nenhum dado disponível na tabela",
                    "sInfo": "Mostrando _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros totais)",
                    "sSearch": "Pesquisar:",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sPrevious": "Anterior",
                        "sNext": "Próximo",
                        "sLast": "Último"
                    }
                }
            });

            $('#mercados').DataTable({
                "paging": true,
                "searching": true,
                "lengthChange": true,
                "pageLength": 10,
                "language": {
                    "sEmptyTable": "Nenhum dado disponível na tabela",
                    "sInfo": "Mostrando _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros totais)",
                    "sSearch": "Pesquisar:",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sPrevious": "Anterior",
                        "sNext": "Próximo",
                        "sLast": "Último"
                    }
                }
            });
        });

        // Função para formatar e exibir a data e hora em tempo real
        function exibirDataHora() {
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            const dataAtual = new Date();
            const dataHoraFormatada = dataAtual.toLocaleDateString('pt-BR', options);

            document.getElementById('dataHora').innerHTML = `${dataAtual.getDate()}, ${dataHoraFormatada} - ${dataAtual.toLocaleTimeString()}`;
        }

        // Atualiza a data e hora a cada segundo
        setInterval(exibirDataHora, 1000);
    </script>

</body>

</html>