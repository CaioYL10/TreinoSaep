<?php


// Inicia a sessão para manter o estado do usuário logado
session_start();


// Verifica se o usuário está logado acessando a variável de sessão 'usuario'
// Se não estiver logado, redireciona para a página de login e interrompe o script
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}


// Verifica o tempo de expiração da sessão: se ficou mais de 30 minutos sem usar o sistema,
// encerra a sessão e volta para o login avisando que expirou
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}


// Ainda está no prazo: renova o horário do último acesso
$_SESSION['ultimo_acesso'] = time();


// Armazena o nome do usuário logado em uma variável para exibir na página
$nome = $_SESSION['usuario']['nome'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Início | Livraria</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-page">
<header class="site-header">
    <span class="brand"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-3-2-6-2-9-1v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Zm0 0v15"/></svg>Livraria</span>
    <a class="logout-link" href="logout.php">Sair da conta</a>
</header>
<main>
    <section class="welcome">
        <h1>Bem-vindo, <?= htmlspecialchars($nome) ?>.</h1>
        <p>O que você gostaria de organizar hoje?</p>
    </section>
    <nav class="nav-grid" aria-label="Áreas da livraria">
        <a class="nav-card" href="livros.php"><span class="card-category">Acervo</span><h2>Livros</h2><p>Cadastre títulos e atualize os dados dos livros.</p><span class="card-action">Ver livros <span aria-hidden="true">↗</span></span></a>
        <a class="nav-card" href="clientes.php"><span class="card-category">Pessoas</span><h2>Clientes</h2><p>Consulte os clientes e mantenha os contatos atualizados.</p><span class="card-action">Ver clientes <span aria-hidden="true">↗</span></span></a>
        <a class="nav-card" href="leituras.php"><span class="card-category">Registro</span><h2>Nova leitura</h2><p>Registre a data, suas observações e a nota de uma leitura.</p><span class="card-action">Registrar leitura <span aria-hidden="true">↗</span></span></a>
        <a class="nav-card" href="historico.php"><span class="card-category">Consulta</span><h2>Histórico</h2><p>Reveja as leituras registradas e edite suas informações.</p><span class="card-action">Consultar histórico <span aria-hidden="true">↗</span></span></a>
    </nav>
</main>
</body>
</html>