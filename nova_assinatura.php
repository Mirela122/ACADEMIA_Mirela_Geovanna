
<?php

session_start();

require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// VERIFICAR LOGIN

if (!isset($_SESSION["id_cliente"])) {
    header("Location: login.php");
    exit;
}

$id_cliente = (int)$_SESSION["id_cliente"];

$mensagem = "";

// VERIFICAR SE JÁ POSSUI PLANO ATIVO

$stmt = $conexao->prepare("
    SELECT id_assinatura
    FROM assinatura
    WHERE id_cliente = ?
    AND status_assinatura = 1
    AND (
        data_vencimento IS NULL
        OR data_vencimento >= CURDATE()
    )
    LIMIT 1
");

$stmt->bind_param("i", $id_cliente);
$stmt->execute();

$possui_ativa = $stmt->get_result()->num_rows > 0;

$stmt->close();

// PROCESSAR NOVA ASSINATURA

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$possui_ativa) {

    $id_plano = filter_input(
        INPUT_POST,
        "plano",
        FILTER_VALIDATE_INT
    );

    $id_forma = filter_input(
        INPUT_POST,
        "pagamento",
        FILTER_VALIDATE_INT
    );

    try {

        $conexao->begin_transaction();

        // BLOQUEAR O CLIENTE DURANTE A CONTRATAÇÃO

        $stmt = $conexao->prepare("
            SELECT id_cliente
            FROM Cliente
            WHERE id_cliente = ?
            FOR UPDATE
        ");

        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        $cliente = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$cliente) {
            throw new Exception("Cliente não encontrado.");
        }

        // CONFERIR NOVAMENTE SE EXISTE PLANO ATIVO

        $stmt = $conexao->prepare("
            SELECT id_assinatura
            FROM assinatura
            WHERE id_cliente = ?
            AND status_assinatura = 1
            AND (
                data_vencimento IS NULL
                OR data_vencimento >= CURDATE()
            )
            LIMIT 1
        ");

        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        $possui_ativa = $stmt->get_result()->num_rows > 0;

        $stmt->close();

        if ($possui_ativa) {
            throw new Exception(
                "Você já possui um plano ativo. Não é possível contratar outro."
            );
        }

        if (!$id_plano || !$id_forma) {
            throw new Exception("Selecione um plano e uma forma de pagamento.");
        }

        // VERIFICAR SE O PLANO ESTÁ ATIVO

        $stmt = $conexao->prepare("
            SELECT id_plano, valor_plano
            FROM Plano
            WHERE id_plano = ?
            AND status_plano = 1
        ");

        $stmt->bind_param("i", $id_plano);
        $stmt->execute();

        $plano = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$plano) {
            throw new Exception("O plano selecionado não está disponível.");
        }

        // VERIFICAR SE A FORMA DE PAGAMENTO ESTÁ ATIVA

        $stmt = $conexao->prepare("
            SELECT id_forma_pagamento
            FROM Forma_Pagamento
            WHERE id_forma_pagamento = ?
            AND status_forma_pagamento = 1
        ");

        $stmt->bind_param("i", $id_forma);
        $stmt->execute();

        $forma = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$forma) {
            throw new Exception("Forma de pagamento indisponível.");
        }

        // CADASTRAR NOVA ASSINATURA ATIVA

        $stmt = $conexao->prepare("
            INSERT INTO assinatura (
                id_cliente,
                id_plano,
                data_inicio,
                data_vencimento,
                status_assinatura
            )
            VALUES (
                ?,
                ?,
                CURDATE(),
                DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                1
            )
        ");

        $stmt->bind_param("ii", $id_cliente, $id_plano);

        $stmt->execute();

        // PEGAR ID DA NOVA ASSINATURA

        $id_assinatura = $conexao->insert_id;

        $stmt->close();

        // REGISTRAR PAGAMENTO COMO PAGO

        $valor = (float)$plano["valor_plano"];

        $stmt = $conexao->prepare("
            INSERT INTO Pagamento (
                valor_pago,
                id_forma_pagamento,
                id_assinatura,
                data_pagamento,
                status_pagamento
            )
            VALUES (?, ?, ?, CURDATE(), 1)
        ");

        $stmt->bind_param(
            "dii",
            $valor,
            $id_forma,
            $id_assinatura
        );

        $stmt->execute();

        $stmt->close();

        // CONFIRMAR OPERAÇÃO

        $conexao->commit();

        header("Location: area_usuario.php?assinatura=sucesso");
        exit;

    } catch (Throwable $e) {

        $conexao->rollback();

        $mensagem = $e->getMessage();
    }
}

// BUSCAR PLANOS ATIVOS

$planos = $conexao->query("
    SELECT id_plano, valor_plano
    FROM Plano
    WHERE status_plano = 1
    ORDER BY id_plano
");

// BUSCAR FORMAS DE PAGAMENTO ATIVAS

$formas = $conexao->query("
    SELECT id_forma_pagamento, nome_forma_pagamento
    FROM Forma_Pagamento
    WHERE status_forma_pagamento = 1
    ORDER BY id_forma_pagamento
");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Nova assinatura | UPIR Academia</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            background-color: #252529;
            color: #c8d1d9;
        }

        .container-assinatura {
            max-width: 650px;
            margin: 70px auto;
            padding: 35px;
            background-color: #303238;
            border-radius: 18px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }

        .titulo {
            color: #cf0139;
            font-weight: bold;
        }

        .form-label {
            color: #c8d1d9;
            font-weight: 500;
        }

        .form-select {
            background-color: #e9ecef;
            border: none;
            border-radius: 8px;
            padding: 12px;
        }

        .btn-upir {
            background-color: #cf0139;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: bold;
        }

        .btn-upir:hover {
            background-color: #a90030;
            color: white;
        }

    </style>

</head>

<body>

    <main class="container">

        <div class="container-assinatura">

            <h1 class="titulo text-center mb-3">
                Escolha seu novo plano
            </h1>

            <p class="text-center mb-4">
                Escolha seu plano e continue treinando na UPIR!
            </p>

            <?php if ($mensagem !== ""): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars(
                        $mensagem,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if ($possui_ativa): ?>

                <div class="alert alert-warning">

                    Você já possui um plano ativo.
                    Não é possível contratar outro plano enquanto
                    sua assinatura estiver vigente.

                </div>

                <a href="area_usuario.php" class="btn btn-upir w-100">
                    Voltar para minha área
                </a>

            <?php elseif ($planos->num_rows === 0): ?>

                <div class="alert alert-warning">
                    Não existem planos disponíveis no momento.
                </div>

                <a href="aluno.php" class="btn btn-upir w-100">
                    Voltar
                </a>

            <?php elseif ($formas->num_rows === 0): ?>

                <div class="alert alert-warning">
                    Não existem formas de pagamento disponíveis.
                </div>

                <a href="area_usuario.php" class="btn btn-upir w-100">
                    Voltar
                </a>

            <?php else: ?>

                <form method="POST">

                    <div class="mb-4">

                        <label for="plano" class="form-label">
                            Escolha seu plano
                        </label>

                        <select
                            name="plano"
                            id="plano"
                            class="form-select"
                            required>

                            <option value="">
                                Selecione um plano
                            </option>

                            <?php while ($plano = $planos->fetch_assoc()): ?>

                                <option value="<?= (int)$plano["id_plano"] ?>">

                                    Plano <?= (int)$plano["id_plano"] ?>

                                    - R$ <?= number_format(
                                        (float)$plano["valor_plano"],
                                        2,
                                        ",",
                                        "."
                                    ) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="mb-4">

                        <label for="pagamento" class="form-label">
                            Forma de pagamento
                        </label>

                        <select
                            name="pagamento"
                            id="pagamento"
                            class="form-select"
                            required>

                            <option value="">
                                Selecione uma forma de pagamento
                            </option>

                            <?php while ($forma = $formas->fetch_assoc()): ?>

                                <option value="<?= (int)$forma["id_forma_pagamento"] ?>">

                                    <?= htmlspecialchars(
                                        $forma["nome_forma_pagamento"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="alert alert-info">

                        Ao confirmar, sua assinatura será ativada
                        imediatamente e o pagamento será registrado
                        como realizado.

                    </div>

                    <button
                        type="submit"
                        class="btn btn-upir w-100">

                        Confirmar contratação

                    </button>

                    <a
                        href="aluno.php"
                        class="btn btn-outline-light w-100 mt-3">

                        Voltar para minha área

                    </a>

                </form>

            <?php endif; ?>

        </div>

    </main>

</body>
</html>

<?php
$conexao->close();
?>
