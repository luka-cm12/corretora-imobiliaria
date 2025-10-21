# Deploy no Hostgator - Corretora Claudia

## Pré-requisitos
- Conta no Hostgator configurada
- Acesso ao cPanel
- Banco de dados MySQL criado no Hostgator

## Passo a Passo do Deploy

### 1. Preparar os arquivos para upload
- Certifique-se de que o arquivo `config.local.php` **NÃO será enviado** (apenas para ambiente local)
- Verifique se as configurações do `config.php` estão corretas para produção

### 2. Configurar o banco de dados no Hostgator
1. Acesse o cPanel do Hostgator (servidor: sh00066)
2. Vá em "MySQL Databases"
3. Crie um novo banco com o nome: `sh00066_corretor_corretora`
4. Crie um usuário: `sh00066_corretor_admin`
5. Defina uma senha segura e anote-a
6. Associe o usuário ao banco com todas as permissões

**IMPORTANTE**: O Hostgator adiciona automaticamente o prefixo do servidor (sh00066_) aos nomes.

### 3. Atualizar as credenciais no código
1. Edite o arquivo `private/config/config.php`
2. Na linha que define DB_PASS, substitua por sua senha real:
   ```php
   if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: ($__isLocal ? '' : 'SUA_SENHA_AQUI'));
   ```

### 4. Importar o banco de dados
1. Acesse o phpMyAdmin no cPanel
2. Selecione o banco criado
3. Importe o arquivo `database-hostgator.sql` ou `hg856221_corretor_corretora.sql`

### 5. Upload dos arquivos
Faça upload de todos os arquivos **EXCETO**:
- `config.local.php` (específico do ambiente local)
- `.git/` (se existir)
- Arquivos de backup `.sql` (opcional)

**INCLUIR obrigatoriamente**:
- `.htaccess` (configurações de segurança e performance)

### 6. Estrutura no servidor
```
public_html/
├── index.php
├── busca.php
├── contato.php
├── imoveis.php
├── imovel-detalhes.php
├── sobre.php
├── processa-contato.php
├── test_uploads.php
├── private/
│   ├── config/
│   │   └── config.php
│   ├── includes/
│   └── admin/
└── public/
    ├── assets/
    └── uploads/
```

### 7. Verificar permissões
- Pasta `public/uploads/`: 755 ou 777 (para upload de imagens)
- Arquivos PHP: 644
- Pastas: 755

### 8. Configuração automática (RECOMENDADO)
1. **Acesse: `https://seudominio.com/setup-deploy.php`**
2. Preencha as credenciais do banco que você criou
3. Clique em "Configurar Sistema"
4. **REMOVA o arquivo `setup-deploy.php` após a configuração**

### OU configuração manual:
1. Edite `private/config/config.php` e substitua a senha na linha DB_PASS
2. Teste a conexão: `seudominio.com/teste-conexao.php`
3. Verifique se o site carrega corretamente

### 9. Configuração de email (opcional)
No `config.php`, as configurações de email para Hostgator são:
- SMTP_HOST: `mail.seudominio.com`
- SMTP_PORT: `587` ou `465`
- SMTP_USERNAME: `contato@seudominio.com`
- SMTP_PASSWORD: `sua_senha_email`

## Troubleshooting

### Erro de conexão com banco
- Verifique se as credenciais no `config.php` estão corretas
- Confirme se o banco foi criado e o usuário associado
- Teste a conexão via phpMyAdmin

### Erro 500
- Verifique permissões dos arquivos
- Ative display_errors temporariamente para ver o erro específico
- Verifique se todos os arquivos foram enviados corretamente

### Imagens não carregam
- Verifique permissões da pasta `public/uploads/`
- Confirme se o caminho das imagens está correto
- Teste upload através do admin

## Comandos úteis

### Via FileZilla ou cPanel File Manager
```bash
# Definir permissões
chmod 755 public/uploads/
chmod 644 *.php
```

### Backup antes do deploy
```bash
# Fazer backup do banco atual (se já existir)
# Fazer backup dos arquivos atuais do servidor
```

## Checklist final
- [ ] Banco de dados criado e importado
- [ ] Arquivos enviados (exceto config.local.php)
- [ ] Permissões configuradas
- [ ] Teste de conexão com banco OK
- [ ] Site carregando corretamente
- [ ] Upload de imagens funcionando
- [ ] Formulário de contato testado
- [ ] SSL configurado (se aplicável)

## URLs importantes
- Site: `https://seudominio.com`
- Admin: `https://seudominio.com/private/admin/`
- Teste de conexão: `https://seudominio.com/teste-conexao.php`