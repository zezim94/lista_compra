<?php
session_start();

// Gerar um número aleatório caso não exista na sessão
if (!isset($_SESSION['numero'])) {
    $_SESSION['numero'] = rand(1, 100);
    $_SESSION['tentativas'] = 0;
}

$mensagem = "";
$emoji = "🔍";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $palpite = (int) $_POST['palpite'];
    $_SESSION['tentativas']++;
    
    if ($palpite < $_SESSION['numero']) {
        $mensagem = "🤔 Tente um número maior!";
    } elseif ($palpite > $_SESSION['numero']) {
        $mensagem = "😯 Tente um número menor!";
    } else {
        $mensagem = "🎉 Parabéns! Você acertou o número em {$_SESSION['tentativas']} tentativas!";
        session_destroy(); // Reiniciar o jogo
    }
}

if (isset($_POST['reiniciar'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo de Adivinhação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            text-align: center;
            background-color: #f8f9fa;
        }
        .game-container {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="game-container w-50 mx-auto">
            <h2>🎯 Adivinhe o Número (1 a 100) 🎯</h2>
            <p>💡 Tente adivinhar o número secreto! Insira um palpite abaixo.</p>
            <form method="post">
                <input type="number" name="palpite" min="1" max="100" required class="form-control w-50 mx-auto mb-2" autofocus>
                <button type="submit" class="btn btn-success">🎲 Enviar Palpite</button>
            </form>
            <p class="mt-3"> <?php echo $mensagem; ?> </p>
            <form method="post">
                <button type="submit" name="reiniciar" class="btn btn-danger">🔄 Reiniciar Jogo</button>
            </form>
        </div>
    </div>
</body>
</html>
