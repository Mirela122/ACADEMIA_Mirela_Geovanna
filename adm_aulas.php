
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

$mensagem = "";
$tipo_mensagem = "";

$dias = [
    "Segunda-feira",
    "Terça-feira",
    "Quarta-feira",
    "Quinta-feira",
    "Sexta-feira",
    "Sábado",
    "Domingo"
];

// =================================================
// EDITAR AULA
// =================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar"])) {

    $id = (int)($_POST["id_aula"] ?? 0);
    $nome = trim($_POST["nome_aula"] ?? "");
    $dia = $_POST["dia_aula"] ?? "";
    $horario = $_POST["horario_aula"] ?? "";
    $professor = (int)($_POST["id_professor"] ?? 0);

    if (
        $id <= 0 ||
        $nome === "" ||
        mb_strlen($nome, "UTF-8") > 100 ||
        !in_array($dia, $dias, true) ||
        !preg_match('/^\d{2}:\d{2}$/', $horario) ||
        $professor <= 0
    ) {

        $mensagem = "Preencha todos os campos corretamente.";
        $tipo_mensagem = "danger";

    } else {

        $sql = "
            UPDATE Aulas
            SET nome_aula = ?,
                dia_aula = ?,
                horario_aula = ?,
                id_professor = ?
            WHERE id_aula = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "sssii",
            $nome,
            $dia,
            $horario,
            $professor,
            $id
        );

        if ($stmt->execute()) {

            header("Location: adm_aulas.php?sucesso=editar");
            exit;

        } else {

            $mensagem = "Erro ao editar a aula.";
            $tipo_mensagem = "danger";

        }

        $stmt->close();
    }
}

// =================================================
// MENSAGENS
// =================================================

if (isset($_GET["sucesso"])) {

    if ($_GET["sucesso"] === "editar") {
        $mensagem = "Aula atualizada com sucesso!";
        $tipo_mensagem = "success";
    }
}

// =================================================
// CONSULTAS
// =================================================

$resumo = $conexao->query(
    "SELECT COUNT(*) AS total FROM Aulas"
)->fetch_assoc();

$resultado = $conexao->query("
    SELECT
        a.id_aula,
        a.nome_aula,
        a.dia_aula,
        a.horario_aula,
        a.id_professor,
        p.nome_professor

    FROM Aulas a

    INNER JOIN Professor p
        ON a.id_professor = p.id_professor

    ORDER BY
        FIELD(
            a.dia_aula,
            'Segunda-feira',
            'Terça-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado',
            'Domingo'
        ),
        a.horario_aula
");

$professores = $conexao->query("
    SELECT id_professor, nome_professor
    FROM Professor
    ORDER BY nome_professor
");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Aulas | UPIR Administração</title>

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
            color: #cf0139;
            font-size: 25px;
            font-weight: bold;
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

        .campo-admin {
            background-color: #e9ecef;
            border: none;
            padding: 11px;
        }

        .btn-upir {
            background-color: #cf0139;
            color: white;
            border: none;
            font-weight: bold;
        }

        .btn-upir:hover {
            background-color: #a90030;
            color: white;
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
            Controle de aulas
        </h1>

        <p class="text-secondary">
            Consulte e gerencie as aulas da academia.
        </p>

        <a href="adm.php" class="btn btn-voltar btn-sm">
            Voltar ao painel
        </a>

    </div>

    <!-- MENSAGENS -->

    <?php if ($mensagem !== ""): ?>

        <div class="alert alert-<?= escapar($tipo_mensagem) ?> alert-dismissible fade show">

            <?= escapar($mensagem) ?>

            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <!-- RESUMO -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Total de aulas cadastradas
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo["total"] ?>
                </h3>

            </div>

        </div>

    </div>

    <!-- TABELA -->

    <div class="painel-admin">

        <h3 class="subtitulo mb-4">
            Aulas cadastradas
        </h3>

        <div class="table-responsive">

            <table class="table tabela-admin">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Aula</th>
                        <th>Professor</th>
                        <th>Dia</th>
                        <th>Horário</th>
                        <th>Ações</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($resultado && $resultado->num_rows > 0): ?>

                    <?php while ($aula = $resultado->fetch_assoc()): ?>

                        <tr>

                            <td><?= (int)$aula["id_aula"] ?></td>

                            <td><?= escapar($aula["nome_aula"]) ?></td>

                            <td><?= escapar($aula["nome_professor"]) ?></td>

                            <td><?= escapar($aula["dia_aula"]) ?></td>

                            <td><?= escapar(substr($aula["horario_aula"], 0, 5)) ?></td>

                            <td>

                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editar<?= (int)$aula["id_aula"] ?>">

                                    Editar

                                </button>

                            </td>

                        </tr>

                        <!-- MODAL DE EDIÇÃO -->

                        <?php
                        $professores->data_seek(0);
                        ?>

                        <div class="modal fade"
                             id="editar<?= (int)$aula["id_aula"] ?>"
                             tabindex="-1">

                            <div class="modal-dialog">

                                <div class="modal-content"
                                     style="background:#303238;color:#c8d1d9;">

                                    <form method="POST">

                                        <div class="modal-header">

                                            <h5 class="modal-title"
                                                style="color:#cf0139;">

                                                Editar aula

                                            </h5>

                                            <button type="button"
                                                    class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal">
                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <input type="hidden"
                                                   name="id_aula"
                                                   value="<?= (int)$aula["id_aula"] ?>">

                                            <label class="form-label">
                                                Nome da aula
                                            </label>

                                            <input
                                                type="text"
                                                name="nome_aula"
                                                class="form-control campo-admin mb-3"
                                                maxlength="100"
                                                value="<?= escapar($aula["nome_aula"]) ?>"
                                                required
                                            >

                                            <label class="form-label">
                                                Professor
                                            </label>

                                            <select
                                                name="id_professor"
                                                class="form-select campo-admin mb-3"
                                                required>

                                                <?php while ($prof = $professores->fetch_assoc()): ?>

                                                    <option
                                                        value="<?= (int)$prof["id_professor"] ?>"
                                                        <?= (int)$prof["id_professor"] === (int)$aula["id_professor"] ? "selected" : "" ?>>

                                                        <?= escapar($prof["nome_professor"]) ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                            <label class="form-label">
                                                Dia da aula
                                            </label>

                                            <select
                                                name="dia_aula"
                                                class="form-select campo-admin mb-3"
                                                required>

                                                <?php foreach ($dias as $dia): ?>

                                                    <option
                                                        value="<?= escapar($dia) ?>"
                                                        <?= $aula["dia_aula"] === $dia ? "selected" : "" ?>>

                                                        <?= escapar($dia) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                            <label class="form-label">
                                                Horário
                                            </label>

                                            <input
                                                type="time"
                                                name="horario_aula"
                                                class="form-control campo-admin"
                                                value="<?= escapar(substr($aula["horario_aula"], 0, 5)) ?>"
                                                required
                                            >

                                        </div>

                                        <div class="modal-footer">

                                            <button
                                                type="button"
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal">

                                                Cancelar

                                            </button>

                                            <button
                                                type="submit"
                                                name="editar"
                                                class="btn btn-upir">

                                                Salvar alterações

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6" class="text-center">
                            Nenhuma aula cadastrada.
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conexao->close();
?>
