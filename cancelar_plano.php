<?php

session_start();

require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION["id_cliente"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Acesso inválido.");
}

$id_cliente = (int) $_SESSION["id_cliente"];

try {

    $conexao->begin_transaction();

    // Buscar assinatura ativa do cliente
    $sql = "SELECT id_assinatura, id_plano, data_inicio
            FROM assinatura
            WHERE id_cliente = ?
            AND status_assinatura = 1
            ORDER BY id_assinatura DESC
            LIMIT 1
            FOR UPDATE";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();

    $assinatura = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$assinatura) {
        throw new Exception("Nenhuma assinatura ativa encontrada.");
    }

    $id_assinatura = (int) $assinatura["id_assinatura"];
    $id_plano = (int) $assinatura["id_plano"];
    $data_inicio = $assinatura["data_inicio"];

    $data_cancelamento = date("Y-m-d");

    // Registrar no histórico
    $sql = "INSERT INTO historico (
                id_assinatura,
                id_plano,
                data_inicio,
                data_cancelamento
            ) VALUES (?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "iiss",
        $id_assinatura,
        $id_plano,
        $data_inicio,
        $data_cancelamento
    );

    $stmt->execute();
    $stmt->close();

    // Cancelar assinatura
    $sql = "UPDATE assinatura
            SET status_assinatura = 0
            WHERE id_assinatura = ?
            AND id_cliente = ?";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param("ii", $id_assinatura, $id_cliente);

    $stmt->execute();
    $stmt->close();

    $conexao->commit();

    header("Location: area_usuario.php?cancelamento=sucesso");
    exit;

} catch (Throwable $erro) {

    $conexao->rollback();

    error_log($erro->getMessage());

    exit("Não foi possível cancelar sua assinatura.");
}

$conexao->close();
?>
