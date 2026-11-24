
<?php

session_start();

if (!isset($_SESSION["id_adm"])) {
    header("Location: adm_login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Administração | Upir</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="adm.css">

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

        <div class="text-center mb-5">

            <h1 class="titulo-admin">
                Área administrativa
            </h1>

            <p>
                Bem-vindo(a), administrador!
            </p>

            <p class="text-secondary">
                Selecione o que deseja gerenciar.
            </p>

        </div>

        <div class="row g-4">

            <!-- CLIENTES -->

            <div class="col-md-4">

                <a href="adm_cliente.php" class="card-admin">

                    <h3>Clientes</h3>

                    <p>
                        Consultar e alterar os dados dos clientes.
                    </p>

                </a>

            </div>

            <!-- PROFESSORES -->

            <div class="col-md-4">

                <a href="adm_professores.php" class="card-admin">

                    <h3>Professores e funções</h3>

                    <p>
                        Cadastrar, consultar, editar e excluir professores.
                    </p>

                </a>

            </div>

            <!-- PLANOS -->

            <div class="col-md-4">

                <a href="adm_plano.php" class="card-admin">

                    <h3>Planos e benefícios</h3>

                    <p>
                        Consultar e modificar os planos e seus valores.
                    </p>

                </a>

            </div>
            
            <!-- ACESSOS -->

            <div class="col-md-4">

                <a href="adm_acessos.php" class="card-admin">

                    <h3>Acessos</h3>

                    <p>
                        Consultar os registros de entrada dos alunos.
                    </p>

                </a>

            </div>

            <!-- AULAS -->

            <div class="col-md-4">

                <a href="adm_aulas.php" class="card-admin">

                    <h3>Aulas</h3>

                    <p>
                        Gerenciar aulas, horários e professores.
                    </p>

                </a>

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
