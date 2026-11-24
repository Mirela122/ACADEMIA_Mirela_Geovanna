
<?php

session_start();

require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: adm_login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($email === "" || $senha === "") {
    header("Location: adm_login.php?erro=1");
    exit;
}

$sql = "SELECT id_adm, email_adm, senha_adm
        FROM Administrador
        WHERE email_adm = ?
        LIMIT 1";

$stmt = $conexao->prepare($sql);

$stmt->bind_param("s", $email);

$stmt->execute();

$resultado = $stmt->get_result();

if ($administrador = $resultado->fetch_assoc()) {

    $senha_hash = hash("sha256", $senha);

    if (hash_equals($administrador["senha_adm"], $senha_hash)) {

        session_regenerate_id(true);

        $_SESSION["id_adm"] = $administrador["id_adm"];
        $_SESSION["email_adm"] = $administrador["email_adm"];

        header("Location: adm.php");
        exit;
    }
}

header("Location: adm_login.php?erro=1");
exit;

?>
