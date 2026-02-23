# 🚀 GUIA COMPLETO DE DEPLOY - CORRETORA CLÁUDIA COLOMBO
## HostGator - www.corretoraclaudia.com.br

---

## 📋 PRÉ-REQUISITOS

### 1. Informações do HostGator
- ✅ Conta ativa no HostGator
- ✅ Domínio `corretoraclaudia.com.br` apontado para o servidor
- ✅ Acesso ao cPanel

### 2. Softwares Necessários
- **Cliente FTP**: FileZilla (recomendado) ou WinSCP
- **Editor de texto**: VS Code, Notepad++
- **Navegador**: Para testar o site

---

## 🗄️ PASSO 1: CONFIGURAR BANCO DE DADOS

### 1.1 Criar Banco MySQL no cPanel
1. Acesse o **cPanel** do HostGator
2. Vá em **"MySQL Databases"** ou **"Bancos de Dados MySQL"**
3. **Criar novo banco:**
   - Nome sugerido: `corretor_corretora`
   - Clique em **"Create Database"**
4. **Criar usuário:**
   - Usuário sugerido: `corretor_admin`
   - Senha forte: `Corretora2024@Segura!`
   - Clique em **"Create User"**
5. **Associar usuário ao banco:**
   - Selecione o banco e usuário criados
   - Marque **"ALL PRIVILEGES"**
   - Clique em **"Make Changes"**

### 1.2 Estrutura do Banco (SQL COMPLETO - Executar no phpMyAdmin)
```sql
-- SQL DUMP COMPLETO DA CORRETORA CLÁUDIA COLOMBO
-- Executar TODO este código no phpMyAdmin após criar o banco

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Estrutura para tabela `auth_tokens`
CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela `configuracoes`
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `grupo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados para a tabela `configuracoes` - ATUALIZE COM SEUS DADOS REAIS
INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `grupo`) VALUES
(1, 'site_titulo', 'Corretora Cláudia Colombo', 'geral'),
(2, 'site_status', 'online', 'geral'),
(3, 'paginacao', '10', 'geral'),
(4, 'manutencao_login', '1', 'geral'),
(5, 'destaque_dias', '7', 'imoveis'),
(6, 'fotos_max', '20', 'imoveis'),
(7, 'recursos', 'piscina,garagem,academia', 'imoveis'),
(8, 'nome', 'Corretora Cláudia Colombo', 'corretora'),
(9, 'endereco', 'Av. Principal, 1234 - Centro', 'corretora'),
(10, 'telefone', '(11) 99999-9999', 'corretora'),
(11, 'email', 'contato@corretoraclaudia.com.br', 'corretora'),
(12, 'meta_desc', 'Encontre o imóvel dos seus sonhos com a Corretora Cláudia Colombo', 'seo'),
(13, 'keywords', 'imóveis, casas, apartamentos, terrenos, corretora', 'seo'),
(14, 'cnpj', '', 'corretora'),
(15, 'creci', '', 'corretora'),
(16, 'google_analytics', '', 'seo'),
(17, 'smtp_host', '', 'email'),
(18, 'smtp_porta', '587', 'email'),
(19, 'smtp_usuario', '', 'email'),
(20, 'smtp_senha', '', 'email'),
(21, 'remetente', '', 'email');

-- Estrutura para tabela `contatos`
CREATE TABLE `contatos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `assunto` varchar(50) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `imovel_interesse` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `imovel_id` int(11) DEFAULT NULL,
  `imovel_titulo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela `imoveis`
CREATE TABLE `imoveis` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `tipo` enum('casa','apartamento','terreno','comercial') NOT NULL,
  `cidade` varchar(50) NOT NULL,
  `bairro` varchar(50) NOT NULL,
  `endereco` varchar(100) DEFAULT NULL,
  `preco` decimal(12,2) NOT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `quartos` int(11) DEFAULT NULL,
  `banheiros` int(11) DEFAULT NULL,
  `garagem` int(11) DEFAULT NULL,
  `imagens` text DEFAULT NULL,
  `destaque` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_proprietario` int(11) DEFAULT NULL,
  `caracteristicas` text DEFAULT NULL,
  `cep` char(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela `logs_sistema`
CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(50) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela `proprietarios`
CREATE TABLE `proprietarios` (
  `id_proprietario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela `sessoes_ativas`
