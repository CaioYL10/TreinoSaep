<?php


// Inicia a sessão para acessar dados do usuário logado e verificar autenticação
session_start();


// Se o usuário não estiver autenticado, redireciona para a página de login e encerra o script
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
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


// Inclui o arquivo de configuração que contém a conexão com o banco de dados
include 'config.php';


// Inicializa variável para armazenar mensagens de alerta que poderão ser exibidas para o usuário
$alerta = '';


// Verifica se o formulário foi submetido via método POST para registrar o procedimento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e sanitiza os dados enviados pelo formulário
    $livro_id = intval($_POST['livro_id']); // Carro em que o procedimento foi feito
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];       
    $nota = $_POST['nota'] !== '' ? $_POST['nota'] : 0;


    // Verifica se os campos obrigatórios foram preenchidos
    if (!$livro_id || !$data || !$descricao) {
        $alerta = "Livro, data e descrição são obrigatórios.";
    } else {
        // Prepara a inserção do procedimento no banco de dados
        // O campo livro_id liga o procedimento ao carro (um carro tem muitos leituras)
        $stmt = $conn->prepare("INSERT INTO leituras (livro_id, data, descricao, nota) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $livro_id, $data, $descricao, $nota);
        $stmt->execute();


        // Redireciona para o histórico para mostrar o procedimento registrado
        header('Location: historico.php');
        exit;
    }
}
// Consulta SQL para buscar todos os carros (com o nome do dono) para popular o select no formulário
$sql = "SELECT l.id, l.nome, c.nome AS cliente_nome
        FROM livros l
        JOIN clientes c ON l.cliente_id = c.id
        ORDER BY l.nome";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar Procedimento</title>
    <!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>


<h2>Registrar Procedimento no Carro</h2>


<!-- Exibe mensagem de alerta em destaque, caso tenha sido definida -->
<?php if ($alerta): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 1em;">
        <?= htmlspecialchars($alerta) ?>
    </div>
<?php endif; ?>


<!-- Formulário de registro do procedimento -->
<form action="" method="post">
    <label for="livro_id">Livro:</label>
    <select id="livro_id" name="livro_id" required>
        <!-- Preenche o select com os carros obtidos do banco (nome, placa e dono) -->
        <?php while ($row = $res->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nome'] . ' (' . $row['cliente_nome'] . ')') ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>


    <label for="data">data:</label>
    <input type="date" id="data" name="data" required>
    <br><br>


    <label for="descricao">Descrição:</label>
    <input type="text" id="descricao" name="descricao" required>
    <br><br>


    <label for="nota">Nota:</label>
    <input type="number" step="0.01" min="0" max="10" id="nota" name="nota">
    <br><br>


    <button type="submit">Registrar</button>
</form>


<br>
<a href="principal.php">Voltar</a>


</body>
</html>
