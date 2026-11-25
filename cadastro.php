
<?php
require_once "conexao.php";

$sql_planos = "SELECT id_plano, valor_plano FROM Plano WHERE status_plano = 1 ORDER BY id_plano";
$planos = $conexao->query($sql_planos);

// Buscar formas de pagamento ativas
$sql_formas = "SELECT id_forma_pagamento, nome_forma_pagamento
               FROM Forma_Pagamento
               WHERE status_forma_pagamento = 1
               ORDER BY id_forma_pagamento";
$formas = $conexao->query($sql_formas);

// Receber plano selecionado pela página de planos
$plano_selecionado = filter_input(INPUT_GET, "id_plano", FILTER_VALIDATE_INT);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cadastro | Upir Academia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="index.css">

    <style>
        body {
            background-color: #202124;
            color: #c8d1d9;
        }

        .cadastro-container {
            max-width: 950px;
            margin: 50px auto;
            padding: 35px;
            background-color: #303238;
            border-radius: 18px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }

        .titulo {
            color: #cf0139;
            font-weight: bold;
        }

        .subtitulo {
            color: #c8d1d9;
        }

        .form-label {
            color: #c8d1d9;
            font-weight: 500;
        }

        .form-control, .form-select {
            background-color: #e9ecef;
            border: none;
            border-radius: 8px;
            padding: 11px;
        }

        .form-control:focus, .form-select:focus {
            border: 1px solid #cf0139;
            box-shadow: 0 0 0 0.2rem rgba(207,1,57,0.2);
        }

        .secao {
            color: #cf0139;
            border-bottom: 1px solid #555;
            padding-bottom: 10px;
            margin: 30px 0 20px;
        }

        .btn-upir {
            background-color: #cf0139;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: bold;
        }

        .btn-upir:hover {
            background-color: #a90030;
            color: white;
        }

        .btn-limpar {
            border: 1px solid #c8d1d9;
            color: #c8d1d9;
            border-radius: 25px;
            padding: 12px 30px;
        }

        .btn-limpar:hover {
            background-color: #c8d1d9;
            color: #202124;
        }

        .logo {
            width: 75px;
        }

        .voltar {
            color: #cf0139;
            text-decoration: none;
        }

        .voltar:hover {
            color: white;
        }

        .radio-opcoes {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            padding-top: 8px;
        }

        .radio-opcoes label {
            margin-left: 5px;
        }

        @media (max-width: 576px) {
            .cadastro-container {
                margin: 20px 12px;
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <header class="container pt-4">
        <a href="index.html">
            <img src="Imagens/icone.png" alt="Logo Upir Academia" class="logo">
        </a>
    </header>

    <main class="container">

        <div class="cadastro-container">

            <div class="text-center mb-4">
                <h1 class="titulo">Seja aluno Upir!</h1>
                <p class="subtitulo">
                    Preencha seus dados e escolha o plano ideal para você.
                </p>
            </div>

            <form action="cadastrar.php" method="POST">

                <h4 class="secao">Dados pessoais</h4>

                <div class="row g-3">

                    <div class="col-md-8">
                        <label for="nome" class="form-label">Nome completo</label>
                        <input type="text" class="form-control"
                            id="nome" name="nome" maxlength="100"
                            placeholder="Digite seu nome completo" required>
                    </div>

                    <div class="col-md-4">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control"
                            id="cpf" name="cpf" maxlength="14"
                            placeholder="000.000.000-00" required>
                    </div>

                    <div class="col-md-6">
                        <label for="data" class="form-label">Data de nascimento</label>
                        <input type="date" class="form-control"
                            id="data" name="data_nascimento" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sexo</label>

                        <div class="radio-opcoes">
                            <div>
                                <input type="radio" id="feminino"
                                    name="sexo" value="Feminino" required>
                                <label for="feminino">Feminino</label>
                            </div>

                            <div>
                                <input type="radio" id="masculino"
                                    name="sexo" value="Masculino">
                                <label for="masculino">Masculino</label>
                            </div>

                            <div>
                                <input type="radio" id="outro"
                                    name="sexo" value="Outro">
                                <label for="outro">Outro</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control"
                            id="telefone" name="telefone" maxlength="20"
                            placeholder="(11) 99999-9999" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control"
                            id="email" name="email" maxlength="225"
                            placeholder="seuemail@email.com" required>
                    </div>

                </div>

                <h4 class="secao">Endereço</h4>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="estado" class="form-label">Estado</label>
                        <input type="text" class="form-control"
                            id="estado" name="estado" maxlength="100"
                            placeholder="Digite seu estado" required>
                    </div>

                    <div class="col-md-6">
                        <label for="cidade" class="form-label">Cidade</label>
                        <input type="text" class="form-control"
                            id="cidade" name="cidade" maxlength="100"
                            placeholder="Digite sua cidade" required>
                    </div>

                    <div class="col-md-6">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" class="form-control"
                            id="cep" name="cep" maxlength="9"
                            placeholder="00000-000" required>
                    </div>

                    <div class="col-md-6">
                        <label for="numero" class="form-label">Número da residência</label>
                        <input type="number" class="form-control"
                            id="numero" name="numero" min="1"
                            placeholder="Número" required>
                    </div>

                </div>

                <h4 class="secao">Dados de acesso</h4>

                <div class="row g-3">

                    <div class="col-md-12">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control"
                            id="senha" name="senha" minlength="8"
                            placeholder="Crie uma senha com pelo menos 8 caracteres"
                            required>
                    </div>

                </div>

                <h4 class="secao">Plano e pagamento</h4>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="plano" class="form-label">Escolha seu plano</label>

                        <select class="form-select" name="plano" id="plano" required>
                            <option value="">Selecione um plano</option>

                            <?php while ($plano = $planos->fetch_assoc()) { ?>

                                <option
                                    value="<?php echo $plano['id_plano']; ?>"
                                    <?php echo ($plano_selecionado == $plano['id_plano']) ? 'selected' : ''; ?>>

                                    Plano <?php echo $plano['id_plano']; ?> -
                                    R$ <?php echo number_format(
                                        $plano['valor_plano'], 2, ',', '.'
                                    ); ?>

                                </option>

                            <?php } ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="pagamento" class="form-label">
                            Forma de pagamento
                        </label>

                        <select class="form-select"
                            name="pagamento" id="pagamento" required>

                            <option value="">Selecione uma forma de pagamento</option>

                            <?php while ($forma = $formas->fetch_assoc()) { ?>

                                <option value="<?php echo $forma['id_forma_pagamento']; ?>">
                                    <?php echo htmlspecialchars(
                                        $forma['nome_forma_pagamento'],
                                        ENT_QUOTES, 'UTF-8'
                                    ); ?>
                                </option>

                            <?php } ?>

                        </select>
                    </div>

                </div>

                <div class="text-center mt-5">

                    <button type="submit" class="btn btn-upir me-2">
                        Finalizar cadastro
                    </button>

                    <button type="reset" class="btn btn-limpar">
                        Limpar
                    </button>

                </div>

                <div class="text-center mt-4">
                    <a href="index.html" class="voltar">
                        Voltar para a página inicial
                    </a>
                </div>

            </form>

        </div>

    </main>

    <footer class="text-center py-4">
        <p class="mb-0 text-secondary">
            UPIR Academia | O treino é só o começo.
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conexao->close();
?>
