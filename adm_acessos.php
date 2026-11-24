
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
// RESUMO DOS ACESSOS
// =================================================

$sql_resumo = "
    SELECT
        COUNT(*) AS total,

        SUM(CASE
            WHEN status_acesso = 1 THEN 1
            ELSE 0
        END) AS autorizados,

        SUM(CASE
            WHEN status_acesso = 0 THEN 1
            ELSE 0
        END) AS bloqueados

    FROM Acesso
";

$resumo = $conexao->query($sql_resumo)->fetch_assoc();

// =================================================
// CONSULTA DOS ACESSOS
// =================================================

$sql = "
    SELECT
        a.id_acesso,
        a.data_acesso,
        a.horario_acesso,
        a.status_acesso,

        c.nome_cliente,
        c.cpf_cliente,

        ass.status_assinatura,
        ass.data_vencimento

    FROM Acesso a

    INNER JOIN Cliente c
        ON a.id_cliente = c.id_cliente

    INNER JOIN assinatura ass
        ON a.id_assinatura = ass.id_assinatura

    ORDER BY
        a.data_acesso DESC,
        a.horario_acesso DESC
";

$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Acessos | UPIR Administração</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="adm.css">

    <style>

        .titulo-pagina,
        .subtitulo {
            color: #cf0139;
            font-weight: bold;
        }

        .painel-admin,
        .card-resumo {
            background-color: #303238;
            border: 1px solid #45474d;
            border-radius: 15px;
            padding: 25px;
        }

        .card-resumo {
            height: 100%;
        }

        .valor-resumo {
            font-size: 25px;
            font-weight: bold;
            color: #cf0139;
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

        .status-autorizado {
            background-color: #198754;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-bloqueado {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-ativo {
            color: #20c997;
            font-weight: bold;
        }

        .status-inativo {
            color: #ffc107;
            font-weight: bold;
        }

        .btn-voltar {
            color: #c8d1d9;
            border-color: #c8d1d9;
        }

        .btn-voltar:hover {
            background-color: #cf0139;
            color: white;
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
            Controle de acessos
        </h1>

        <p class="text-secondary">
            Consulte os registros de entrada dos alunos na academia.
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
                    Total de registros
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["total"] ?>
                </h3>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Acessos autorizados
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["autorizados"] ?>
                </h3>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Acessos bloqueados
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["bloqueados"] ?>
                </h3>

            </div>

        </div>

    </div>

    <!-- TABELA DE ACESSOS -->

    <div class="painel-admin">

        <h3 class="subtitulo mb-4">
            Histórico de acessos
        </h3>

        <div class="table-responsive">

            <table class="table tabela-admin">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Cliente</th>
                        <th>CPF</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Situação do acesso</th>
                        <th>Assinatura</th>
                        <th>Vencimento</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if ($resultado && $resultado->num_rows > 0): ?>

                        <?php while ($acesso = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= (int)$acesso["id_acesso"] ?>
                                </td>

                                <td>
                                    <?= escapar($acesso["nome_cliente"]) ?>
                                </td>

                                <td>
                                    <?= escapar($acesso["cpf_cliente"]) ?>
                                </td>

                                <td>
                                    <?= date("d/m/Y", strtotime($acesso["data_acesso"])) ?>
                                </td>

                                <td>
                                    <?= escapar(substr($acesso["horario_acesso"], 0, 5)) ?>
                                </td>

                                <td>

                                    <?php if ($acesso["status_acesso"] == 1): ?>

                                        <span class="status-autorizado">
                                            Autorizado
                                        </span>

                                    <?php else: ?>

                                        <span class="status-bloqueado">
                                            Bloqueado
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($acesso["status_assinatura"] == 1): ?>

                                        <span class="status-ativo">
                                            Ativa
                                        </span>

                                    <?php else: ?>

                                        <span class="status-inativo">
                                            Inativa
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?= $acesso["data_vencimento"]
                                        ? date("d/m/Y", strtotime($acesso["data_vencimento"]))
                                        : "Não informado" ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8" class="text-center py-4">
                                Nenhum registro de acesso encontrado.
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
