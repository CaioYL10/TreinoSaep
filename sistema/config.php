<?php
// Ajuste estas variáveis ou configure-as no ambiente do Apache.
$servername = getenv('LIVRARIA_DB_HOST') ?: 'localhost';
$username = getenv('LIVRARIA_DB_USER') ?: 'root';
$password = getenv('LIVRARIA_DB_PASSWORD') ?: '';
$dbname = getenv('LIVRARIA_DB_NAME') ?: 'livraria_db';
$chave = getenv('LIVRARIA_CPF_KEY') ?: 'troque-esta-chave-local';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Falha na conexão com o banco: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>