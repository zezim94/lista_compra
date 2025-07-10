<?php
session_start();
include('../config/db.php');
// Função para salvar erro no banco
function salvarErro($mensagem, $arquivo, $linha, $pdo)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO log_erros (mensagem, arquivo, linha) VALUES (:mensagem, :arquivo, :linha)");
        $stmt->execute([
            ':mensagem' => $mensagem,
            ':arquivo' => $arquivo,
            ':linha' => $linha
        ]);
    } catch (PDOException $e) {
        // Caso falhe, evita erro em cascata, poderia registrar em arquivo
        error_log("Falha ao salvar erro no banco: " . $e->getMessage());
    }
}

// Tratador de erros customizado
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($pdo) {
    // Ignora erros @ de supressão
    if (error_reporting() === 0) {
        return false;
    }

    salvarErro($errstr, $errfile, $errline, $pdo);

    // Você pode escolher se quer continuar o tratamento padrão do erro ou não:
    // Para continuar com o tratamento padrão (exibir mensagem, etc), retorne false
    // Para não continuar, retorne true

    return false;
});

// Tratador de exceções não capturadas
set_exception_handler(function ($exception) use ($pdo) {
    salvarErro($exception->getMessage(), $exception->getFile(), $exception->getLine(), $pdo);
    // Opcional: exibir mensagem ou redirecionar para página de erro
    echo "Ocorreu um erro. Por favor, tente novamente mais tarde.";
    exit;
});

// Proteção contra cache
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Redireciona se usuário não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Inicializar sessão de itens temporários
if (!isset($_SESSION['itens_temp'])) {
    $_SESSION['itens_temp'] = [];
}

