
<?php
session_start();

if (isset($_SESSION["id_adm"])) {
    header("Location: adm.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login Administrativo | Upir</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="adm.css">
</head>

<body class="login-body">

    <div class="login-container">

        <div class="text-center mb-4">

            <img src="Imagens/icone.png"
                 alt="Logo Upir"
                 class="logo-admin">

            <h2 class="titulo-admin mt-3">UPIR</h2>

            <p>Área administrativa</p>

        </div>

        <?php if (isset($_GET["erro"])) { ?>

            <div class="alert alert-danger">
                E-mail ou senha incorretos!
            </div>

        <?php } ?>

        <form action="adm_validacao.php" method="POST">

            <div class="mb-3">

                <label for="email" class="form-label">
                    E-mail
                </label>

                <input type="email"
                       class="form-control"
                       id="email"
                       name="email"
                       maxlength="100"
                       placeholder="Digite seu e-mail"
                       required>

            </div>

            <div class="mb-4">

                <label for="senha" class="form-label">
                    Senha
                </label>

                <input type="password"
                       class="form-control"
                       id="senha"
                       name="senha"
                       placeholder="Digite sua senha"
                       required>

            </div>

            <button type="submit" class="btn btn-upir w-100">
                Entrar
            </button>

        </form>

        <div class="text-center mt-4">

            <a href="index.html" class="link-voltar">
                Voltar para o site
            </a>

        </div>

    </div>

</body>
</html>
