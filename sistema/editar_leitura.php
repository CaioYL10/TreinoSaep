<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}

$_SESSION['ultimo_acesso'] = time();
include 'config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = $conn->prepare(
    'SELECT r.id, r.data, r.descricao, r.nota, b.nome AS livro_nome
     FROM leituras r
     JOIN livros b ON r.livro_id = b.id
     WHERE r.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$leitura = $result->fetch_assoc();

if (!$leitura) {
    http_response_code(404);
    echo 'Leitura não encontrada.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'] ?? '';
    $descricao = trim($_POST['descricao'] ?? '');
    $nota = (int) ($_POST['nota'] ?? 0);

    if ($data === '' || $descricao === '' || $nota < 0 || $nota > 10) {
        $erro = 'Informe a data, a descrição e uma nota entre 0 e 10.';
    } else {
        $stmt = $conn->prepare('UPDATE leituras SET data = ?, descricao = ?, nota = ? WHERE id = ?');
        $stmt->bind_param('ssii', $data, $descricao, $nota, $id);
        $stmt->execute();
        header('Location: historico.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar leitura</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Editar leitura</h2>
<p>Livro: <?= htmlspecialchars($leitura['livro_nome']) ?></p>

<?php if (isset($erro)): ?>
    <p role="alert"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<form method="post">
    <label for="data">Data da leitura:</label>
    <input type="date" id="data" name="data" value="<?= htmlspecialchars($_POST['data'] ?? $leitura['data']) ?>" required>
    <br><br>

    <label for="descricao">Descrição:</label>
    <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($_POST['descricao'] ?? $leitura['descricao']) ?>" required>
    <br><br>

    <label for="nota">Nota:</label>
    <input type="number" id="nota" name="nota" min="0" max="10" step="1" value="<?= (int) ($_POST['nota'] ?? $leitura['nota']) ?>" required>
    <br><br>

    <button type="submit">Salvar</button>
</form>

<a href="historico.php">Voltar ao histórico</a>
</body>
</html>