// Remover item da lista temporária
if (isset($_GET['remover_item']) && is_numeric($_GET['remover_item'])) {
    $index = (int) $_GET['remover_item'];
    if (isset($_SESSION['itens_temp'][$index])) {
        unset($_SESSION['itens_temp'][$index]);
        $_SESSION['itens_temp'] = array_values($_SESSION['itens_temp']); // Reindexa
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$lista_id = $_SESSION['itens_temp'][0]['lista_id'] ?? null;
$mercado_id = $_SESSION['itens_temp'][0]['mercado_id'] ?? null;

// Processar adição de item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_item'], $_POST['quantidade'], $_POST['preco'], $_POST['mercado_id'], $_POST['lista_id'])) {
    $nome_item = trim($_POST['nome_item']);
    $quantidade = filter_var($_POST['quantidade'], FILTER_VALIDATE_FLOAT);
    $preco = filter_var($_POST['preco'], FILTER_VALIDATE_FLOAT);
    $post_mercado_id = (int) $_POST['mercado_id'];
    $post_lista_id = (int) $_POST['lista_id'];

    if ($nome_item && $quantidade > 0 && $preco >= 0) {
        $_SESSION['itens_temp'][] = [
            'nome_item' => $nome_item,
            'quantidade' => $quantidade,
            'preco' => $preco,
            'mercado_id' => $post_mercado_id,
            'lista_id' => $post_lista_id
        ];
        header("Location: compras.php?lista_compra_id=$post_lista_id&mercado_id=$post_mercado_id");
        exit;
    } else {
        $_SESSION['mensagem'] = "Quantidade ou preço inválido.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Atualizar item existente (inline)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_index'])) {
    $index = (int) $_POST['editar_index'];
    $nova_quantidade = filter_var($_POST['nova_quantidade'], FILTER_VALIDATE_FLOAT);
    $novo_preco = filter_var($_POST['novo_preco'], FILTER_VALIDATE_FLOAT);

    if (isset($_SESSION['itens_temp'][$index]) && $nova_quantidade > 0 && $novo_preco >= 0) {
        $_SESSION['itens_temp'][$index]['quantidade'] = $nova_quantidade;
        $_SESSION['itens_temp'][$index]['preco'] = $novo_preco;
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Carregar mercados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM mercados WHERE usuario_id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $mercados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar mercados: " . $e->getMessage());
}

// Carregar listas de compras do mercado selecionado
$listas_compras = [];
if ($mercado_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM listas_compras WHERE usuario_id = :usuario_id AND mercado_id = :mercado_id");
        $stmt->execute([
            'usuario_id' => $_SESSION['usuario_id'],
            'mercado_id' => $mercado_id
        ]);
        $listas_compras = $stmt->fetchAll();
    } catch (PDOException $e) {
        die("Erro ao carregar listas: " . $e->getMessage());
    }
}

// Carregar itens temporários para exibição
$compras_dia_atual = $_SESSION['itens_temp'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Lista de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4"> <!-- MODIFICADO -->
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <h2 class="text-center mb-4">Realizar Compras</h2>

                <form method="POST" class="card p-4 shadow-sm bg-white"> <!-- MODIFICADO -->
                    <div class="mb-3">
                        <label for="mercado_id" class="form-label">Mercado</label>
                        <select name="mercado_id" id="mercado_id" class="form-select" required
                            onchange="carregarListas()">
                            <option value="">Selecione</option>
                            <?php foreach ($mercados as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($m['id'] == $mercado_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="listas-container" style="display:<?= $mercado_id ? 'block' : 'none' ?>">
                        <label for="lista_id" class="form-label">Lista de Compras</label>
                        <select name="lista_id" id="lista_id" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($listas_compras as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= ($l['id'] == $lista_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($l['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nome_item" class="form-label">Item</label>
                            <input type="text" name="nome_item" id="nome_item" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="quantidade" class="form-label">Qtd</label>
                            <input type="number" name="quantidade" id="quantidade" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="preco" class="form-label">Preço</label>
                            <input type="number" step="0.01" name="preco" id="preco" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Adicionar Item</button>
                </form>

                <?php if ($lista_id && $mercado_id): ?>
                    <form action="finalizar_compra.php" method="POST" class="mt-3">
                        <input type="hidden" name="finalizar" value="1">
                        <button type="submit" class="btn btn-success w-100">Finalizar Compra</button>
                    </form>
                <?php endif; ?>
                <?php
                // Buscar últimas 5 compras do usuário
                $stmt = $pdo->prepare("
                    SELECT c.id, c.data_compra, m.nome AS mercado_nome
                    FROM compras c
                    JOIN mercados m ON c.mercado_id = m.id
                    WHERE c.usuario_id = :usuario_id
                    ORDER BY c.data_compra DESC
                ");
                $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
                $compras_anteriores = $stmt->fetchAll();
                ?>

                <?php if (count($compras_anteriores) > 0): ?>
                    <div class="mt-4">
                        <h5>Importar Compra Anterior</h5>
                        <div class="input-group mb-3">
                            <select id="compra_anterior" class="form-select">
                                <option value="">Selecione uma compra</option>
                                <?php foreach ($compras_anteriores as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= date('d/m/Y', strtotime($c['data_compra'])) ?> -
                                        <?= htmlspecialchars($c['mercado_nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="importarCompra()">Importar</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-5">
                    <h4>Itens Temporários</h4>
                    <?php $total = 0; ?>
                    <div class="table-responsive"> <!-- MODIFICADO -->
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Qtd</th>
                                    <th>Preço</th>
                                    <th>Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($compras_dia_atual)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum item adicionado</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($compras_dia_atual as $index => $item): ?>
                                        <tr>
                                            <form method="POST">
                                                <input type="hidden" name="editar_index" value="<?= $index ?>">
                                                <td><?= htmlspecialchars($item['nome_item']) ?></td>
                                                <td>
                                                    <input type="number" step="0.01" name="nova_quantidade"
                                                        value="<?= $item['quantidade'] ?>" class="form-control form-control-sm"
                                                        required>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="novo_preco"
                                                        value="<?= $item['preco'] ?>" class="form-control form-control-sm"
                                                        required>
                                                </td>
                                                <td>
                                                    R$ <?= number_format($item['quantidade'] * $item['preco'], 2, ',', '.') ?>
                                                    <?php $total += $item['quantidade'] * $item['preco']; ?>
                                                </td>
                                                <td class="d-flex gap-1">
                                                    <button type="submit" class="btn btn-sm btn-success">Salvar</button>
                                                    <a href="?remover_item=<?= $index ?>" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Remover item?')">Excluir</a>
                                                </td>
                                            </form>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <h5 class="text-end mt-2">Total: R$ <?= number_format($total, 2, ',', '.') ?></h5>
                </div>

                <div class="mt-4">
                    <a href="../index.php" class="btn btn-secondary w-100">Voltar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function carregarListas() {
            const mercadoId = document.getElementById('mercado_id').value;
            const container = document.getElementById('listas-container');
            const listaSelect = document.getElementById('lista_id');
            const listaSelecionada = "<?= $lista_id ?>";

            listaSelect.innerHTML = '<option value="">Carregando...</option>';
            container.style.display = mercadoId ? 'block' : 'none';

            if (mercadoId) {
                fetch(`listas_compras_busca.php?mercado_id=${mercadoId}`)
                    .then(res => res.json())
                    .then(data => {
                        listaSelect.innerHTML = '<option value="">Selecione</option>';
                        data.forEach(lista => {
                            const opt = document.createElement('option');
                            opt.value = lista.id;
                            opt.textContent = lista.nome;
                            if (lista.id == listaSelecionada) {
                                opt.selected = true;
                            }
                            listaSelect.appendChild(opt);
                        });
                    });
            }
        }

        window.onload = function () {
            if (document.getElementById('mercado_id').value) {
                carregarListas();
            }
        }
        function importarCompra() {
            const compraId = document.getElementById('compra_anterior').value;
            if (!compraId) {
                alert('Selecione uma compra para importar.');
                return;
            }

            fetch(`importar_compra.php?compra_id=${compraId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        // Recarregar a página para mostrar os itens importados
                        window.location.reload();
                    } else {
                        alert(data.erro || 'Erro ao importar itens.');
                    }
                })
                .catch(() => {
                    alert('Erro ao importar itens.');
                });
        }

    </script>
</body>

</html>