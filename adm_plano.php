
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

if (empty($_SESSION["token"])) {
    $_SESSION["token"] = bin2hex(random_bytes(32));
}

$token = $_SESSION["token"];

// =================================================
// CADASTRAR PLANO
// =================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cadastrar_plano"])) {

    $valor = trim($_POST["valor_plano"] ?? "");
    $valor = str_replace(",", ".", $valor);

    if (
        !hash_equals($token, $_POST["token"] ?? "") ||
        !is_numeric($valor) ||
        (float)$valor <= 0 ||
        (float)$valor > 999999.99
    ) {

        $mensagem = "Informe um valor válido para o plano.";
        $tipo_mensagem = "danger";

    } else {

        $stmt = $conexao->prepare("
            INSERT INTO Plano (valor_plano)
            VALUES (?)
        ");

        $valor = (float)$valor;
        $stmt->bind_param("d", $valor);

        if ($stmt->execute()) {
            header("Location: adm_plano_beneficio.php?sucesso=plano");
            exit;
        }

        $mensagem = "Erro ao cadastrar o plano.";
        $tipo_mensagem = "danger";
    }
}

// =================================================
// CADASTRAR BENEFÍCIO
// =================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cadastrar_beneficio"])) {

    $descricao = trim($_POST["descricao_beneficio"] ?? "");

    if (
        !hash_equals($token, $_POST["token"] ?? "") ||
        $descricao === "" ||
        mb_strlen($descricao, "UTF-8") > 500
    ) {

        $mensagem = "Informe uma descrição válida para o benefício.";
        $tipo_mensagem = "danger";

    } else {

        $stmt = $conexao->prepare("
            INSERT INTO Beneficio (descricao_beneficio)
            VALUES (?)
        ");

        $stmt->bind_param("s", $descricao);

        if ($stmt->execute()) {
            header("Location: adm_plano_beneficio.php?sucesso=beneficio");
            exit;
        }

        $mensagem = "Erro ao cadastrar o benefício.";
        $tipo_mensagem = "danger";
    }
}

