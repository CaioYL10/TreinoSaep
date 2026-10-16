<?php


// Inicia a sessão para acessar os dados do usuário logado
session_start();


// Verifica se o usuário está autenticado; caso contrário, redireciona para a página de login
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


// Inclui o arquivo de configuração para estabelecer a conexão com a base de dados
include 'config.php';


// Recebe o ID do livro a ser editado via parâmetro GET na URL; se não informado, assume 0
$id = $_GET['id'] ?? 0;


// Prepara e executa uma consulta para buscar os dados do livro com o ID informado
$sql = "SELECT * FROM livros WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$livro = $res->fetch_assoc();


// Se não encontrou o livro, exibe mensagem e termina a execução
if (!$livro) {
    echo "Livro não encontrado.";
    exit();
}


// Processa o formulário quando enviado via POST para atualizar o livro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados atualizados do formulário
    $cliente_id = $_POST['cliente_id'];   // (clínica: $tutor_id = $_POST['tutor_id'];)
    $nome = $_POST['nome'];     // (clínica: $nome = $_POST['nome'];)
    $genero = $_POST['genero'];       // (clínica: $especie = $_POST['especie'];)
    $data_lancamento = $_POST['data_lancamento'];           // (clínica: $data_nascimento = $_POST['data_nascimento'];)


    // Validação simples: dono, nome e genero são obrigatórios
    if (!$cliente_id || !$nome || !$genero) {
        echo "Dono, nome e genero são obrigatórios.";
    } else {
        // Atualiza os dados do livro no banco de dados
        $sql = "UPDATE livros SET cliente_id=?, nome=?, genero=?, data_lancamento=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $cliente_id, $nome, $genero, $data_lancamento, $id);
        $stmt->execute();


        // Redireciona o usuário de volta para a lista de livros após atualizar
        
        header("Location: livros.php");
        exit();
    }
}


// Busca todos os donos para preencher o select (permite trocar o dono do livro)
$donos = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">


<h2>Editar livro</h2>


<!-- Formulário para edição do livro com dados já preenchidos -->
<form method="post">
    <select name="cliente_id" required>
        <!-- Deixa selecionado o dono atual do livro -->
        <?php while ($d = $donos->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>" <?= $d['id'] == $livro['cliente_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    <input name="nome" placeholder="nome" value="<?= htmlspecialchars($livro['nome']) ?>" required>
    <input name="genero" placeholder="genero" value="<?= htmlspecialchars($livro['genero']) ?>" required>
    <input type="date" name="data_lancamento" required placeholder="data_lancamento" value="<?= htmlspecialchars($livro['data_lancamento'] ?? '') ?>">
    <button>Salvar</button>
</form>


<!-- Link para voltar à página de listagem de livros -->
<a href="livros.php">Voltar</a>