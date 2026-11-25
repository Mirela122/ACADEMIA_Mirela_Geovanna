<?php

session_start();

require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// VERIFICAR LOGIN

if (!isset($_SESSION["id_cliente"])) {
    header("Location: login.php");
    exit;
}

$id_cliente = (int) $_SESSION["id_cliente"];

// FUNÇÕES

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function formatarData($data) {
    if (empty($data)) {
        return "Não informado";
    }

    return date("d/m/Y", strtotime($data));
}

// BUSCAR DADOS DO ALUNO

$sql = "SELECT nome_cliente
        FROM Cliente
        WHERE id_cliente = ?";

$consulta = $conexao->prepare($sql);
$consulta->bind_param("i", $id_cliente);
$consulta->execute();

$aluno = $consulta->get_result()->fetch_assoc();
$consulta->close();

if (!$aluno) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// BUSCAR ASSINATURA MAIS RECENTE

$sql = "SELECT
            a.id_assinatura,
            a.id_plano,
            a.data_inicio,
            a.data_vencimento,
            a.status_assinatura,
            p.valor_plano
        FROM assinatura a
        INNER JOIN Plano p
            ON a.id_plano = p.id_plano
        WHERE a.id_cliente = ?
        ORDER BY a.id_assinatura DESC
        LIMIT 1";

$consulta = $conexao->prepare($sql);
$consulta->bind_param("i", $id_cliente);
$consulta->execute();

$assinatura = $consulta->get_result()->fetch_assoc();
$consulta->close();

// VERIFICAR SE A ASSINATURA ESTÁ VIGENTE

if ($assinatura) {

    $assinatura["ativa"] =
        (bool)$assinatura["status_assinatura"] &&
        (
            empty($assinatura["data_vencimento"]) ||
            $assinatura["data_vencimento"] >= date("Y-m-d")
        );
}

// HISTÓRICO DE ASSINATURAS

$sql = "SELECT
            h.id_historico,
            h.id_assinatura,
            h.id_plano,
            h.data_inicio,
            h.data_cancelamento,
            p.valor_plano
        FROM historico h
        INNER JOIN assinatura a
            ON h.id_assinatura = a.id_assinatura
        INNER JOIN Plano p
            ON h.id_plano = p.id_plano
        WHERE a.id_cliente = ?
        ORDER BY h.data_cancelamento DESC";

$consulta = $conexao->prepare($sql);
$consulta->bind_param("i", $id_cliente);
$consulta->execute();

$historico = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
$consulta->close();

// HISTÓRICO DE PAGAMENTOS

$sql = "SELECT
            pg.valor_pago,
            pg.data_pagamento,
            pg.status_pagamento,
            fp.nome_forma_pagamento
        FROM Pagamento pg
        INNER JOIN assinatura a
            ON pg.id_assinatura = a.id_assinatura
        LEFT JOIN Forma_Pagamento fp
            ON pg.id_forma_pagamento = fp.id_forma_pagamento
        WHERE a.id_cliente = ?
        ORDER BY pg.id_pagamento DESC";

$consulta = $conexao->prepare($sql);
$consulta->bind_param("i", $id_cliente);
$consulta->execute();

$pagamentos = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
$consulta->close();

// HISTÓRICO DE ACESSOS

$sql = "SELECT
            data_acesso,
            horario_acesso,
            status_acesso
        FROM Acesso
        WHERE id_cliente = ?
        ORDER BY data_acesso DESC, horario_acesso DESC";

$consulta = $conexao->prepare($sql);
$consulta->bind_param("i", $id_cliente);
$consulta->execute();

