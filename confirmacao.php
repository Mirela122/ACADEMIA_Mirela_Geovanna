
<?php
require_once "conexao.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_assinatura = filter_input(
    INPUT_GET,
    "id_assinatura",
    FILTER_VALIDATE_INT
);

if (!$id_assinatura) {
    exit("Assinatura inválida.");
}

$sql = "SELECT
            c.nome_cliente,
            p.id_plano,
            p.valor_plano,
            a.id_assinatura,
            a.data_inicio,
            a.data_vencimento,
            a.status_assinatura,
            pg.status_pagamento,
            pg.data_pagamento,
            fp.nome_forma_pagamento

        FROM assinatura a

        INNER JOIN Cliente c
            ON a.id_cliente = c.id_cliente

        INNER JOIN Plano p
            ON a.id_plano = p.id_plano

        INNER JOIN Pagamento pg
            ON a.id_assinatura = pg.id_assinatura

        INNER JOIN Forma_Pagamento fp
            ON pg.id_forma_pagamento = fp.id_forma_pagamento

        WHERE a.id_assinatura = ?
        ORDER BY pg.id_pagamento DESC
        LIMIT 1";

$consulta = $conexao->prepare($sql);
$consulta->bind_param("i", $id_assinatura);
$consulta->execute();

$dados = $consulta->get_result()->fetch_assoc();

$consulta->close();
$conexao->close();

if (!$dados) {
    exit("Dados da assinatura não encontrados.");
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function formatarData($data) {
    return $data ? date("d/m/Y", strtotime($data)) : "Não informada";
}

// Verificar se contratação foi concluída
if (!$dados["status_assinatura"] || !$dados["status_pagamento"]) {
    exit("A contratação ainda não foi concluída.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cadastro realizado | Upir Academia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background-color: #202124;
            color: #c8d1d9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirmacao {
            background-color: #303238;
            border-radius: 18px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }

        .titulo {
            color: #cf0139;
            font-weight: bold;
        }

        .icone {
            font-size: 55px;
            color: #198754;
        }

        .detalhes {
            background-color: #25272c;
            padding: 20px;
            border-radius: 12px;
        }

        .btn-upir {
            background-color: #cf0139;
            color: white;
            border-radius: 25px;
            padding: 12px 30px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-upir:hover {
            background-color: #a90030;
            color: white;
        }
    </style>
</head>

<body>

    <main class="confirmacao text-center">

        <div class="icone">✓</div>

        <h1 class="titulo">Cadastro realizado!</h1>

        <p class="mt-3">
            Seja bem-vindo(a) à Upir Academia!
            Sua contratação foi concluída com sucesso.
        </p>

        <div class="detalhes text-start mt-4">

            <h4 class="titulo mb-3">Dados da assinatura</h4>

            <p>
                <strong>Aluno:</strong>
                <?= escapar($dados["nome_cliente"]) ?>
            </p>

            <p>
                <strong>Número da assinatura:</strong>
                <?= (int)$dados["id_assinatura"] ?>
            </p>

            <p>
                <strong>Plano:</strong>
                Plano <?= (int)$dados["id_plano"] ?>
            </p>

            <p>
                <strong>Valor mensal:</strong>
                R$ <?= number_format((float)$dados["valor_plano"], 2, ",", ".") ?>
            </p>

            <p>
                <strong>Início:</strong>
                <?= formatarData($dados["data_inicio"]) ?>
            </p>

            <p>
                <strong>Vencimento:</strong>
                <?= formatarData($dados["data_vencimento"]) ?>
            </p>

            <p>
                <strong>Forma de pagamento:</strong>
                <?= escapar($dados["nome_forma_pagamento"]) ?>
            </p>

            <p>
                <strong>Pagamento:</strong>
                <span class="text-success">Aprovado</span>
            </p>

            <p class="mb-0">
                <strong>Assinatura:</strong>
                <span class="text-success">Ativa</span>
            </p>

        </div>

        <p class="mt-4">
            Obrigado por escolher a Upir!
        </p>

        <a href="login.php" class="btn-upir">
            Acessar minha conta
        </a>

    </main>

</body>
</html>
