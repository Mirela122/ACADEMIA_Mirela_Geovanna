
<?php

session_start();

if (!isset($_SESSION["id_adm"])) {
    header("Location: adm_login.php");
    exit;
}

require_once "conexao.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// =================================================
// CONSULTAR PAGAMENTOS
// =================================================

$sql = "
    SELECT
        pg.id_pagamento,
        pg.valor_pago,
        pg.data_pagamento,
        pg.status_pagamento,

        fp.nome_forma_pagamento,

        a.id_assinatura,
        a.id_plano,

        c.nome_cliente,
        c.email_cliente,
        c.cpf_cliente,

        p.valor_plano

    FROM Pagamento pg

    INNER JOIN Forma_Pagamento fp
        ON pg.id_forma_pagamento = fp.id_forma_pagamento

    INNER JOIN assinatura a
        ON pg.id_assinatura = a.id_assinatura

    INNER JOIN Cliente c
        ON a.id_cliente = c.id_cliente

    INNER JOIN Plano p
        ON a.id_plano = p.id_plano

    ORDER BY
        pg.data_pagamento DESC,
        pg.id_pagamento DESC
";

$resultado = $conexao->query($sql);

// =================================================
// RESUMO FINANCEIRO
// =================================================

$sql_resumo = "
    SELECT

        COUNT(*) AS total_pagamentos,

        SUM(
            CASE WHEN status_pagamento = 1
            THEN 1 ELSE 0 END
        ) AS pagos,

        SUM(
            CASE WHEN status_pagamento = 0
            THEN 1 ELSE 0 END
        ) AS pendentes,

        COALESCE(
            SUM(
                CASE WHEN status_pagamento = 1
                THEN valor_pago ELSE 0 END
            ), 0
        ) AS total_pago,

        COALESCE(
            SUM(
                CASE WHEN status_pagamento = 0
                THEN valor_pago ELSE 0 END
            ), 0
        ) AS total_pendente

    FROM Pagamento
";

$resumo = $conexao->query($sql_resumo)->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pagamentos | UPIR Administração</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="adm.css">

    <style>

        .titulo-pagina,
        .subtitulo {
            color: #cf0139;
            font-weight: bold;
        }

        .painel-admin {
            background-color: #303238;
            border: 1px solid #45474d;
            border-radius: 15px;
            padding: 25px;
        }

        .tabela-admin {
            color: #c8d1d9;
            vertical-align: middle;
        }

        .tabela-admin th {
            color: #cf0139;
            white-space: nowrap;
        }

        .tabela-admin td {
            border-color: #45474d;
        }

        .tabela-admin tbody tr:hover {
            background-color: #3b3439;
        }

        .card-resumo {
            background-color: #303238;
            border: 1px solid #45474d;
            border-radius: 15px;
            padding: 22px;
            height: 100%;
        }

        .card-resumo h3 {
            color: #cf0139;
            font-weight: bold;
        }

        .valor-resumo {
            font-size: 23px;
            font-weight: bold;
            color: #c8d1d9;
        }

        .btn-voltar {
            color: #c8d1d9;
            border-color: #c8d1d9;
        }

        .btn-voltar:hover {
            background-color: #cf0139;
            color: white;
        }

        .status-pago {
            background-color: #198754;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-pendente {
            background-color: #ffc107;
            color: #212529;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        @media(max-width:576px) {

            .painel-admin,
            .card-resumo {
                padding: 15px;
            }

        }

    </style>

</head>

<body>

<!-- MENU -->

<nav class="navbar navbar-dark bg-dark px-4">

    <a class="navbar-brand fw-bold" href="adm.php">
        UPIR | ADMINISTRAÇÃO
    </a>

    <a href="adm_sair.php" class="btn btn-outline-light">
        Sair
    </a>

</nav>

<main class="container my-5">

    <!-- CABEÇALHO -->

    <div class="mb-4">

        <h1 class="titulo-pagina">
            Pagamentos
        </h1>

        <p class="text-secondary">
            Consulte os pagamentos e acompanhe a situação financeira dos clientes.
        </p>

        <a href="adm.php" class="btn btn-voltar btn-sm">
            Voltar ao painel
        </a>

    </div>

    <!-- RESUMO -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Total de pagamentos
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["total_pagamentos"] ?>
                </h3>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Pagamentos realizados
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["pagos"] ?>
                </h3>

                <p class="text-secondary mb-0">
                    R$ <?= number_format(
                        (float)$resumo["total_pago"],
                        2,
                        ",",
                        "."
                    ) ?>
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Pagamentos pendentes
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["pendentes"] ?>
                </h3>

                <p class="text-secondary mb-0">
                    R$ <?= number_format(
                        (float)$resumo["total_pendente"],
                        2,
                        ",",
                        "."
                    ) ?>
                </p>

            </div>

        </div>

    </div>

    <!-- TABELA DE PAGAMENTOS -->

    <div class="painel-admin">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <h3 class="subtitulo mb-0">
                Histórico de pagamentos
            </h3>

            <span class="text-secondary">
                Registros: <?= (int)$resumo["total_pagamentos"] ?>
            </span>

        </div>

        <div class="table-responsive">

            <table class="table tabela-admin">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Cliente</th>
                        <th>CPF</th>
                        <th>Assinatura</th>
                        <th>Plano</th>
                        <th>Valor pago</th>
                        <th>Forma de pagamento</th>
                        <th>Data</th>
                        <th>Situação</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if ($resultado->num_rows > 0): ?>

                        <?php while ($pagamento = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= (int)$pagamento["id_pagamento"] ?>
                                </td>

                                <td>

                                    <?= escapar($pagamento["nome_cliente"]) ?>

                                    <br>

                                    <small class="text-secondary">
                                        <?= escapar($pagamento["email_cliente"]) ?>
                                    </small>

                                </td>

                                <td>
                                    <?= escapar($pagamento["cpf_cliente"]) ?>
                                </td>

                                <td>
                                    #<?= (int)$pagamento["id_assinatura"] ?>
                                </td>

                                <td>
                                    Plano #<?= (int)$pagamento["id_plano"] ?>
                                </td>

                                <td>
                                    R$ <?= number_format(
                                        (float)$pagamento["valor_pago"],
                                        2,
                                        ",",
                                        "."
                                    ) ?>
                                </td>

                                <td>
                                    <?= escapar($pagamento["nome_forma_pagamento"]) ?>
                                </td>

                                <td>

                                    <?php if (!empty($pagamento["data_pagamento"])): ?>

                                        <?= date(
                                            "d/m/Y",
                                            strtotime($pagamento["data_pagamento"])
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-warning">
                                            Aguardando pagamento
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($pagamento["status_pagamento"] == 1): ?>

                                        <span class="status-pago">
                                            Pago
                                        </span>

                                    <?php else: ?>

                                        <span class="status-pendente">
                                            Pendente
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="9" class="text-center py-4">
                                Nenhum pagamento cadastrado.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<footer class="text-center py-4">

    <p class="text-secondary">
        UPIR Academia | Área administrativa
    </p>

</footer>

</body>
</html>