$acessos = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
$consulta->close();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <title>Área do Aluno - Upir Academia</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="index.css">

    <style>

        body {
            background-color: #252529;
            color: #c8d1d9;
        }

        .painel {
            background-color: #c8d1d9;
            color: #252529;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
        }

        .titulo {
            color: #cf0139;
        }

        .card-informacao {
            background-color: #c8d1d9;
            color: #252529;
            border: none;
            border-radius: 15px;
            height: 100%;
        }

        .valor {
            color: #cf0139;
            font-weight: bold;
            font-size: 28px;
        }

        .tabela {
            color: #252529;
        }

        .btn-upir {
            background-color: #cf0139;
            color: white;
            border-radius: 20px;
        }

        .btn-upir:hover {
            background-color: #a80030;
            color: white;
        }

        .secao {
            margin-top: 45px;
            margin-bottom: 45px;
        }

    </style>

</head>

<body>

    <!-- MENU -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">

        <a class="navbar-brand" href="index.html">

            <img
                src="Imagens/icone.png"
                alt="Logo Upir"
                style="max-height: 60px; width: 60px;">

        </a>

    </nav>

    <main class="container py-5">

        <!-- MENSAGEM DE CANCELAMENTO -->

        <?php if (isset($_GET["cancelamento"]) &&
                  $_GET["cancelamento"] === "sucesso"): ?>

            <div class="alert alert-success">
                Seu plano foi cancelado com sucesso!
            </div>

        <?php endif; ?>

        <!-- MENSAGEM DE NOVA ASSINATURA -->

       <?php if (isset($_GET["assinatura"]) &&
          $_GET["assinatura"] === "sucesso"): ?>

        <div class="alert alert-success">
         Seu novo plano foi contratado com sucesso!
       </div>

<?php endif; ?>

        <!-- SAUDAÇÃO -->

        <div class="mb-5">

    <h1 class="titulo">
        Olá, <?= escapar($aluno["nome_cliente"]) ?>!
    </h1>

    <h4>
        Bem-vindo à sua área do aluno UPIR.
    </h4>

    <p>
        Aqui você pode acompanhar seu plano,
        pagamentos e acessos à academia.
    </p>

    <a href="editar_dados.php" class="btn btn-upir mt-3">
        Editar meus dados
    </a>

</div>


        <!-- MEU PLANO -->

        <section class="secao">

            <h2 class="titulo mb-4">Meu plano</h2>

            <?php if ($assinatura): ?>

                <div class="row g-4">

                    <div class="col-md-4">

                        <div class="card card-informacao p-3">

                            <div class="card-body">

                                <h5>Plano contratado</h5>

                                <h2>
                                    Plano <?= (int)$assinatura["id_plano"] ?>
                                </h2>

                                <p class="valor">

                                    R$ <?= number_format(
                                        $assinatura["valor_plano"],
                                        2,
                                        ",",
                                        "."
                                    ) ?>

                                </p>

                                <p>Valor mensal cadastrado</p>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="card card-informacao p-3">

                            <div class="card-body">

                                <h5>Situação da assinatura</h5>

                                <?php if ($assinatura["ativa"]): ?>

                                    <h3 class="text-success">
                                        Ativa
                                    </h3>

                                    <form
                                        action="cancelar_plano.php"
                                        method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja cancelar seu plano?');">

                                        <button
                                            type="submit"
                                            class="btn btn-danger mt-3">

                                            Cancelar meu plano

                                        </button>

                                    </form>

                                <?php else: ?>

                                    <h3 class="text-danger">
                                     Inativa ou vencida
                                    </h3>

                                <a href="nova_assinatura.php" class="btn btn-upir mt-3">
                                    Contratar um novo plano
                                </a>

                                <?php endif; ?>

                                <p class="mt-3">

                                    Assinatura nº
                                    <?= (int)$assinatura["id_assinatura"] ?>

                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="card card-informacao p-3">

                            <div class="card-body">

                                <h5>Datas da assinatura</h5>

                                <p>

                                    <strong>Início:</strong><br>

                                    <?= formatarData(
                                        $assinatura["data_inicio"]
                                    ) ?>

                                </p>

                                <p>

                                    <strong>Vencimento:</strong><br>

                                    <?= formatarData(
                                        $assinatura["data_vencimento"]
                                    ) ?>

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            <?php else: ?>

               <div class="painel">

    <h4>Você ainda não possui um plano.</h4>

    <a href="nova_assinatura.php" class="btn btn-upir mt-3">
        Escolher um plano
    </a>

