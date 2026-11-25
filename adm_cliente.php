
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

function formatarData($data) {
    if (empty($data)) {
        return "Não informado";
    }

    $timestamp = strtotime($data);

    return $timestamp ? date("d/m/Y", $timestamp) : "Não informado";
}

// =================================================
// LISTAGEM DE CLIENTES
// =================================================

$sql = "
    SELECT
        id_cliente,
        nome_cliente,
        cpf_cliente,
        email_cliente,
        telefone_cliente
    FROM Cliente
    ORDER BY id_cliente ASC
";

$resultado = $conexao->query($sql);
$clientes = $resultado->fetch_all(MYSQLI_ASSOC);

// =================================================
// CONSULTA INDIVIDUAL
// =================================================

$cliente = null;
$assinatura = null;
$beneficios = [];
$historico = [];
$pagamentos = [];
$acessos = [];

if (isset($_GET["id"])) {

    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        $id = 0;
    }

    // DADOS CADASTRAIS

    $stmt = $conexao->prepare("
        SELECT *
        FROM Cliente
        WHERE id_cliente = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $cliente = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($cliente) {

        // =================================================
        // ASSINATURA ATUAL
        // =================================================

        $stmt = $conexao->prepare("
            SELECT
                ass.id_assinatura,
                ass.id_plano,
                ass.data_inicio,
                ass.data_vencimento,
                ass.status_assinatura,
                p.valor_plano

            FROM assinatura ass

            INNER JOIN Plano p
                ON ass.id_plano = p.id_plano

            WHERE ass.id_cliente = ?
              AND ass.status_assinatura = 1

            ORDER BY ass.id_assinatura DESC
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $assinatura = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // =================================================
        // BENEFÍCIOS DO PLANO ATUAL
        // =================================================

        if ($assinatura) {

            $stmt = $conexao->prepare("
                SELECT
                    b.id_beneficio,
                    b.descricao_beneficio

                FROM Plano_Beneficio pb

                INNER JOIN Beneficio b
                    ON pb.id_beneficio = b.id_beneficio

                WHERE pb.id_plano = ?

                ORDER BY b.id_beneficio
            ");

            $stmt->bind_param("i", $assinatura["id_plano"]);
            $stmt->execute();

            $beneficios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        // =================================================
        // HISTÓRICO DE PLANOS
        // =================================================

        $stmt = $conexao->prepare("
            SELECT
                h.id_historico,
                h.id_assinatura,
                h.id_plano,
                h.data_inicio,
                h.data_cancelamento,
                p.valor_plano

            FROM historico h

            INNER JOIN assinatura ass
                ON h.id_assinatura = ass.id_assinatura

            INNER JOIN Plano p
                ON h.id_plano = p.id_plano

            WHERE ass.id_cliente = ?

            ORDER BY h.data_inicio DESC, h.id_historico DESC
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $historico = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // =================================================
        // HISTÓRICO DE PAGAMENTOS
        // =================================================

        $stmt = $conexao->prepare("
            SELECT
                pg.id_pagamento,
                pg.valor_pago,
                pg.data_pagamento,
                pg.status_pagamento,
                fp.nome_forma_pagamento,
                ass.id_assinatura,
                ass.id_plano

            FROM Pagamento pg

            INNER JOIN assinatura ass
                ON pg.id_assinatura = ass.id_assinatura

            LEFT JOIN Forma_Pagamento fp
                ON pg.id_forma_pagamento = fp.id_forma_pagamento

            WHERE ass.id_cliente = ?

            ORDER BY pg.id_pagamento DESC
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $pagamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // =================================================
        // HISTÓRICO DE ACESSOS
        // =================================================

        $stmt = $conexao->prepare("
            SELECT
                a.id_acesso,
                a.data_acesso,
                a.horario_acesso,
                a.status_acesso,
                ass.id_assinatura,
                ass.id_plano

            FROM Acesso a

            LEFT JOIN assinatura ass
                ON a.id_assinatura = ass.id_assinatura

            WHERE a.id_cliente = ?

            ORDER BY a.data_acesso DESC, a.horario_acesso DESC
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $acessos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Clientes | UPIR Administração</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet" href="adm.css">

<style>

.titulo-pagina,
.subtitulo {
    color: #cf0139;
    font-weight: bold;
}

.painel-admin {
    background-color: #303238;
    border: 1px solid #45474d;
    border-radius: 15px;
    padding: 25px;
}

.informacao {
    background-color: #202124;
    padding: 15px;
    border-radius: 10px;
    height: 100%;
}

.informacao small {
    color: #cf0139;
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.informacao p {
    margin: 0;
    overflow-wrap: anywhere;
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

.status-ativo {
    color: #20c997;
    font-weight: bold;
}

.status-inativo {
    color: #ffc107;
    font-weight: bold;
}

.status-autorizado {
    background-color: #198754;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.status-bloqueado {
    background-color: #dc3545;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.btn-detalhes,
.btn-upir {
    background-color: #cf0139;
    color: white;
    border: none;
}

.btn-detalhes:hover,
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
    .painel-admin {
        padding: 15px;
    }
}

</style>

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

<div class="mb-4">

<h1 class="titulo-pagina">
Gerenciamento de clientes
</h1>

<p class="text-secondary">
Consulte os dados cadastrais, planos, pagamentos e acessos dos clientes.
</p>

<a href="adm.php" class="btn btn-voltar btn-sm">
Voltar ao painel
</a>

</div>

<?php if (isset($_GET["id"]) && !$cliente): ?>

<div class="alert alert-warning">
Cliente não encontrado.
</div>

<a href="adm_cliente.php" class="btn btn-upir">
Voltar aos clientes
</a>

<?php elseif ($cliente): ?>

<!-- DADOS CADASTRAIS -->

<div class="painel-admin mb-4">

<h3 class="subtitulo mb-4">
Dados cadastrais
</h3>

<div class="row g-3">

<?php
$campos = [
    "Nome completo" => "nome_cliente",
    "CPF" => "cpf_cliente",
    "E-mail" => "email_cliente",
    "Telefone" => "telefone_cliente",
    "Data de nascimento" => "data_de_nascimento_cliente",
    "Sexo" => "sexo_cliente",
    "Cidade" => "cidade_cliente",
    "Estado" => "estado_cliente",
    "CEP" => "cep_cliente",
    "Número da residência" => "numero_residencia_cliente"
];

foreach ($campos as $titulo => $campo):
?>

<div class="col-md-6">

<div class="informacao">

<small><?= escapar($titulo) ?></small>

<p>
<?php
if ($campo === "data_de_nascimento_cliente") {
    echo escapar(formatarData($cliente[$campo]));
} else {
    echo escapar($cliente[$campo] ?? "Não informado");
}
?>
</p>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

<!-- PLANO ATUAL -->

<div class="painel-admin mb-4">

<h3 class="subtitulo mb-4">
Plano atual e assinatura
</h3>

<?php if ($assinatura): ?>

<div class="row g-3">

<div class="col-md-4">
<div class="informacao">
<small>Plano contratado</small>
<p>Plano <?= (int)$assinatura["id_plano"] ?></p>
</div>
</div>

<div class="col-md-4">
<div class="informacao">
<small>Valor do plano</small>
<p>
R$ <?= number_format((float)$assinatura["valor_plano"], 2, ",", ".") ?>
</p>
</div>
</div>

<div class="col-md-4">
<div class="informacao">
<small>Situação</small>
<p class="status-ativo">Ativa</p>
</div>
</div>

<div class="col-md-6">
<div class="informacao">
<small>Data de início</small>
<p><?= escapar(formatarData($assinatura["data_inicio"])) ?></p>
</div>
</div>

<div class="col-md-6">
<div class="informacao">
<small>Data de vencimento</small>
<p><?= escapar(formatarData($assinatura["data_vencimento"])) ?></p>
</div>
</div>

<div class="col-md-6">
<div class="informacao">
<small>Número da assinatura</small>
<p>#<?= (int)$assinatura["id_assinatura"] ?></p>
</div>
</div>

</div>

<h5 class="subtitulo mt-4 mb-3">
Benefícios do plano
</h5>

<?php if (count($beneficios) > 0): ?>

<ul class="list-group">

<?php foreach ($beneficios as $beneficio): ?>

<li class="list-group-item bg-dark text-light border-secondary">
<?= escapar($beneficio["descricao_beneficio"]) ?>
</li>

<?php endforeach; ?>

</ul>

<?php else: ?>

<p class="text-secondary">
Este plano não possui benefícios cadastrados.
</p>

<?php endif; ?>

<?php else: ?>

<div class="alert alert-warning mb-0">
Este cliente não possui uma assinatura ativa no momento.
</div>

<?php endif; ?>

</div>

<!-- HISTÓRICO DE PLANOS -->

<div class="painel-admin mb-4">

<h3 class="subtitulo mb-4">
Histórico de planos e assinaturas
</h3>

<div class="table-responsive">

<table class="table tabela-admin">

<thead>
<tr>
<th>Registro</th>
<th>Assinatura</th>
<th>Plano</th>
<th>Valor</th>
<th>Início</th>
<th>Cancelamento</th>
</tr>
</thead>

<tbody>

<?php if (count($historico) > 0): ?>

<?php foreach ($historico as $item): ?>

<tr>

<td><?= (int)$item["id_historico"] ?></td>

<td>#<?= (int)$item["id_assinatura"] ?></td>

<td>Plano <?= (int)$item["id_plano"] ?></td>

<td>
R$ <?= number_format((float)$item["valor_plano"], 2, ",", ".") ?>
</td>

<td>
<?= escapar(formatarData($item["data_inicio"])) ?>
</td>

<td>
<?= escapar(formatarData($item["data_cancelamento"])) ?>
</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="6" class="text-center py-3">
Nenhum histórico de plano encontrado.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

<!-- HISTÓRICO DE PAGAMENTOS -->

<div class="painel-admin mb-4">

<h3 class="subtitulo mb-4">
Histórico de pagamentos
</h3>

<div class="table-responsive">

<table class="table tabela-admin">

<thead>
<tr>
<th>ID</th>
<th>Assinatura</th>
<th>Plano</th>
<th>Valor</th>
<th>Forma de pagamento</th>
<th>Data</th>
<th>Situação</th>
</tr>
</thead>

<tbody>

<?php if (count($pagamentos) > 0): ?>

<?php foreach ($pagamentos as $pg): ?>

<tr>

<td><?= (int)$pg["id_pagamento"] ?></td>

<td>#<?= (int)$pg["id_assinatura"] ?></td>

<td>Plano <?= (int)$pg["id_plano"] ?></td>

<td>
R$ <?= number_format((float)$pg["valor_pago"], 2, ",", ".") ?>
</td>

<td>
<?= escapar($pg["nome_forma_pagamento"] ?? "Não informado") ?>
</td>

<td>
<?= escapar(formatarData($pg["data_pagamento"])) ?>
</td>

<td>

<?php if ($pg["status_pagamento"] == 1): ?>

<span class="status-ativo">Pago</span>

<?php else: ?>

<span class="status-inativo">Pendente</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="7" class="text-center py-3">
Nenhum pagamento encontrado.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

<!-- HISTÓRICO DE ACESSOS -->

<div class="painel-admin mb-4">

<h3 class="subtitulo mb-4">
Histórico de acessos à academia
</h3>

<div class="table-responsive">

<table class="table tabela-admin">

<thead>
<tr>
<th>ID</th>
<th>Assinatura</th>
<th>Plano</th>
<th>Data</th>
<th>Horário</th>
<th>Situação</th>
</tr>
</thead>

<tbody>

<?php if (count($acessos) > 0): ?>

<?php foreach ($acessos as $acesso): ?>

<tr>

<td><?= (int)$acesso["id_acesso"] ?></td>

<td>
<?= $acesso["id_assinatura"]
    ? "#" . (int)$acesso["id_assinatura"]
    : "Não informado" ?>
</td>

<td>
<?= $acesso["id_plano"]
    ? "Plano " . (int)$acesso["id_plano"]
    : "Não informado" ?>
</td>

<td>
<?= escapar(formatarData($acesso["data_acesso"])) ?>
</td>

<td>
<?= escapar(
    !empty($acesso["horario_acesso"])
        ? substr($acesso["horario_acesso"], 0, 5)
        : "Não informado"
) ?>
</td>

<td>

<?php if ($acesso["status_acesso"] == 1): ?>

<span class="status-autorizado">
Autorizado
</span>

<?php else: ?>

<span class="status-bloqueado">
Bloqueado
</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="6" class="text-center py-3">
Nenhum acesso registrado.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

<a href="adm_cliente.php" class="btn btn-upir">
Voltar para clientes
</a>

<?php else: ?>

<!-- LISTAGEM DE CLIENTES -->

<div class="painel-admin">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

<h3 class="subtitulo mb-0">
Clientes cadastrados
</h3>

<span class="text-secondary">
Total de clientes: <?= count($clientes) ?>
</span>

</div>

<div class="table-responsive">

<table class="table tabela-admin">

<thead>
<tr>
<th>ID</th>
<th>Nome</th>
<th>CPF</th>
<th>Telefone</th>
<th>E-mail</th>
<th>Detalhes</th>
</tr>
</thead>

<tbody>

<?php if (count($clientes) > 0): ?>

<?php foreach ($clientes as $item): ?>

<tr>

<td><?= (int)$item["id_cliente"] ?></td>

<td><?= escapar($item["nome_cliente"]) ?></td>

<td><?= escapar($item["cpf_cliente"]) ?></td>

<td><?= escapar($item["telefone_cliente"]) ?></td>

<td><?= escapar($item["email_cliente"]) ?></td>

<td>

<a
href="adm_cliente.php?id=<?= (int)$item["id_cliente"] ?>"
class="btn btn-detalhes btn-sm">
Ver detalhes
</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="6" class="text-center py-4">
Nenhum cliente cadastrado.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

<?php endif; ?>

</main>

<footer class="text-center py-4">

<p class="text-secondary">
UPIR Academia | Área administrativa
</p>

</footer>

</body>
</html>

<?php
$conexao->close();
?>
