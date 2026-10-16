# Sistema da Livraria

Aplicação PHP para cadastrar clientes e livros e acompanhar leituras.

## Estrutura

- `sistema/`: arquivos PHP e CSS da aplicação.
- `saep_db.sql`: estrutura do banco `livraria_db` e uma conta de demonstração, sem dados pessoais de clientes.
- `DER.svg`: diagrama entidade-relacionamento do banco.
- `prints/`: capturas das telas do sistema.

## Como executar

1. Copie a pasta `sistema` para a pasta pública do Apache, por exemplo `C:\Apache\htdocs\livraria`.
2. Importe `saep_db.sql` no MySQL pelo phpMyAdmin ou pelo cliente MySQL.
3. Se necessário, configure `LIVRARIA_DB_HOST`, `LIVRARIA_DB_USER`, `LIVRARIA_DB_PASSWORD`, `LIVRARIA_DB_NAME` e `LIVRARIA_CPF_KEY` no ambiente do Apache.
4. Abra `http://localhost/livraria/login.php`.

Conta de demonstração: **Caio** / **caio123**.