</div>

            <?php endif; ?>

        </section>


        <!-- HISTÓRICO DE ASSINATURAS -->
         

        <section class="secao">

            <h2 class="titulo mb-4">
                Histórico de assinaturas
            </h2>

            <div class="painel">

                <?php if (count($historico) > 0): ?>

                    <div class="table-responsive">

                        <table class="table table-striped table-hover tabela">

                            <thead>

                                <tr>
                                    <th>Plano</th>
                                    <th>Valor</th>
                                    <th>Data de início</th>
                                    <th>Data de cancelamento</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($historico as $item): ?>

                                    <tr>

                                        <td>
                                            Plano <?= (int)$item["id_plano"] ?>
                                        </td>

                                        <td>

                                            R$ <?= number_format(
                                                $item["valor_plano"],
                                                2,
                                                ",",
                                                "."
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= formatarData(
                                                $item["data_inicio"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= formatarData(
                                                $item["data_cancelamento"]
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <p class="mb-0">
                        Você ainda não possui cancelamentos registrados.
                    </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- HISTÓRICO DE PAGAMENTOS -->

        <section class="secao">

            <h2 class="titulo mb-4">
                Histórico de pagamentos
            </h2>

            <div class="painel">

                <?php if (count($pagamentos) > 0): ?>

                    <div class="table-responsive">

                        <table class="table table-striped table-hover tabela">

                            <thead>

                                <tr>
                                    <th>Valor</th>
                                    <th>Forma de pagamento</th>
                                    <th>Data</th>
                                    <th>Situação</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($pagamentos as $pagamento): ?>

                                    <tr>

                                        <td>

                                            R$ <?= number_format(
                                                $pagamento["valor_pago"],
                                                2,
                                                ",",
                                                "."
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= escapar(
                                                $pagamento["nome_forma_pagamento"]
                                                ?? "Não informado"
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= formatarData(
                                                $pagamento["data_pagamento"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?php if ($pagamento["status_pagamento"]): ?>

                                                <span class="badge bg-success">
                                                    Pago
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-warning text-dark">
                                                    Pendente
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <p class="mb-0">
                        Você ainda não possui pagamentos registrados.
                    </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- HISTÓRICO DE ACESSOS -->

        <section class="secao">

            <h2 class="titulo mb-4">
                Meus acessos à academia
            </h2>

            <div class="painel">

                <p>
                    Confira seu histórico de entradas na UPIR.
                </p>

                <?php if (count($acessos) > 0): ?>

                    <div class="table-responsive">

                        <table class="table table-striped table-hover tabela">

                            <thead>

                                <tr>
                                    <th>Data</th>
                                    <th>Horário</th>
                                    <th>Situação do acesso</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($acessos as $acesso): ?>

                                    <tr>

                                        <td>

                                            <?= formatarData(
                                                $acesso["data_acesso"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= escapar(
                                                $acesso["horario_acesso"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?php if ($acesso["status_acesso"]): ?>

                                                <span class="badge bg-success">
                                                    Acesso autorizado
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-danger">
                                                    Acesso não autorizado
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <p class="mb-0">
                        Você ainda não possui registros de acesso à academia.
                    </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- RODAPÉ -->

        <div class="text-center mt-5">

            <h4 class="titulo">UPIR Academia</h4>

            <p>
                Cuidando da sua saúde e bem-estar!
            </p>

            <a href="index.html" class="btn btn-upir">
                Voltar ao início
            </a>

        </div>

    </main>

</body>
</html>

<?php

$conexao->close();

?>
