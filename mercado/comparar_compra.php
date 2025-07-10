<?php
session_start();
include'../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redirecionar para a página de login caso o usuário não esteja logado
    header('Location: login.php');
    exit;
}

// Recuperar o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Inicializar variáveis de filtros
$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim = $_POST['data_fim'] ?? '';
$mercado = $_POST['mercado'] ?? '';
$lista_compra = $_POST['lista_compra'] ?? '';

// Consultar mercados disponíveis para o usuário logado
$query_mercados = "
    SELECT m.id, m.nome 
    FROM mercados m
    WHERE m.usuario_id = :usuario_id
    UNION
    SELECT l.mercado_id, m.nome 
    FROM listas_compras l
    JOIN mercados m ON l.mercado_id = m.id
    WHERE l.usuario_id = :usuario_id
";
$stmt_mercados = $pdo->prepare($query_mercados);
$stmt_mercados->execute(['usuario_id' => $usuario_id]);
$mercados = $stmt_mercados->fetchAll(PDO::FETCH_ASSOC);

// Agora criar a query para buscar itens de compras com filtros
$query = "
    SELECT ic.*, lc.nome AS lista_nome, m.nome AS nome_mercado, c.total AS total_compra
    FROM itens_compras ic
    LEFT JOIN compras c ON ic.compra_id = c.id
    LEFT JOIN listas_compras lc ON c.lista_compra_id = lc.id
    LEFT JOIN mercados m ON c.mercado_id = m.id
    WHERE 1=1
";

if ($data_inicio) {
    $query .= " AND ic.data >= :data_inicio";
}

if ($data_fim) {
    $query .= " AND ic.data <= :data_fim";
}

if ($mercado) {
    $query .= " AND c.mercado_id = :mercado";
}

if ($lista_compra) {
    $query .= " AND c.lista_compra_id = :lista_compra";
}

$stmt = $pdo->prepare($query);

if ($data_inicio) {
    $stmt->bindParam(':data_inicio', $data_inicio);
}

if ($data_fim) {
    $stmt->bindParam(':data_fim', $data_fim);
}

if ($mercado) {
    $stmt->bindParam(':mercado', $mercado);
}

if ($lista_compra) {
    $stmt->bindParam(':lista_compra', $lista_compra);
}

$stmt->execute();
$itens_compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar as listas de compras relacionadas ao mercado selecionado
$listas_compra = [];
if ($mercado) {
    $query_listas = "
        SELECT id, nome
        FROM listas_compras
        WHERE mercado_id = :mercado
    ";
    $stmt_listas = $pdo->prepare($query_listas);
    $stmt_listas->execute(['mercado' => $mercado]);
    $listas_compra = $stmt_listas->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itens de Compras</title>

    <!-- Link para o CSS do DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- Link para o CSS do jQuery UI (se necessário para estilos de filtros) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI para data picker -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- Link para o JS do DataTables -->
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Corpo da página */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Estilos do formulário e filtros */
        .filters {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .filters label {
            font-weight: bold;
        }

        .filters input,
        .filters select {
            padding: 8px;
            font-size: 14px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .filters button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        /* Estilos da tabela */
        #itens_compras {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #itens_compras th,
        #itens_compras td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        #itens_compras th {
            background-color: #343a40;
            color: white;
            font-size: 16px;
        }

        #itens_compras tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        #itens_compras td {
            font-size: 14px;
            color: #555;
        }

        /* Responsividade para dispositivos móveis */
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                padding: 15px;
            }

            .filters input,
            .filters select,
            .filters button {
                width: 100%;
                margin-bottom: 10px;
            }

            #itens_compras th,
            #itens_compras td {
                font-size: 12px;
                padding: 8px;
            }

            .container {
                padding: 15px;
            }
        }

        #itens_compras tbody tr:hover {
            background-color: #e9f5ff !important;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Filtros -->
        <div class="filters">
            <form method="POST">
                <div>
                    <label for="data_inicio">Data Início:</label>
                    <input type="text" id="data_inicio" name="data_inicio" class="date-filter" placeholder="Data Início"
                        value="<?= $data_inicio ?>">

                    <label for="data_fim">Data Fim:</label>
                    <input type="text" id="data_fim" name="data_fim" class="date-filter" placeholder="Data Fim"
                        value="<?= $data_fim ?>">
                </div>

                <div>
                    <label for="mercado">Mercado:</label>
                    <select id="mercado" name="mercado">
                        <option value="">Selecione o Mercado</option>
                        <?php foreach ($mercados as $mercado_item): ?>
                            <option value="<?= $mercado_item['id'] ?>" <?= $mercado == $mercado_item['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mercado_item['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="lista_compra">Lista de Compras:</label>
                    <select id="lista_compra" name="lista_compra">
                        <option value="">Selecione a Lista</option>
                        <?php foreach ($listas_compra as $lista_item): ?>
                            <option value="<?= $lista_item['id'] ?>" <?= $lista_compra == $lista_item['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lista_item['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Filtrar</button>
                    <button type="button" onclick="window.location.href='?';">Limpar Filtros</button>
                </div>
            </form>
             <div style="margin-top: 20px;">
            <button onclick="window.location.href='../index.php';"
                style="padding: 10px 15px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Voltar</button>
        </div>
        </div>
       

        <!-- Tabela de Itens de Compra -->
        <table id="itens_compras" class="display hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lista Compra</th>
                    <th>Nome Item</th>
                    <th>Quantidade</th>
                    <th>Preço</th>
                    <th>Total do Item</th>
                    <th>Mercado</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($itens_compras): ?>
                    <?php foreach ($itens_compras as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['lista_nome'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['nome_item'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['quantidade'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['preco'] ?? '') ?></td>
                            <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($item['nome_mercado'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['data'] ?? '') ?></td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhum item encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Configura o filtro de data (usando o jQuery UI)
        $('.date-filter').datepicker({
            dateFormat: 'yy-mm-dd'
        });

        // Quando o mercado for selecionado, carregar as listas de compras associadas
        $('#mercado').change(function () {
            var mercadoId = $(this).val();
            if (mercadoId) {
                // Recarregar as listas de compras dinamicamente
                $.ajax({
                    url: 'get_listas.php', // Coloque aqui o arquivo PHP que retorna as listas baseadas no mercado
                    method: 'GET',
                    data: {
                        mercado_id: mercadoId
                    },
                    success: function (response) {
                        $('#lista_compra').html(response);
                    }
                });
            } else {
                $('#lista_compra').html('<option value="">Selecione a Lista</option>');
            }
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#itens_compras').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
                }
            });
        });
    </script>

</body>

</html>