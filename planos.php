<!DOCTYPE html>
<html lang="pt-br">
<head>
        <title>Upir Academia</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- Bootstrap CSS v5.3.8 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="index.css">
    </head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="menu">
        <div class="container px-4">
        <a class="navbar-brand">
            <img src="Imagens/icone.png" alt="img-fluid" style="max-height: 70px;width: 70px;">
        </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria
                label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html" style="color: #5f575f;">Tela inicial</a></li>
                    <li class="nav-item"><a class="nav-link" href="espaco.html" style="color: #5f575f;">Espaço</a></li>
                    <li class="nav-item"><a class="nav-link" href="aulas.html" style="color: #5f575f;">Aulas</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php" style="color: #cf0139;"><u>Já sou aluno</u></a></li>
                    <li class="nav-item"><a class="nav-link" href="cadastro.php" style="background-color: #cf0139;">Se torne aluno</a></li>
                </ul>
            </div>
           </div>
        </nav>

        <div class="row justify-content-center cont ">
             <div class="col-xl-11 col-sm-4 text-center" >  <br><br><br>
                <h1 style="color: #cf0139;" >Conheça nossos planos e preços</h1>
                <h2 style="color: #c8d1d9;">Compare os benefícios de cada plano e comece a viver uma vida mais saudável!</h2>
                <br><br><br>
            </div>
        </div>
 </div>
<?php
require_once "conexao.php";

$sql = "SELECT
            p.id_plano,
            p.valor_plano,
            b.descricao_beneficio
        FROM Plano p
        LEFT JOIN Plano_Beneficio pb
            ON p.id_plano = pb.id_plano
        LEFT JOIN Beneficio b
            ON pb.id_beneficio = b.id_beneficio
        ORDER BY p.id_plano, b.id_beneficio";

$resultado = $conexao->query($sql);

$planos = [];

if ($resultado) {

    while ($linha = $resultado->fetch_assoc()) {

        $id = $linha['id_plano'];

        if (!isset($planos[$id])) {
            $planos[$id] = [
                'id' => $id,
                'valor' => $linha['valor_plano'],
                'beneficios' => []
            ];
        }

        if ($linha['descricao_beneficio'] !== null) {
            $planos[$id]['beneficios'][] =
                trim($linha['descricao_beneficio']);
        }
    }

} else {
    die("Erro ao consultar os planos.");
}
?>

<div class="container">

    <div class="row justify-content-center g-4">

        <?php foreach ($planos as $plano): ?>

            <div class="col-xl-4 col-md-6">

                <div class="card h-100 p-3">

                    <div class="card-body d-flex flex-column">

                        <h2>
                            Plano 0<?= (int)$plano['id'] ?>
                        </h2>

                        <h5>O que nosso plano oferece:</h5>

                        <ul class="list-group list-group-flush my-3">

                            <?php foreach ($plano['beneficios'] as $beneficio): ?>

                                <li class="list-group-item">
                                    ✓ <?= htmlspecialchars($beneficio) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                        <p>Mensal</p>

                        <h2>
                            R$ <?= number_format(
                                (float)$plano['valor'],
                                2,
                                ',',
                                '.'
                            ) ?>
                        </h2>

                        <div class="mt-auto">

                            <a href="cadastro.php?id_plano=<?= (int)$plano['id'] ?>"
                               class="btn btn-danger w-100">
                                Escolher este plano
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<br><br><br>
        <div class="container">
            <div class="row justify-content-center cont">
                <div class="col-xl-12 col-sm-12  black">
                    <img width="100%" height="100% px" src="Imagens/beneficios.jpg" alt="img-fluid">
                </div>
            </div>
        </div>

         <br><br><br><br>
         <div  class="container">
                <ul class=  "row justify-content-center cont">
                   <div class="col-xl-3 col-sm-4 " style="text-align: center;">
                        <h2 style="color: #3c3f42;">Nossa academia</h2>
                        <br>
                        <li class="nav-item"><a class="nav-link" href="index.html" style="color: #54585c;"><u>Tela Inicial </u></a></li>
                        <li class="nav-item"><a class="nav-link" href="espaco.html" style="color: #54585c;"><u>Nosso espaço</u></a></li>
                        <li class="nav-item"><a class="nav-link" href="aulas.html" style="color: #54585c;"><u>Aulas</u></a></li>
                   </div>
                   
                    <div class="col-xl-3 col-sm-4" style="text-align: center;" >
                        <h2 style="color: #3c3f42;">Institucional</h>
                        <br>
                        <li class="nav-item"><a class="nav-link" href="trabalhe.html" style="color: #54585c;"><u>Trabalhe conosco</u></a></li>
                        <li class="nav-item"><a class="nav-link" href="duvidas.html" style="color: #54585c;"><u>Dúvidas frequentes</u></a></li>
                   </div>
                </ul>
         </div>

         <br><br>
         <div style="background-color: #917d87; width: 100%; height: 100%; text-align: center; color: white; ">
                 <br><br><br><br>
                <h1>UPIR ESCOLA DE GINÁSTICA E DANÇA S.A</h1>
                <br>
                <p>Avenida Paulista, nº 1294, 2º andar, Bela Vista,</p>
                <p>CEP 01310-100</p>
                <p>CNPJ: 07.594.978/0001-78</p>
                <br><br><br>
         </div>
</body>
</html>