// =================================================
// ASSOCIAR BENEFÍCIO AO PLANO
// =================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["associar"])) {

    $id_plano = (int)($_POST["id_plano"] ?? 0);
    $id_beneficio = (int)($_POST["id_beneficio"] ?? 0);

    if (
        !hash_equals($token, $_POST["token"] ?? "") ||
        $id_plano <= 0 ||
        $id_beneficio <= 0
    ) {

        $mensagem = "Dados inválidos. Tente novamente.";
        $tipo_mensagem = "danger";

    } else {

        $stmt = $conexao->prepare("
            SELECT id_plano
            FROM Plano
            WHERE id_plano = ?
        ");

        $stmt->bind_param("i", $id_plano);
        $stmt->execute();

        $plano_existe = $stmt->get_result()->num_rows > 0;

        $stmt = $conexao->prepare("
            SELECT id_beneficio
            FROM Beneficio
            WHERE id_beneficio = ?
        ");

        $stmt->bind_param("i", $id_beneficio);
        $stmt->execute();

        $beneficio_existe = $stmt->get_result()->num_rows > 0;

        if (!$plano_existe || !$beneficio_existe) {

            $mensagem = "Plano ou benefício não encontrado.";
            $tipo_mensagem = "danger";

        } else {

            $stmt = $conexao->prepare("
                SELECT id_plano_beneficio
                FROM Plano_Beneficio
                WHERE id_plano = ?
                AND id_beneficio = ?
            ");

            $stmt->bind_param("ii", $id_plano, $id_beneficio);
            $stmt->execute();

            if ($stmt->get_result()->num_rows > 0) {

                $mensagem = "Este benefício já está associado a esse plano.";
                $tipo_mensagem = "warning";

            } else {

                $stmt = $conexao->prepare("
                    INSERT INTO Plano_Beneficio
                    (id_plano, id_beneficio)
                    VALUES (?, ?)
                ");

                $stmt->bind_param("ii", $id_plano, $id_beneficio);

                if ($stmt->execute()) {
                    header("Location: adm_plano_beneficio.php?sucesso=associar");
                    exit;
                }

                $mensagem = "Erro ao associar o benefício.";
                $tipo_mensagem = "danger";
            }
        }
    }
}

// =================================================
// REMOVER ASSOCIAÇÃO
// =================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remover"])) {

    $id = (int)($_POST["id_plano_beneficio"] ?? 0);

    if (
        !hash_equals($token, $_POST["token"] ?? "") ||
        $id <= 0
    ) {

        $mensagem = "Dados inválidos.";
        $tipo_mensagem = "danger";

    } else {

        $stmt = $conexao->prepare("
            DELETE FROM Plano_Beneficio
            WHERE id_plano_beneficio = ?
        ");

        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            header("Location: adm_plano_beneficio.php?sucesso=remover");
            exit;
        }

        $mensagem = "Não foi possível remover a associação.";
        $tipo_mensagem = "danger";
    }
}

// =================================================
// MENSAGENS
// =================================================

if (isset($_GET["sucesso"])) {

    $mensagens = [
        "plano" => ["Plano cadastrado com sucesso!", "success"],
        "beneficio" => ["Benefício cadastrado com sucesso!", "success"],
        "associar" => ["Benefício associado ao plano com sucesso!", "success"],
        "remover" => ["Associação removida com sucesso!", "success"]
    ];

    if (isset($mensagens[$_GET["sucesso"]])) {
        [$mensagem, $tipo_mensagem] = $mensagens[$_GET["sucesso"]];
    }
}

// =================================================
// CONSULTAS
// =================================================

$resumo_planos = $conexao->query("
    SELECT COUNT(*) AS total FROM Plano
")->fetch_assoc();

$resumo_beneficios = $conexao->query("
    SELECT COUNT(*) AS total FROM Beneficio
")->fetch_assoc();

$resumo_associacoes = $conexao->query("
    SELECT COUNT(*) AS total FROM Plano_Beneficio
")->fetch_assoc();

$planos = $conexao->query("
    SELECT id_plano, valor_plano
    FROM Plano
    ORDER BY id_plano ASC
");

$beneficios = $conexao->query("
    SELECT id_beneficio, descricao_beneficio
    FROM Beneficio
    ORDER BY id_beneficio ASC
");

$resultado = $conexao->query("
    SELECT
        pb.id_plano_beneficio,
        p.id_plano,
        p.valor_plano,
        b.id_beneficio,
        b.descricao_beneficio

    FROM Plano_Beneficio pb

    INNER JOIN Plano p
        ON pb.id_plano = p.id_plano

    INNER JOIN Beneficio b
        ON pb.id_beneficio = b.id_beneficio

    ORDER BY p.id_plano, b.id_beneficio
");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Planos e Benefícios | UPIR Administração</title>

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
            Planos e benefícios
        </h1>

        <p class="text-secondary">
            Cadastre planos, benefícios e gerencie suas associações.
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
                    Total de planos
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo_planos["total"] ?>
                </h3>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Total de benefícios
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo_beneficios["total"] ?>
                </h3>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-resumo">

                <p class="text-secondary mb-2">
                    Benefícios associados
                </p>

                <h3 class="valor-resumo">
                    <?= (int)$resumo_associacoes["total"] ?>
                </h3>

            </div>

        </div>

    </div>

    <!-- CADASTROS -->

    <div class="row g-4 mb-4">

        <!-- CADASTRAR PLANO -->

        <div class="col-md-6">

            <div class="painel-admin h-100">

                <h3 class="subtitulo mb-3">
                    Cadastrar plano
                </h3>

                <form method="POST">

                    <input type="hidden" name="token"
                           value="<?= escapar($token) ?>">

                    <label class="form-label">
                        Valor do plano (R$)
                    </label>

                    <input
                        type="number"
                        name="valor_plano"
                        class="form-control campo-admin mb-3"
                        placeholder="Ex: 99.90"
                        min="0.01"
                        max="999999.99"
                        step="0.01"
                        required
                    >

                    <button type="submit"
                            name="cadastrar_plano"
                            class="btn btn-upir w-100">

                        Cadastrar plano

                    </button>

                </form>

            </div>

        </div>

        <!-- CADASTRAR BENEFÍCIO -->

        <div class="col-md-6">

            <div class="painel-admin h-100">

                <h3 class="subtitulo mb-3">
                    Cadastrar benefício
                </h3>

                <form method="POST">

                    <input type="hidden" name="token"
                           value="<?= escapar($token) ?>">

                    <label class="form-label">
                        Descrição do benefício
                    </label>

                    <textarea
                        name="descricao_beneficio"
                        class="form-control campo-admin mb-3"
                        rows="3"
                        maxlength="500"
                        placeholder="Descreva o benefício oferecido..."
                        required
                    ></textarea>

                    <button type="submit"
                            name="cadastrar_beneficio"
                            class="btn btn-upir w-100">

                        Cadastrar benefício

                    </button>

                </form>

            </div>

        </div>

    </div>

    <!-- ASSOCIAR BENEFÍCIOS -->

    <div class="painel-admin mb-4">

        <h3 class="subtitulo mb-3">
            Associar benefício ao plano
        </h3>

        <?php if ($planos->num_rows > 0 && $beneficios->num_rows > 0): ?>

            <form method="POST">

                <input type="hidden" name="token"
                       value="<?= escapar($token) ?>">

                <div class="row g-3 align-items-end">

                    <div class="col-md-5">

                        <label class="form-label">
                            Plano
                        </label>

                        <select name="id_plano"
                                class="form-select campo-admin"
                                required>

                            <option value="">
                                Selecione um plano
                            </option>

                            <?php while ($plano = $planos->fetch_assoc()): ?>

                                <option value="<?= (int)$plano["id_plano"] ?>">

                                    Plano <?= (int)$plano["id_plano"] ?>
                                    - R$ <?= number_format((float)$plano["valor_plano"], 2, ",", ".") ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-md-5">

                        <label class="form-label">
                            Benefício
                        </label>

                        <select name="id_beneficio"
                                class="form-select campo-admin"
                                required>

                            <option value="">
                                Selecione um benefício
                            </option>

                            <?php while ($beneficio = $beneficios->fetch_assoc()): ?>

                                <option value="<?= (int)$beneficio["id_beneficio"] ?>">

                                    <?= escapar($beneficio["descricao_beneficio"]) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <button type="submit"
                                name="associar"
                                class="btn btn-upir w-100">

                            Associar

                        </button>

                    </div>

                </div>

            </form>

        <?php else: ?>

            <div class="alert alert-warning mb-0">
                Cadastre pelo menos um plano e um benefício antes de associá-los.
            </div>

        <?php endif; ?>

    </div>

    <!-- TABELA DE ASSOCIAÇÕES -->

    <div class="painel-admin">

        <h3 class="subtitulo mb-4">
            Benefícios de cada plano
        </h3>

        <div class="table-responsive">

            <table class="table tabela-admin">

                <thead>

                    <tr>
                        <th>Plano</th>
                        <th>Valor</th>
                        <th>Benefício</th>
                        <th>Ação</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($resultado && $resultado->num_rows > 0): ?>

                        <?php while ($linha = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    Plano <?= (int)$linha["id_plano"] ?>
                                </td>

                                <td>
                                    R$ <?= number_format((float)$linha["valor_plano"], 2, ",", ".") ?>
                                </td>

                                <td>
                                    <?= escapar($linha["descricao_beneficio"]) ?>
                                </td>

                                <td>

                                    <form method="POST"
                                          onsubmit="return confirm('Deseja remover este benefício do plano?');">

                                        <input type="hidden" name="token"
                                               value="<?= escapar($token) ?>">

                                        <input type="hidden"
                                               name="id_plano_beneficio"
                                               value="<?= (int)$linha["id_plano_beneficio"] ?>">

                                        <button type="submit"
                                                name="remover"
                                                class="btn btn-danger btn-sm">

                                            Remover

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="4" class="text-center py-4">
                                Nenhum benefício associado a um plano.
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
