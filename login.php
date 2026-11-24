<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Login - Upir Academia</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="index.css">
</head>

<body>

    <div class="container mt-4">
        <a href="index.html">
            <img src="Imagens/icone.png"
                 alt="Logo Upir"
                 style="max-height: 70px; width: 70px;">
        </a>
    </div>

    <div class="container my-5">

        <form id="formull2" action="validacao.php" method="POST">

            <fieldset class="p-4">

                <div class="row justify-content-center align-items-center">

                    <h1 class="text-center mb-5">Login</h1>

                    <div class="col-xl-3 col-md-4 text-center">
                        <img width="80%"
                             src="Imagens/dragao.png"
                             alt="Dragão da Upir">
                    </div>

                    <div class="col-xl-6 col-md-7">

                        <div class="mb-4">
                            <label for="cpf" class="form-label">
                                CPF:
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="cpf"
                                name="cpf"
                                maxlength="14"
                                placeholder="Digite seu CPF"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="senha" class="form-label">
                                Senha:
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="senha"
                                name="senha"
                                placeholder="Digite sua senha"
                                required>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-light w-100"
                            style="border-radius: 20px;">
                            Entrar
                        </button>

                    </div>

                    <div class="text-center mt-4">
                        <a href="index.html"
                           class="nav-link"
                           style="color: #cf0139;">
                            Voltar
                        </a>
                    </div>

                </div>

            </fieldset>

        </form>

    </div>

</body>
</html>