<?php
// (clínica: o login.php não muda)
// Inicia a sessão para manter estados de login
session_start();


// Se o usuário já estiver logado, redireciona para a página principal
if (isset($_SESSION['usuario'])) {
    header('Location: principal.php');
    exit();
}


// Variável para armazenar mensagem de erro
$erro = "";


// Se a sessão expirou, as outras páginas mandam para cá com ?expirou=1
if (isset($_GET['expirou'])) {
    $erro = "Sua sessão expirou. Faça login novamente.";
}


// Verifica se o formulário foi submetido via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclui o arquivo config.php para usar $conn e conectar ao banco
    include 'config.php';


    // Recebe e sanitiza os dados enviados via formulário
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';


    // Prepara uma consulta segura para evitar SQL Injection
    // Busca só pelo nome: a senha está criptografada (hash) no banco e é conferida depois
    $sql = "SELECT * FROM usuarios WHERE nome=?";
    $stmt = $conn->prepare($sql);


    // Associa o parâmetro recebido ao statement SQL
    $stmt->bind_param('s', $nome);


    // Executa a consulta
    $stmt->execute();


    // Obtém o resultado da consulta
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();


    // Se encontrou o usuário e a senha digitada confere com o hash gravado, login é válido
    // password_verify compara a senha digitada com o hash criado por password_hash
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Salva os dados do usuário na sessão para uso nas demais páginas
        $_SESSION['usuario'] = $usuario;


        // Guarda o horário do login para controlar o tempo de expiração da sessão
        $_SESSION['ultimo_acesso'] = time();


        // Redireciona para a página principal do sistema
        header("Location: principal.php");
        exit();
    } else {
        // Caso contrário, prepara a mensagem de erro para exibir ao usuário
        $erro = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | Livraria</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
<header class="site-header">
    <span class="brand"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-3-2-6-2-9-1v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Zm0 0v15"/></svg>Livraria</span>
    <span class="header-note">Livros e leituras em um só lugar</span>
</header>
<main class="auth-layout">
    <section class="auth-intro">
        <h1>Um lugar para<br>suas próximas leituras.</h1>
        <p>Organize os livros, acompanhe as leituras e mantenha cada registro por perto.</p>
        <div class="intro-detail"><span></span>Seu acervo, bem cuidado.</div>
    </section>
    <form method="post" class="auth-form">
        <h2>Bem-vindo de volta</h2>
        <p class="form-caption">Entre para acessar a livraria.</p>
        <div class="field">
            <label for="username">Usuário</label>
            <input type="text" id="username" name="nome" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Senha</label>
            <input type="password" id="password" name="senha" autocomplete="current-password" required>
        </div>
        <button type="submit">Entrar</button>
        <?php if ($erro): ?>
            <p class="form-error" role="alert"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>
    </form>
</main>
</body>
</html>