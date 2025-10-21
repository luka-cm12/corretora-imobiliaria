<?php
/**
 * Configurações específicas para o servidor sh00066 (Hostgator)
 * Use este arquivo como referência para as configurações de produção
 */

// ============================================
// INFORMAÇÕES DO SERVIDOR SH00066 (HOSTGATOR)
// ============================================

// Pacote de hospedagem: M_100
// Nome do servidor: sh00066
// Versão do MySQL: 8.0.43-34
// Versão do Apache: 2.4.65
// Sistema operacional: Linux
// Arquitetura: x86_64

// ============================================
// CONFIGURAÇÕES DE BANCO DE DADOS
// ============================================

// IMPORTANTE: O Hostgator adiciona automaticamente o prefixo "sh00066_" 
// aos nomes de banco de dados e usuários que você criar no cPanel

// Configurações para produção (servidor sh00066):
const PROD_DB_HOST = 'localhost';
const PROD_DB_NAME = 'sh00066_corretor_corretora';  // Nome com prefixo do servidor
const PROD_DB_USER = 'sh00066_corretor_admin';      // Usuário com prefixo do servidor
const PROD_DB_PASS = 'SUA_SENHA_AQUI';              // Defina sua senha segura

// ============================================
// CONFIGURAÇÕES DE EMAIL
// ============================================

// Para emails no Hostgator, use:
const PROD_SMTP_HOST = 'mail.seudominio.com.br';    // Ou smtp.hostgator.com
const PROD_SMTP_PORT = 587;                         // Ou 465 para SSL
const PROD_SMTP_USER = 'contato@seudominio.com.br'; // Email que você criou no cPanel
const PROD_SMTP_PASS = 'SUA_SENHA_EMAIL';           // Senha do email

// ============================================
// INSTRUÇÕES DE USO
// ============================================

/*
1. No cPanel do Hostgator:
   - Vá em "MySQL Databases"
   - Crie o banco: Digite apenas "corretor_corretora" 
     (o sistema criará automaticamente: sh00066_corretor_corretora)
   - Crie o usuário: Digite apenas "corretor_admin"
     (o sistema criará automaticamente: sh00066_corretor_admin)
   - Defina uma senha segura
   - Associe o usuário ao banco com todas as permissões

2. Importe o banco de dados:
   - Use o phpMyAdmin no cPanel
   - Selecione o banco sh00066_corretor_corretora
   - Importe o arquivo .sql

3. Configure o sistema:
   - Acesse: https://seudominio.com/setup-deploy.php
   - Os campos já virão preenchidos com os valores corretos
   - Insira apenas a senha que você definiu
   - Execute a configuração automática

4. Remova arquivos de setup:
   - setup-deploy.php
   - teste-conexao.php (após verificar funcionamento)
   - Este arquivo (server-sh00066-config.php)
*/

// ============================================
// VERIFICAÇÕES IMPORTANTES
// ============================================

/*
ANTES DO DEPLOY:
- [ ] Banco criado no cPanel: sh00066_corretor_corretora
- [ ] Usuário criado no cPanel: sh00066_corretor_admin
- [ ] Senha definida e anotada
- [ ] Banco importado via phpMyAdmin
- [ ] Todos os arquivos enviados (exceto config.local.php)
- [ ] Permissões da pasta public/uploads/ definidas

APÓS O DEPLOY:
- [ ] Setup executado via setup-deploy.php
- [ ] Site funcionando corretamente
- [ ] Conexão com banco OK (via teste-conexao.php)
- [ ] Admin acessível
- [ ] Arquivos de setup removidos
*/

?>