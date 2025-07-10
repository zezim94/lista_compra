<?php
include'../config/db.php';

if (isset($_GET['mercado_id'])) {
    $mercado_id = $_GET['mercado_id'];

    // Consultar as listas de compras relacionadas ao mercado
    $query = "
        SELECT id, nome
        FROM listas_compras
        WHERE mercado_id = :mercado_id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['mercado_id' => $mercado_id]);
    $listas_compra = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gerar as opções de listas
    foreach ($listas_compra as $lista) {
        echo "<option value='" . $lista['id'] . "'>" . htmlspecialchars($lista['nome']) . "</option>";
    }
}
?>
