
<?php

require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Acesso inválido.");
}

// RECEBER DADOS

$nome = trim($_POST["nome"] ?? "");
$cpf = trim($_POST["cpf"] ?? "");
$sexo = trim($_POST["sexo"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");
$email = trim($_POST["email"] ?? "");
$data_nascimento = $_POST["data_nascimento"] ?? "";
$estado = trim($_POST["estado"] ?? "");
$cidade = trim($_POST["cidade"] ?? "");
$cep = trim($_POST["cep"] ?? "");

$numero = filter_var(
    $_POST["numero"] ?? null,
    FILTER_VALIDATE_INT
);

$senha = $_POST["senha"] ?? "";

$id_plano = filter_var(
    $_POST["plano"] ?? null,
    FILTER_VALIDATE_INT
);

$id_forma_pagamento = filter_var(
    $_POST["pagamento"] ?? null,
    FILTER_VALIDATE_INT
);

// VALIDAR DADOS

if (
    $nome === "" ||
    $cpf === "" ||
    $telefone === "" ||
    $email === "" ||
    $estado === "" ||
    $cidade === "" ||
    $cep === "" ||
    !in_array($sexo, ["Feminino", "Masculino", "Outro"], true) ||
    $numero === false ||
    $numero === null ||
    $numero < 1 ||
    strlen($senha) < 8 ||
    !$id_plano ||
    !$id_forma_pagamento ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    exit("Preencha todos os campos corretamente.");
}

// VALIDAR NASCIMENTO

$data = DateTime::createFromFormat(
    "Y-m-d",
    $data_nascimento
);

if (
    !$data ||
    $data->format("Y-m-d") !== $data_nascimento ||
    $data >= new DateTime("today")
) {
    exit("Data de nascimento inválida.");
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

try {

    $conexao->begin_transaction();

    // CONSULTAR PLANO

    $sql = "SELECT valor_plano
            FROM Plano
            WHERE id_plano = ?";

    $consulta = $conexao->prepare($sql);
    $consulta->bind_param("i", $id_plano);
    $consulta->execute();

    $plano = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if (!$plano) {
        throw new Exception("Plano não encontrado.");
    }

    $valor_plano = $plano["valor_plano"];

    // VERIFICAR FORMA DE PAGAMENTO

    $sql = "SELECT id_forma_pagamento
            FROM Forma_Pagamento
            WHERE id_forma_pagamento = ?
            AND status_forma_pagamento = 1";

    $consulta = $conexao->prepare($sql);
    $consulta->bind_param("i", $id_forma_pagamento);
    $consulta->execute();

    $forma = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if (!$forma) {
        throw new Exception("Forma de pagamento indisponível.");
    }

    // CADASTRAR CLIENTE

    $sql = "INSERT INTO Cliente (
        nome_cliente,
        telefone_cliente,
        email_cliente,
        cpf_cliente,
        data_de_nascimento_cliente,
        sexo_cliente,
        cidade_cliente,
        estado_cliente,
        cep_cliente,
        numero_residencia_cliente,
        senha_cliente
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $consulta = $conexao->prepare($sql);

    $consulta->bind_param(
        "sssssssssis",
        $nome,
        $telefone,
        $email,
        $cpf,
        $data_nascimento,
        $sexo,
        $cidade,
        $estado,
        $cep,
        $numero,
        $senha_hash
    );

    $consulta->execute();

    $id_cliente = $conexao->insert_id;

    $consulta->close();

    // CRIAR ASSINATURA ATIVA

    $sql = "INSERT INTO assinatura (
        id_cliente,
        id_plano,
        data_inicio,
        data_vencimento,
        status_assinatura
    ) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 1)";

    $consulta = $conexao->prepare($sql);

    $consulta->bind_param(
        "ii",
        $id_cliente,
        $id_plano
    );

    $consulta->execute();

    $id_assinatura = $conexao->insert_id;

    $consulta->close();

    // REGISTRAR PAGAMENTO COMO REALIZADO

    $sql = "INSERT INTO Pagamento (
        valor_pago,
        id_forma_pagamento,
        id_assinatura,
        data_pagamento,
        status_pagamento
    ) VALUES (?, ?, ?, CURDATE(), 1)";

    $consulta = $conexao->prepare($sql);

    $consulta->bind_param(
        "dii",
        $valor_plano,
        $id_forma_pagamento,
        $id_assinatura
    );

    $consulta->execute();
    $consulta->close();

    // FINALIZAR TRANSAÇÃO

    $conexao->commit();

    header(
        "Location: confirmacao.php?id_assinatura=" . $id_assinatura
    );

    exit;

} catch (Throwable $erro) {

    $conexao->rollback();

    error_log($erro->getMessage());

    echo "<h2>Não foi possível realizar o cadastro.</h2>";
    echo "<p>Verifique seus dados ou se CPF, telefone ou e-mail já estão cadastrados.</p>";
    echo '<a href="cadastro.php">Voltar ao cadastro</a>';
}

$conexao->close();

?>
