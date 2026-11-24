<?php

require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Acesso inválido.");
}

$cpf = trim($_POST["cpf"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($cpf === "" || $senha === "") {
    exit("Preencha todos os campos.");
}

try {

    $sql = "SELECT id_cliente, nome_cliente, senha_cliente
            FROM Cliente
            WHERE cpf_cliente = ?";

    $consulta = $conexao->prepare($sql);

    $consulta->bind_param("s", $cpf);

    $consulta->execute();

    $resultado = $consulta->get_result();

    $cliente = $resultado->fetch_assoc();

    $consulta->close();

    if (!$cliente || !password_verify($senha, $cliente["senha_cliente"])) {
        exit("CPF ou senha incorretos.");
    }

    session_start();

    $_SESSION["id_cliente"] = $cliente["id_cliente"];
    $_SESSION["nome_cliente"] = $cliente["nome_cliente"];

    header("Location: area_usuario.php");
    exit;

} catch (Throwable $erro) {

    error_log($erro->getMessage());

    echo "Erro ao realizar login.";

}

$conexao->close();

?>