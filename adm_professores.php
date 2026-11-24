
<?php

session_start();

if (!isset($_SESSION["id_adm"])) {
    header("Location: adm_login.php");
    exit;
}

require_once "conexao.php";

// CONSULTAR PROFESSORES E SUAS FUNÇÕES

$sql = "SELECT
            p.id_professor,
            p.nome_professor,
            p.telefone_professor,
            p.sexo_professor,
            p.email_professor,
            p.cpf_professor,
            p.data_de_nascimento_professor,
            f.nome_funcao
        FROM Professor p
        INNER JOIN Funcao_Professor f
        ON p.id_funcao = f.id_funcao
        ORDER BY p.nome_professor";

$resultado = $conexao->query($sql);

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Professores | UPIR Administração</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="adm.css">

    <style>

        .titulo-pagina {
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

        .btn-voltar {
            color: #c8d1d9;
            border-color: #c8d1d9;
        }

        .btn-voltar:hover {
            background-color: #cf0139;
            color: white;
            border-color: #cf0139;
        }

        @media(max-width: 576px) {

            .painel-admin {
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
            Professores
        </h1>

        <p class="text-secondary">
            Consulte as informações dos professores cadastrados na UPIR.
        </p>

        <a href="adm.php" class="btn btn-voltar btn-sm">
            Voltar ao painel
        </a>

    </div>

    <!-- LISTAGEM -->

    <div class="painel-admin">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <h3 class="titulo-pagina mb-0">
                Professores cadastrados
            </h3>

            <span class="text-secondary">
                Total:
                <?= $resultado->num_rows ?>
            </span>

        </div>

        <div class="table-responsive">

            <table class="table tabela-admin">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Sexo</th>
                        <th>Nascimento</th>
                        <th>Função</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if ($resultado->num_rows > 0) { ?>

                        <?php while ($professor = $resultado->fetch_assoc()) { ?>

                            <tr>

                                <td>
                                    <?= escapar($professor["id_professor"]) ?>
                                </td>

                                <td>
                                    <?= escapar($professor["nome_professor"]) ?>
                                </td>

                                <td>
                                    <?= escapar($professor["cpf_professor"]) ?>
                                </td>

                                <td>
                                    <?= escapar($professor["telefone_professor"]) ?>
                                </td>

                                <td>
                                    <?= escapar($professor["email_professor"]) ?>
                                </td>

                                <td>
                                    <?= escapar($professor["sexo_professor"]) ?>
                                </td>

                                <td>
                                    <?= date(
                                        "d/m/Y",
                                        strtotime($professor["data_de_nascimento_professor"])
                                    ) ?>
                                </td>

                                <td>
                                    <?= escapar($professor["nome_funcao"]) ?>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="8" class="text-center py-4">

                                Nenhum professor cadastrado.

                            </td>

                        </tr>

                    <?php } ?>

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
