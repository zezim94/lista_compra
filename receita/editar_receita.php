<?php session_start(); include('db.php');
if (!isset($_SESSION['usuario_id'])) header('Location: login.php');

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM receitas WHERE id=:id AND usuario_id=:u");
$stmt->execute(['id'=>$id, 'u'=>$_SESSION['usuario_id']]);
$r = $stmt->fetch();
if (!$r) exit('Receita não encontrada.');

if ($_SERVER['REQUEST_METHOD']=='POST'){
  $nome = trim($_POST['nome']);
  if ($nome){
    $pdo->prepare("UPDATE receitas SET nome=:n WHERE id=:id")->execute(['n'=>$nome,'id'=>$id]);
    header("Location: listar_receitas.php"); exit;
  }
}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Editar Receita</title></head><body>
<div class="container mt-5">
  <h2>Editar Receita</h2>
  <form method="POST">
    <div class="mb-3"><label>Nome</label><input name="nome" class="form-control" value="<?=htmlspecialchars($r['nome'])?>" required></div>
    <button class="btn btn-primary">Salvar</button>
    <a href="listar_receitas.php" class="btn btn-secondary">Voltar</a>
  </form>
</div></body></html>
