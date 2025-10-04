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