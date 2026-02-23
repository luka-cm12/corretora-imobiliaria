-- SQL DUMP ESTRUTURA COMPLETA - CORRETORA CLÁUDIA COLOMBO
-- HostGator Deploy - Executar no phpMyAdmin após criar o banco

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Estrutura para tabela `auth_tokens`
-- --------------------------------------------------------
CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura para tabela `configuracoes`
-- --------------------------------------------------------
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `grupo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Configurações básicas da Corretora Cláudia Colombo
INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `grupo`) VALUES
(1, 'site_titulo', 'Corretora Cláudia Colombo', 'geral'),
(2, 'site_status', 'online', 'geral'),
(3, 'paginacao', '10', 'geral'),
(4, 'manutencao_login', '1', 'geral'),
(5, 'destaque_dias', '7', 'imoveis'),
(6, 'fotos_max', '20', 'imoveis'),
(7, 'recursos', 'piscina,garagem,academia,churrasqueira,area_gourmet', 'imoveis'),
(8, 'nome', 'Corretora Cláudia Colombo', 'corretora'),
(9, 'endereco', 'Caxias do Sul - RS', 'corretora'),
(10, 'telefone', '(54) 99999-9999', 'corretora'),
(11, 'email', 'contato@corretoraclaudia.com.br', 'corretora'),
(12, 'meta_desc', 'Encontre o imóvel dos seus sonhos em Caxias do Sul com a Corretora Cláudia Colombo', 'seo'),
(13, 'keywords', 'imóveis Caxias do Sul, casas, apartamentos, terrenos, corretora', 'seo'),
(14, 'cnpj', '00.000.000/0001-00', 'corretora'),
(15, 'creci', 'CRECI/RS 00000-F', 'corretora'),
(16, 'google_analytics', '', 'seo'),
(17, 'smtp_host', 'mail.corretoraclaudia.com.br', 'email'),
(18, 'smtp_porta', '587', 'email'),
(19, 'smtp_usuario', 'contato@corretoraclaudia.com.br', 'email'),
(20, 'smtp_senha', '', 'email'),
(21, 'remetente', 'contato@corretoraclaudia.com.br', 'email');

-- --------------------------------------------------------
-- Estrutura para tabela `contatos`
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Estrutura para tabela `imoveis`
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Estrutura para tabela `logs_sistema`
-- --------------------------------------------------------
CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(50) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura para tabela `proprietarios`
-- --------------------------------------------------------
CREATE TABLE `proprietarios` (
  `id_proprietario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura para tabela `sessoes_ativas`
-- --------------------------------------------------------
CREATE TABLE `sessoes_ativas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `data_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura para tabela `usuarios`
-- --------------------------------------------------------
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

-- USUÁRIO ADMINISTRADOR PADRÃO
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `status`, `criado_em`) VALUES
(1, 'Cláudia Colombo', 'admin@corretoraclaudia.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW());
-- SENHA PADRÃO: "password" - ALTERE IMEDIATAMENTE APÓS O PRIMEIRO LOGIN!

-- --------------------------------------------------------
-- ÍNDICES E RESTRIÇÕES
-- --------------------------------------------------------

-- Índices para tabela `auth_tokens`
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `usuario_id` (`usuario_id`);

-- Índices para tabela `configuracoes`
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grupo` (`grupo`,`chave`);

-- Índices para tabela `contatos`
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`);

-- Índices para tabela `imoveis`
ALTER TABLE `imoveis`
  ADD PRIMARY KEY (`id`);

-- Índices para tabela `logs_sistema`
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

-- Índices para tabela `proprietarios`
ALTER TABLE `proprietarios`
  ADD PRIMARY KEY (`id_proprietario`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

-- Índices para tabela `sessoes_ativas`
ALTER TABLE `sessoes_ativas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `usuario_id` (`usuario_id`);

-- Índices para tabela `usuarios`
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `auth_tokens` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `configuracoes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
ALTER TABLE `contatos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `imoveis` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `logs_sistema` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `proprietarios` MODIFY `id_proprietario` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sessoes_ativas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------
-- RESTRIÇÕES DE CHAVE ESTRANGEIRA
-- --------------------------------------------------------
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

-- ✅ BANCO DE DADOS CRIADO COM SUCESSO!
-- 🔑 Login padrão: admin@corretoraclaudia.com.br / password
-- 🚨 ALTERE A SENHA IMEDIATAMENTE APÓS O PRIMEIRO LOGIN!