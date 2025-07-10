<?php
session_start();
require_once 'config/db.php'; // ou include 'db.php';

// Prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Verificar se o usuário está logado, caso contrário, redirecionar para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$nomeUsuario = $_SESSION['nome']; // Recuperar o nome do usuário logado
$nivelUsuario = $_SESSION['nivel']; // Recuperar o nível do usuário

// Obter a hora atual
$horaAtual = date('H'); // Hora no formato 24 horas (00 a 23)

// Definir a saudação com base na hora
if ($horaAtual >= 06 && $horaAtual < 12) {
    $saudacao = "Bom dia";
} elseif ($horaAtual >= 12 && $horaAtual < 18) {
    $saudacao = "Boa tarde";
} else {
    $saudacao = "Boa noite";
}

// Verificar se a variável login_time existe e calcular o tempo de atividade
if (isset($_SESSION['login_time'])) {
    // Calcular a diferença de tempo
    $tempoDeAtividade = time() - $_SESSION['login_time']; // diferença em segundos
    // Converter o tempo de atividade para um formato legível (H:i:s)
    $tempoDeAtividadeFormatado = gmdate("H:i:s", $tempoDeAtividade); // Convertendo para horas, minutos e segundos
} else {
    // Caso o login_time não esteja definido, o usuário pode estar na página pela primeira vez ou a sessão expirou
    $tempoDeAtividadeFormatado = "0:00:00"; // Caso não haja tempo de login
}
// Montar dados para o gráfico
$stmt = $pdo->prepare("
  SELECT nome_item, SUM(quantidade * preco) AS total_item
  FROM itens_compras ic
  JOIN compras c ON ic.compra_id = c.id
  WHERE c.usuario_id = :usuario_id
  GROUP BY nome_item
");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$dadosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = json_encode(array_column($dadosGrafico, 'nome_item'));
$values = json_encode(array_map(fn($d) => (float) $d['total_item'], $dadosGrafico));

// Novo: dados para gráfico de mercados
$stmt2 = $pdo->prepare("
  SELECT m.nome AS mercado, SUM(ic.quantidade * ic.preco) AS total_mercado
  FROM compras c
  JOIN itens_compras ic ON c.id = ic.compra_id
  JOIN mercados m ON c.mercado_id = m.id
  WHERE c.usuario_id = :usuario_id
  GROUP BY m.nome
");
$stmt2->execute(['usuario_id' => $_SESSION['usuario_id']]);
$dadosMercados = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$labelsMercados = json_encode(array_column($dadosMercados, 'mercado'));
$valuesMercados = json_encode(array_map(fn($d) => (float) $d['total_mercado'], $dadosMercados));

$stmt3 = $pdo->prepare("
  SELECT DATE_FORMAT(c.data_compra, '%Y-%m') AS mes_ano, SUM(ic.quantidade * ic.preco) AS total_mes
  FROM compras c
  JOIN itens_compras ic ON c.id = ic.compra_id
  WHERE c.usuario_id = :usuario_id
  GROUP BY mes_ano
  ORDER BY mes_ano
");
$stmt3->execute(['usuario_id' => $_SESSION['usuario_id']]);
$dadosMeses = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$labelsMeses = json_encode(array_map(function ($d) {
    // Formatar "2025-07" para "Jul/2025"
    $dateObj = DateTime::createFromFormat('Y-m', $d['mes_ano']);
    return $dateObj ? $dateObj->format('M/Y') : $d['mes_ano'];
}, $dadosMeses));
$valuesMeses = json_encode(array_map(fn($d) => (float) $d['total_mes'], $dadosMeses));


// Caso ainda não tenha sido definido, define o tempo de login
if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time(); // Definindo o timestamp do login no momento que o usuário acessar
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;

            /* Espaço para navbar fixa */
        }

        .header {
            background-color: #007bff;
            padding: 10px 0;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header .navbar-brand {
            color: white;
            font-size: 1.5rem;
            cursor: default;
            user-select: none;
        }

        .header .navbar-nav .nav-link {
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .header .navbar-nav .nav-link:hover {
            color: #f1f1f1;
        }

        .welcome-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .activity-time {
            font-size: 1.2rem;
            margin-top: 10px;
            color: #555;
        }

        .time-container {
            font-size: 1.1rem;
            margin-top: 20px;
            color: #666;
            font-style: italic;
        }

        .chart-row {
            display: flex;
            justify-content: space-around;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .chart-container {
            flex: 1 1 300px;
            max-width: 500px;
            height: 320px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
            padding: 15px 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .chart-container h5 {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        @media (max-width: 767px) {
            .welcome-container {
                padding: 20px;
                margin-bottom: 20px;
            }

            .chart-container {
                max-width: 100%;
                height: 280px;
                margin-bottom: 20px;
            }
        }

        .footer-text {
            text-align: center;
            margin: 40px 0 20px;
            color: #777;
            font-size: 0.9rem;
        }

        .footer-text a {
            text-decoration: none;
            color: #007bff;
            transition: color 0.3s ease;
        }

        .footer-text a:hover {
            text-decoration: underline;
            color: #0056b3;
        }
    </style>
</head>

<body>

    <!-- Cabeçalho com Navbar Dropdown -->
    <nav class="navbar navbar-expand-lg navbar-dark header">
        <div class="container">
            <a class="navbar-brand" href="#"> <?php echo $saudacao; ?>, <?php echo $nomeUsuario; ?>!</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Compras
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="mercado/lista_compra.php">Criar Lista compras</a></li>
                            <li><a class="dropdown-item" href="mercado/compras.php">Realizar compra</a></li>
                            <li><a class="dropdown-item" href="mercado/comparar_compra.php">Comparar</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Mercado
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="mercado/criar_mercado.php">Criar mercado</a></li>
                            <li><a class="dropdown-item" href="mercado/mercado.php">Mercados</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Receita
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="receita/criar_receita.php">Criar receita</a></li>
                            <li><a class="dropdown-item" href="receita/listar_receitas.php">Receitas</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Conta
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="editar_perfil.php">Perfil</a></li>
                            <?php if ((int) $nivelUsuario === 2): ?>
                                <li><a class="dropdown-item" href="painel_adm.php">Admin</a></li>
                                <li><a class="dropdown-item" href="cadastro_usuario.php">Novo Usuário</a></li>
                                <!-- Apenas visível para nível 2 -->
                            <?php endif; ?>
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
                <div class="welcome-container">
                    <h2> <?php echo $saudacao; ?>, <?php echo $nomeUsuario; ?>!</h2>
                    <p>Estamos felizes por ter você aqui.</p>
                    <div class="activity-time">
                        <p><strong>Tempo de Atividade: </strong><span id="tempoAtividade"></span></p>
                        </p>
                    </div>
                    <div class="time-container">
                        <p id="current-time"></p> <!-- Hora em tempo real -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 chart-row">
        <div class="chart-container">
            <h5 class="text-center">Gastos por Produto</h5>
            <canvas id="gastosPorProduto"></canvas>
        </div>
        <div class="chart-container">
            <h5 class="text-center">Gastos por Mercado</h5>
            <canvas id="gastosPorMercado"></canvas>
        </div>
        <div class="chart-container">
            <h5 class="text-center">Gastos por Mês</h5>
            <canvas id="gastosPorMes"></canvas>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>

    <!-- Script para atualizar a hora em tempo real -->
    <script>
        function atualizarHora() {
            const agora = new Date();
            const hora = agora.getHours().toString().padStart(2, '0');
            const minutos = agora.getMinutes().toString().padStart(2, '0');
            const segundos = agora.getSeconds().toString().padStart(2, '0');
            const data = agora.toLocaleDateString('pt-BR', { year: 'numeric', month: 'long', day: 'numeric' });

            document.getElementById('current-time').innerHTML = `Data: ${data} | Hora: ${hora}:${minutos}:${segundos}`;
        }
        // Atualizar a hora a cada segundo
        setInterval(atualizarHora, 1000);
    </script>
    <script>
        const labelsProd = <?= $labels ?>;
        const dataProd = <?= $values ?>;
        const labelsMerc = <?= $labelsMercados ?>;
        const dataMerc = <?= $valuesMercados ?>;
        const labelsMes = <?= $labelsMeses ?>;
        const dataMes = <?= $valuesMeses ?>;
        const backgroundColors = [
            '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6610f2',
            '#ff5733', '#33c1ff', '#9d33ff', '#33ff57', '#ff33a1', '#a1ff33', '#33ffa1', '#ff3380', '#8033ff', '#33a1ff',
            '#ff8c00', '#b22222', '#008080', '#4682b4', '#daa520', '#2e8b57', '#9932cc', '#dc143c', '#00ced1', '#ba55d3',
            '#3cb371', '#8b0000', '#b8860b', '#cd5c5c', '#f08080', '#8fbc8f', '#00fa9a', '#afeeee', '#5f9ea0', '#7fffd4',
            '#8a2be2', '#7b68ee', '#dda0dd', '#6a5acd', '#ff69b4', '#db7093', '#ffb6c1', '#bc8f8f', '#cd853f', '#deb887',
            '#f4a460', '#d2b48c', '#fa8072', '#e9967a', '#ffa07a', '#ffe4e1', '#f5deb3', '#f5f5dc', '#f0e68c', '#e0ffff',
            '#00ff7f', '#adff2f', '#7fff00', '#32cd32', '#98fb98', '#90ee90', '#ffdead', '#ffe4b5', '#ffe4c4', '#ffdab9',
            '#b0e0e6', '#add8e6', '#87cefa', '#87ceeb', '#00bfff', '#1e90ff', '#6495ed', '#4682b4', '#4169e1', '#6a5acd',
            '#483d8b', '#000080', '#191970', '#0000cd', '#00008b', '#8b008b', '#800080', '#9370db', '#ba55d3', '#9400d3',
            '#ff00ff', '#ff1493', '#c71585', '#db7093', '#ff69b4', '#da70d6', '#ee82ee', '#d8bfd8', '#ffe4e1', '#ffc0cb'
        ];
        new Chart(document.getElementById('gastosPorProduto'), {
            type: 'pie',
            data: {
                labels: labelsProd,
                datasets: [{
                    data: dataProd,
                    backgroundColor: backgroundColors.slice(0, labelsProd.length),
                    hoverOffset: 4
                }]
            },

            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('gastosPorMercado'), {
            type: 'bar',
            data: {
                labels: labelsMerc,
                datasets: [{
                    label: 'Total (R$)',
                    data: dataMerc,
                    backgroundColor: backgroundColors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) } }
                }
            }
        });
        new Chart(document.getElementById('gastosPorMes'), {
            type: 'bar',
            data: {
                labels: labelsMes,
                datasets: [{
                    label: 'Gastos por Mês',
                    data: dataMes,
                    backgroundColor: backgroundColors.slice(0, labelsMes.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            }
                        }
                    }
                }
            }
        });

    </script>
    <script>
        const loginTimestamp = <?= $_SESSION['login_time'] ?>;

        function atualizarTempoAtividade() {
            const agora = Math.floor(Date.now() / 1000); // em segundos
            const tempo = agora - loginTimestamp;

            const horas = Math.floor(tempo / 3600).toString().padStart(2, '0');
            const minutos = Math.floor((tempo % 3600) / 60).toString().padStart(2, '0');
            const segundos = (tempo % 60).toString().padStart(2, '0');

            document.getElementById('tempoAtividade').textContent = `${horas}:${minutos}:${segundos}`;
        }

        setInterval(atualizarTempoAtividade, 1000);
        atualizarTempoAtividade(); // inicia imediatamente
    </script>

    <footer class="footer-text">
        <p>&copy; 2025 Lista de Compras</p>
    </footer>

</body>

</html>