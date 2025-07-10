<?php
session_start();
include'../config/db.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_receita = trim($_POST['nome_receita']);
    $ingredientes = $_POST['ingredientes'] ?? [];

    if (!$nome_receita || count($ingredientes) === 0) {
        $error = "Preencha o nome e ao menos um ingrediente.";
    } else {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO receitas (usuario_id, nome, data_criacao)
                               VALUES (:uid, :nome, NOW())");
        $stmt->execute([
            ':uid'  => $usuario_id,
            ':nome' => $nome_receita
        ]);
        $receita_id = $pdo->lastInsertId();

        $stmtIngr = $pdo->prepare("INSERT INTO ingredientes_receita
                                   (receita_id, nome_ingrediente, quantidade, preco)
                                   VALUES (:rid, :nome, :qtd, :preco)");

        foreach ($ingredientes as $ing) {
            if (!$ing['nome'] || !is_numeric($ing['quantidade']) || !is_numeric($ing['preco'])) {
                continue;
            }

            $stmtIngr->execute([
                ':rid'        => $receita_id,
                ':nome'       => trim($ing['nome']),
                ':qtd'        => (float)$ing['quantidade'],
                ':preco'      => (float)$ing['preco']
            ]);
        }

        $pdo->commit();

        header('Location: listar_receitas.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Receita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ingrediente { margin-bottom: 15px; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Criar Receita</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" id="form-receita">
            <div class="mb-3">
                <label for="nome_receita" class="form-label">Nome da Receita</label>
                <input type="text" class="form-control" id="nome_receita" name="nome_receita" required>
            </div>
            <h4>Ingredientes</h4>
            <div id="area-ingredientes"></div>
            <button type="button" id="add-ingrediente" class="btn btn-secondary mb-3">+ Ingrediente</button>
            <div class="mb-3">
                <strong>Total da Receita: R$ <span id="total-receita">0,00</span></strong>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Receita</button>
            <a href="listar_receitas.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script>
    function formatBRL(v) {
        return parseFloat(v).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
    }

    let cont = 0;
    const area = document.getElementById('area-ingredientes');
    const totalSpan = document.getElementById('total-receita');

    document.getElementById('add-ingrediente').addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'ingrediente row';
        div.innerHTML = `
            <div class="col-md-5"><input name="ingredientes[${cont}][nome]" placeholder="Nome" class="form-control" required></div>
            <div class="col-md-2"><input type="number" name="ingredientes[${cont}][quantidade]" placeholder="Qtd" class="form-control qtd" required></div>
            <div class="col-md-2"><input type="number" step="0.01" name="ingredientes[${cont}][preco]" placeholder="Preço" class="form-control preco" required></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger remover">–</button></div>
        `;
        area.appendChild(div);
        cont++;
        updateEvents();
    });

    function updateEvents() {
        area.querySelectorAll('.qtd, .preco').forEach(el => {
            el.oninput = calcularTotal;
        });
        area.querySelectorAll('.remover').forEach(btn => {
            btn.onclick = () => { btn.closest('.ingrediente').remove(); calcularTotal(); };
        });
    }

    function calcularTotal() {
        let total = 0;
        area.querySelectorAll('.ingrediente').forEach(row => {
            const qtd = parseFloat(row.querySelector('.qtd').value) || 0;
            const preco = parseFloat(row.querySelector('.preco').value) || 0;
            total += qtd * preco;
        });
        totalSpan.textContent = formatBRL(total);
    }

    // Adiciona inicialmente um ingrediente
    document.getElementById('add-ingrediente').click();
    </script>
</body>
</html>