CREATE TABLE `sessoes_ativas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `data_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela `usuarios`
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','corretor') NOT NULL DEFAULT 'corretor',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NULL DEFAULT NULL,
  `ultimo_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- USUÁRIO ADMINISTRADOR PADRÃO - ALTERE A SENHA APÓS O PRIMEIRO LOGIN!
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `status`, `criado_em`) VALUES
(1, 'Administrador', 'admin@corretoraclaudia.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW());
-- SENHA PADRÃO: "password" (ALTERE IMEDIATAMENTE!)

-- Índices para tabelas
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `usuario_id` (`usuario_id`);

ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grupo` (`grupo`,`chave`);

ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `imoveis`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

ALTER TABLE `proprietarios`
  ADD PRIMARY KEY (`id_proprietario`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `sessoes_ativas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `usuario_id` (`usuario_id`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- AUTO_INCREMENT
ALTER TABLE `auth_tokens` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `configuracoes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
ALTER TABLE `contatos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `imoveis` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `logs_sistema` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `proprietarios` MODIFY `id_proprietario` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sessoes_ativas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- Restrições de chave estrangeira
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `sessoes_ativas`
  ADD CONSTRAINT `sessoes_ativas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
```

---

## 📁 PASSO 2: PREPARAR ARQUIVOS PARA UPLOAD

### 2.1 Configurar Arquivo de Produção
1. Renomeie o arquivo `config.hostgator.php` para `config.local.php`
2. Edite as credenciais do banco:

```php
<?php
// private/config/config.local.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'corretor_corretora'); // Seu banco real
define('DB_USER', 'corretor_admin');     // Seu usuário real
define('DB_PASS', 'Corretora2024@Segura!'); // Sua senha real

// Configurações de email
define('SMTP_HOST', 'mail.corretoraclaudia.com.br');
define('SMTP_USERNAME', 'contato@corretoraclaudia.com.br');
define('SMTP_PASSWORD', 'SuaSenhaEmail123!');
```

### 2.2 Verificar Estrutura de Arquivos
```
📁 Arquivos para upload:
├── .htaccess (novo - otimizado)
├── index.php
├── contato.php
├── sobre.php
├── imoveis.php
├── imovel-detalhes.php
├── busca.php
├── processa-contato.php
├── test_uploads.php
├── 📁 private/
│   ├── 📁 admin/
│   ├── 📁 config/
│   │   ├── config.php
│   │   └── config.local.php (NOVO)
│   ├── 📁 includes/
│   └── 📁 imoveis/
└── 📁 public/
    ├── 📁 assets/
    └── 📁 uploads/
```

---

## 🌐 PASSO 3: UPLOAD VIA FTP

### 3.1 Configurar FileZilla
1. **Host**: ftp.corretoraclaudia.com.br (ou o fornecido pelo HostGator)
2. **Usuário**: Seu usuário FTP do cPanel
3. **Senha**: Sua senha FTP do cPanel
4. **Porta**: 21 (FTP) ou 22 (SFTP)

### 3.2 Fazer Upload
1. Conecte via FTP
2. Navegue até `/public_html/`
3. **Upload completo**: Arraste todos os arquivos e pastas
4. **Aguarde**: Upload pode demorar (muitas imagens)

### 3.3 Verificar Permissões
- **Pasta uploads/**: 755 ou 777
- **Arquivos PHP**: 644
- **Pasta private/**: 755 (já protegida por .htaccess)

---

## 🔧 PASSO 4: CONFIGURAÇÕES FINAIS

### 4.1 Testar Conexão com Banco
1. Acesse: `https://www.corretoraclaudia.com.br/test_uploads.php`
2. Deve mostrar: "✅ Conexão com banco OK"
3. Se erro: Verifique credenciais em `config.local.php`

### 4.2 Importar Seus Dados Existentes (OPCIONAL)
Se você quiser manter os imóveis e dados do seu ambiente local:
1. No seu phpMyAdmin local, exporte apenas os dados das tabelas:
   - `imoveis` (seus imóveis cadastrados)
   - `proprietarios` (proprietários cadastrados)
   - `contatos` (contatos recebidos)
2. No HostGator, após executar o SQL acima, importe esses dados
3. **IMPORTANTE**: As imagens dos imóveis precisam ser copiadas via FTP para `/public/uploads/`

### 4.2 Configurar Email (Opcional)
1. No cPanel, vá em **"Email Accounts"**
2. Crie: `contato@corretoraclaudia.com.br`
3. Configure SMTP em `config.local.php`

### 4.3 SSL/HTTPS
1. No cPanel, vá em **"SSL/TLS"**
2. Ative **"Force HTTPS Redirect"**
3. Ou use o `.htaccess` já configurado

---

## ✅ PASSO 5: VALIDAÇÃO E TESTES

### 5.1 Checklist de Testes
- [ ] **Homepage**: `https://www.corretoraclaudia.com.br/`
- [ ] **Listagem de imóveis**: `/imoveis.php`
- [ ] **Detalhes do imóvel**: Clique em qualquer imóvel
- [ ] **Busca**: Teste filtros de busca
- [ ] **Contato**: Envie um formulário de teste
- [ ] **Admin**: `/private/admin/login.php`
  - Usuário: `admin@corretoraclaudia.com.br`
  - Senha: `password` (ALTERE IMEDIATAMENTE!)

### 5.3 Importar Imagens dos Imóveis
Se você tem imóveis cadastrados localmente:
1. Copie toda a pasta `/public/uploads/` do seu projeto local
2. Faça upload via FTP para o servidor HostGator
3. Verifique se as imagens estão carregando nos imóveis

### 5.2 Problemas Comuns e Soluções

**❌ Erro "Internal Server Error"**
- Verifique permissões dos arquivos
- Confira se `.htaccess` está correto
- Veja logs de erro no cPanel

**❌ "Erro ao conectar ao banco"**
- Confirme credenciais em `config.local.php`
- Verifique se usuário tem privilégios no banco

**❌ Imagens não carregam**
- Verifique permissões da pasta `/public/uploads/`
- Confirme upload das imagens via FTP

**❌ CSS/JS não funciona**
- Force atualização: Ctrl+F5
- Verifique caminhos em `config.php`

---

## 🔐 PASSO 6: SEGURANÇA PÓS-DEPLOY

### 6.1 Alterar Senhas Padrão
1. **Admin do sistema**: Login em `/private/admin/`
2. **Banco de dados**: Use senhas fortes
3. **Email**: Configure senhas complexas

### 6.2 Backups
1. **Banco**: Backup semanal via cPanel
2. **Arquivos**: Backup mensal dos uploads
3. **Automático**: Configure no HostGator se disponível

### 6.3 Monitoramento
- Configure alertas de erro no cPanel
- Monitore logs de acesso
- Verifique desempenho regularmente

---

## 📞 SUPORTE

**Em caso de problemas:**
1. HostGator Suporte: Chat 24/7
2. Documentação: [help.hostgator.com](https://help.hostgator.com)
3. Logs de erro: cPanel > Error Logs

**Credenciais importantes para anotar:**
- 🔗 URL: https://www.corretoraclaudia.com.br/
- 🗄️ Banco: `corretor_corretora`
- 👤 Admin: `admin@corretoraclaudia.com.br`
- 📧 Email: `contato@corretoraclaudia.com.br`

---

## ✨ PRÓXIMOS PASSOS (Opcional)

1. **Configurar Google Analytics**
2. **Otimizar SEO** (meta tags, sitemap)
3. **Configurar Google Search Console**
4. **Implementar sistema de newsletter**
5. **Adicionar chat online**

---

**🎉 Parabéns! Sua corretora está online em:**
# https://www.corretoraclaudia.com.br/