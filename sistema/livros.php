<?php


// Inicia a sessão para verificar se o usuário está autenticado
session_start();


// Se não houver usuário logado, redireciona para a página de login
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


// Inclui o arquivo de configuração para conexão com o banco de dados
include 'config.php';


// Verifica se o formulário de cadastro foi enviado via POST e o botão 'add' foi clicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Prepara uma consulta segura para inserir um novo carro, evitando SQL Injection
    // O campo cliente_id liga o carro ao seu dono (um dono tem muitos carros)
    $stmt = $conn->prepare(
        "INSERT INTO livros (cliente_id, nome, genero, data_lancamento)
         VALUES (?, ?, ?, ?)"
    );
    // Liga os parâmetros enviados no formulário aos generodores da consulta preparada
    // (clínica: tipos "isssss" - a data de nascimento é texto 's', não inteiro 'i')
    $stmt->bind_param(
        "isss",
        $_POST['cliente_id'],   // (clínica: $_POST['tutor_id'])
        $_POST['nome'],    // (clínica: $_POST['nome'])
        $_POST['genero'],     // (clínica: $_POST['especie'])
        $_POST['data_lancamento'] // Data de lançamento
    );
    // Executa a inserção no banco
    $stmt->execute();
    // Após adicionar o carro, redireciona para a mesma página para atualizar a lista
    header("Location: livros.php");
    exit();
}


// Recebe o termo de busca enviado via GET, ou vazio se não houver
$busca = $_GET['busca'] ?? '';


// Protege o termo de busca contra SQL Injection antes de colocar na consulta
$termo = $conn->real_escape_string($busca);


// Monta a cláusula WHERE da consulta para filtrar carros pelo nome ou placa, se houver termo de busca
$where = $busca ? "WHERE l.nome LIKE '%$termo%'" : '';


// Consulta SQL que une carros e dono (JOIN) para mostrar o nome do dono de cada carro
// (clínica: "SELECT p.*, t.nome AS tutor_nome
//            FROM pets p
//            JOIN tutor t ON p.tutor_id = t.id
//            $where
//            ORDER BY p.nome")
$query = "SELECT l.*, c.nome AS cliente_nome
          FROM livros l
          JOIN clientes c ON l.cliente_id = c.id
          $where
          ORDER BY l.nome";


// Executa a consulta e armazena o resultado
$carros = $conn->query($query);


// Busca todos os donos para preencher o select do formulário de cadastro
$donos = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">


<h2>Cadastro de Livro</h2>


<!-- Formulário para pesquisa de carros -->
<form method="get">
    <input name="busca" placeholder="Buscar livro por nome..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Buscar</button>
    <a href="principal.php">Voltar</a>
</form>


<!-- Tabela exibindo os carros buscados/listados -->
<table border="1" cellpadding="5">
    <tr><th>Nome</th><th>Gênero</th><th>Data de lançamento</th><th>Cliente</th><th>Ações</th></tr>
    <?php while($row = $carros->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= htmlspecialchars($row['genero']) ?></td>
       
        <td><?= htmlspecialchars($row['data_lancamento'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
        <td>
            <!-- Link para editar o carro, passando o ID via GET -->
            <a href="editar_livro.php?id=<?= $row['id'] ?>">Editar</a>
            <!-- Link para deletar o carro, com confirmação (os procedimentos dele também são apagados) -->
            <a href="deletar_livro.php?id=<?= $row['id'] ?>" onclick="return confirm('Excluir o carro apaga também os procedimentos dele. Confirma?')">Excluir</a>
        </td>
      </tr>
    <?php endwhile; ?>
</table>


<!-- Formulário para cadastro de novo carro -->
<h3>Novo livro</h3>
<form method="post">
    <!-- Select com os donos cadastrados: o valor enviado é o id do dono -->
    <select name="cliente_id" required>
        <option value="">Selecione o cliente</option>
        <?php while ($d = $donos->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    
    <input name="nome" required placeholder="Nome">
    <input name="genero" required placeholder="Gênero">
    <input type="date" name="data_lancamento" required placeholder="Data de lançamento">
    <button name="add">Cadastrar</button>
</form>
