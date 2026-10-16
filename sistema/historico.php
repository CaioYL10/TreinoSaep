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

$sql = "SELECT r.id, r.data, r.descricao, r.nota,
               b.nome AS livro_nome, b.genero, b.data_lancamento,
               c.nome AS cliente_nome,
               CAST(AES_DECRYPT(c.cpf, '$chave') AS CHAR) AS cliente_cpf,
               c.telefone, c.email
        FROM leituras r
        JOIN livros b ON r.livro_id = b.id
        JOIN clientes c ON b.cliente_id = c.id
        ORDER BY r.data";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Leituras</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Histórico de Leituras</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Data da leitura</th>
        <th>Descrição</th>
        <th>Nota</th>
        <th>Livro</th>
        <th>Gênero</th>
        <th>Data de lançamento</th>
        <th>Cliente</th>
        <th>CPF</th>
        <th>Telefone</th>
        <th>E-mail</th>
        <th>Ações</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= (int) $row['id'] ?></td>
        <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['data']))) ?></td>
        <td><?= htmlspecialchars($row['descricao']) ?></td>
        <td><?= number_format((float) $row['nota'], 0, ',', '.') ?></td>
        <td><?= htmlspecialchars($row['livro_nome']) ?></td>
        <td><?= htmlspecialchars($row['genero']) ?></td>
        <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['data_lancamento']))) ?></td>
        <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($row['cliente_cpf'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['telefone'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
        <td><a href="editar_leitura.php?id=<?= (int) $row['id'] ?>">Editar leitura</a></td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="principal.php">Voltar</a>
</body>
</html>