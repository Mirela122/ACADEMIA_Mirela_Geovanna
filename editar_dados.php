
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

$mensagem = "";
$tipo_mensagem = "danger";

// BUSCAR DADOS DO CLIENTE

$stmt = $conexao->prepare("
    SELECT *
    FROM Cliente
    WHERE id_cliente = ?
");

$stmt->bind_param("i", $id_cliente);
$stmt->execute();

$cliente = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$cliente) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// ATUALIZAR DADOS

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"]);
    $telefone = trim($_POST["telefone"]);
    $email = trim($_POST["email"]);
    $cpf = trim($_POST["cpf"]);
    $nascimento = $_POST["nascimento"];
    $sexo = trim($_POST["sexo"]);
    $cidade = trim($_POST["cidade"]);
    $estado = trim($_POST["estado"]);
    $cep = trim($_POST["cep"]);
    $numero = filter_input(
        INPUT_POST,
        "numero",
        FILTER_VALIDATE_INT
    );

    $senha = $_POST["senha"] ?? "";

    try {

        if (
            $nome === "" ||
            $telefone === "" ||
            $email === "" ||
            $cpf === "" ||
            $nascimento === "" ||
            $sexo === "" ||
            $cidade === "" ||
            $estado === "" ||
            $cep === "" ||
            !$numero
        ) {
            throw new Exception("Preencha todos os campos corretamente.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Digite um e-mail válido.");
        }

        // VERIFICAR SE EMAIL, CPF OU TELEFONE JÁ PERTENCEM A OUTRO CLIENTE

        $stmt = $conexao->prepare("
            SELECT id_cliente
            FROM Cliente
            WHERE (email_cliente = ?
                OR cpf_cliente = ?
                OR telefone_cliente = ?)
            AND id_cliente <> ?
        ");

        $stmt->bind_param(
            "sssi",
            $email,
            $cpf,
            $telefone,
            $id_cliente
        );

        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception(
                "Este e-mail, CPF ou telefone já está cadastrado para outro aluno."
            );
        }

        $stmt->close();

        // ATUALIZAR SEM ALTERAR A SENHA

        if ($senha === "") {

            $stmt = $conexao->prepare("
                UPDATE Cliente
                SET
                    nome_cliente = ?,
                    telefone_cliente = ?,
                    email_cliente = ?,
                    cpf_cliente = ?,
                    data_de_nascimento_cliente = ?,
                    sexo_cliente = ?,
                    cidade_cliente = ?,
                    estado_cliente = ?,
                    cep_cliente = ?,
                    numero_residencia_cliente = ?
                WHERE id_cliente = ?
            ");

            $stmt->bind_param(
                "sssssssssii",
                $nome,
                $telefone,
                $email,
                $cpf,
                $nascimento,
                $sexo,
                $cidade,
                $estado,
                $cep,
                $numero,
                $id_cliente
            );

        } else {

            // ATUALIZAR INCLUINDO NOVA SENHA

            $senha_hash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            $stmt = $conexao->prepare("
                UPDATE Cliente
                SET
                    nome_cliente = ?,
                    telefone_cliente = ?,
                    email_cliente = ?,
                    cpf_cliente = ?,
                    data_de_nascimento_cliente = ?,
                    sexo_cliente = ?,
                    cidade_cliente = ?,
                    estado_cliente = ?,
                    cep_cliente = ?,
                    numero_residencia_cliente = ?,
                    senha_cliente = ?
                WHERE id_cliente = ?
            ");

            $stmt->bind_param(
                "sssssssssssi",
                $nome,
                $telefone,
                $email,
                $cpf,
                $nascimento,
                $sexo,
                $cidade,
                $estado,
                $cep,
                $numero,
                $senha_hash,
                $id_cliente
            );
        }

        $stmt->execute();
        $stmt->close();

        header("Location: editar_dados.php?sucesso=1");
        exit;

    } catch (Throwable $e) {

        $mensagem = $e->getMessage();
    }

    // ATUALIZAR OS DADOS EXIBIDOS

    $stmt = $conexao->prepare("
        SELECT *
        FROM Cliente
        WHERE id_cliente = ?
    ");

    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();

    $cliente = $stmt->get_result()->fetch_assoc();

    $stmt->close();
}

function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editar meus dados - UPIR Academia</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            background-color: #252529;
            color: #c8d1d9;
        }

        .container-editar {
            max-width: 850px;
            margin: 50px auto;
            padding: 35px;
            background-color: #303238;
            border-radius: 18px;
        }

        .titulo {
            color: #cf0139;
            font-weight: bold;
        }

        .form-label {
            color: #c8d1d9;
        }

        .form-control,
        .form-select {
            padding: 11px;
        }

        .btn-upir {
            background-color: #cf0139;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 11px 25px;
        }

        .btn-upir:hover {
            background-color: #a80030;
            color: white;
        }

    </style>

</head>

<body>

<main class="container">

    <div class="container-editar">

        <h1 class="titulo text-center mb-4">
            Meus dados cadastrais
        </h1>

        <p class="text-center mb-4">
            Atualize suas informações pessoais.
        </p>

        <?php if (isset($_GET["sucesso"])): ?>

            <div class="alert alert-success">
                Seus dados foram atualizados com sucesso!
            </div>

        <?php endif; ?>

        <?php if ($mensagem !== ""): ?>

            <div class="alert alert-danger">
                <?= escapar($mensagem) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="row g-3">

                <div class="col-md-12">

                    <label class="form-label">Nome completo</label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?= escapar($cliente["nome_cliente"]) ?>"
                        maxlength="100"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Telefone</label>

                    <input
                        type="text"
                        name="telefone"
                        class="form-control"
                        value="<?= escapar($cliente["telefone_cliente"]) ?>"
                        maxlength="20"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">E-mail</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= escapar($cliente["email_cliente"]) ?>"
                        maxlength="225"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">CPF</label>

                    <input
                        type="text"
                        name="cpf"
                        class="form-control"
                        value="<?= escapar($cliente["cpf_cliente"]) ?>"
                        maxlength="14"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Data de nascimento</label>

                    <input
                        type="date"
                        name="nascimento"
                        class="form-control"
                        value="<?= escapar($cliente["data_de_nascimento_cliente"]) ?>"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Sexo</label>

                    <select name="sexo" class="form-select" required>

                        <option value="Masculino"
                            <?= $cliente["sexo_cliente"] === "Masculino" ? "selected" : "" ?>>
                            Masculino
                        </option>

                        <option value="Feminino"
                            <?= $cliente["sexo_cliente"] === "Feminino" ? "selected" : "" ?>>
                            Feminino
                        </option>

                    </select>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Cidade</label>

                    <input
                        type="text"
                        name="cidade"
                        class="form-control"
                        value="<?= escapar($cliente["cidade_cliente"]) ?>"
                        maxlength="100"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Estado</label>

                    <input
                        type="text"
                        name="estado"
                        class="form-control"
                        value="<?= escapar($cliente["estado_cliente"]) ?>"
                        maxlength="100"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">CEP</label>

                    <input
                        type="text"
                        name="cep"
                        class="form-control"
                        value="<?= escapar($cliente["cep_cliente"]) ?>"
                        maxlength="9"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Número da residência</label>

                    <input
                        type="number"
                        name="numero"
                        class="form-control"
                        value="<?= escapar($cliente["numero_residencia_cliente"]) ?>"
                        min="1"
                        required>

                </div>

                <div class="col-md-12">

                    <hr class="border-secondary">

                    <label class="form-label">
                        Nova senha (opcional)
                    </label>

                    <input
                        type="password"
                        name="senha"
                        class="form-control"
                        placeholder="Deixe vazio para manter a senha atual"
                        minlength="8">

                    <small class="text-secondary">
                        Preencha somente se desejar alterar sua senha.
                    </small>

                </div>

            </div>

            <div class="d-grid gap-3 mt-4">

                <button type="submit" class="btn btn-upir">
                    Salvar alterações
                </button>

                <a href="area_usuario.php"
                   class="btn btn-outline-light">
                    Voltar para minha área
                </a>

            </div>

        </form>

    </div>

</main>

</body>
</html>

<?php
$conexao->close();
?>
