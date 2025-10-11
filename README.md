corretora-base/
├── public/                     # Arquivos públicos
│   ├── assets/
│   │   ├── css/admin.css
|   |   |-style.css ✔
│   │   ├── images/
│   │   ├── js/ ✔
|   |   ├── main.js ✔
|   |   ├── lightbox.js ✔
|   |   ├── form-validation.js ✔
|   |   └── mobile-menu.js ✔
|   |   |
│   │   └── fonts/
│   ├── uploads/ .htaccess ✔  
|                     # Imagens dos imóveis
├── private/                    # Área administrativa
│   ├── admin/✔
│   ├── includes/✔✔
|   ├── header.php ✔
|   ├── footer.php ✔
|   ├── db.php ✔
|   ├── functions.php ✔
|   └── auth.php ✔
|   |
│   └── imoveis/adicionar.php ✔
├── index.php ✔                    # Página inicial
├── contato.php ✔
├── sobre.php ✔
├── imoveis.php ✔ 
├── imovel-detalhes.php ✔
├── busca.php ✔
└── README.md ✔


## Deploy na Hostinger

1) PHP e estrutura
- Selecione PHP 8.1+ no painel da Hostinger.
- Envie todos os arquivos para a pasta public_html (ou um subdiretório se preferir).
- A pasta `private/` já possui `.htaccess` para bloquear acesso direto.
- A pasta `public/uploads/` tem `.htaccess` para impedir execução de PHP e listagem.

2) Banco de dados
- Crie um banco MySQL no painel (anote host, nome, usuário e senha).
- Importe seu dump (crie um backup pelo phpMyAdmin local se ainda não tiver).
- Informe as credenciais na aplicação:
	- Opção A: criar o arquivo `private/config/config.local.php` com:
		```php
		<?php
		define('DB_HOST', 'seu_host');
		define('DB_NAME', 'seu_db');
		define('DB_USER', 'seu_usuario');
		define('DB_PASS', 'sua_senha');
		```
	- Opção B: definir variáveis de ambiente (DB_HOST, DB_NAME, DB_USER, DB_PASS) no painel.

3) URL base
- `BASE_URL` agora é calculado automaticamente (considera HTTPS e subpasta). Não é preciso alterar manualmente.

4) Permissões
- Garanta escrita em `public/uploads/` (755 geralmente é suficiente na Hostinger).

5) E-mail (SMTP)
- Configure os campos de SMTP pela área admin em `Configurações` ou ajuste PHPMailer conforme necessário.

6) Debug
- Em produção, a exibição de erros é desativada automaticamente. Use os logs do painel se precisar investigar.

## Deploy na HostGator

1) PHP e estrutura
- No cPanel, selecione PHP 8.1+ no "Select PHP Version".
- Faça upload de todos os arquivos para a pasta `public_html/` (ou um subdiretório, se quiser manter em `public_html/site/`).
- A pasta `private/` já tem `.htaccess` para impedir acesso direto a arquivos sensíveis; mantenha-a fora de rotas públicas.
- A pasta `public/uploads/` deve existir e estar gravável (veja permissões abaixo).

2) Banco de dados MySQL
- Crie um banco no cPanel (MySQL Databases) e um usuário com senha forte; adicione o usuário ao banco com "All Privileges".
- Anote o host do MySQL (geralmente `localhost` na HostGator compartilhada).
- Importe o dump pelo phpMyAdmin.
- Crie o arquivo `private/config/config.local.php` no servidor com as credenciais:
	```php
	<?php
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'SEU_PREFIXO_banco');
	define('DB_USER', 'SEU_PREFIXO_usuario');
	define('DB_PASS', 'sua_senha_forte');
	```
	Observação: o projeto já tenta definir padrões automaticamente, mas em produção é melhor declarar as credenciais reais via `config.local.php`.

3) URL base (BASE_URL)
- Detectada automaticamente (HTTP/HTTPS e subpasta). Não precisa editar manualmente.

4) Permissões
- `public/uploads/`: 755 geralmente funciona. Se precisar para upload via PHP, ajuste via Gerenciador de Arquivos do cPanel.
- `.htaccess` já bloqueia execução de PHP dentro de `uploads`.

5) E-mail (SMTP)
- Se for usar envio de e-mails, configure SMTP do seu domínio no cPanel (Email Accounts) e ajuste no sistema conforme necessário.

6) Segurança
- `private/config/.htaccess` e `private/includes/.htaccess` bloqueiam acesso direto.
- Evite deixar arquivos `.sql`, `.zip` ou backups na raiz pública.

7) Troubleshooting
- Tela em branco/erro 500: verifique "Errors" no cPanel e permissões.
- Erro de banco: confira `config.local.php` e se o usuário tem privilégios no banco.