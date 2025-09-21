-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 22/09/2025 às 01:35
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `corretora_base`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$ExemploDeHashDeSenhaSegura123456', 'Administrador', 'admin@corretorabase.com.br', '2025-08-10 20:48:27'),
(2, 'usuario2', '$2y$10$OutroExemploDeHashDeSenhaSegura', 'Usuário Secundário', 'usuario2@corretorabase.com.br', '2025-08-10 20:48:27'),
(7, 'admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@corretoraclaudia.com.br', '2025-09-07 22:43:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `grupo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `grupo`) VALUES
(1, 'site_titulo', 'Imóveis & Cia', 'geral'),
(2, 'site_status', 'online', 'geral'),
(3, 'paginacao', '10', 'geral'),
(4, 'manutencao_login', '1', 'geral'),
(5, 'destaque_dias', '7', 'imoveis'),
(6, 'fotos_max', '20', 'imoveis'),
(7, 'recursos', 'piscina,garagem,academia', 'imoveis'),
(8, 'nome', 'Imóveis & Cia', 'corretora'),
(9, 'endereco', 'Av. Principal, 1234 - Centro', 'corretora'),
(10, 'telefone', '(00) 1234-5678', 'corretora'),
(11, 'email', 'contato@imoveisecia.com.br', 'corretora'),
(12, 'meta_desc', 'Encontre o imóvel dos seus sonhos com a Imóveis & Cia', 'seo'),
(13, 'keywords', 'imóveis, casas, apartamentos, terrenos, corretora', 'seo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contatos`
--

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

--
-- Despejando dados para a tabela `contatos`
--

INSERT INTO `contatos` (`id`, `nome`, `email`, `telefone`, `assunto`, `mensagem`, `imovel_interesse`, `created_at`, `imovel_id`, `imovel_titulo`) VALUES
(1, 'tst', 'teste@teste.com', '(99) 99999-9999', 'Orçamento', 'teste', 'teste', '2025-09-08 23:49:24', NULL, NULL),
(2, 'tst', 'teste@teste.com', '(99) 99999-9999', 'Orçamento', 'teste', 'teste', '2025-09-08 23:49:25', NULL, NULL),
(3, 'tst', 'teste@teste.com', '(99) 99999-9999', 'Orçamento', 'teste', 'teste', '2025-09-08 23:49:26', NULL, NULL),
(4, 'tst', 'teste@teste.com', '(99) 99999-9999', 'Orçamento', 'teste', 'teste', '2025-09-08 23:49:26', NULL, NULL),
(5, 'tst', 'teste@teste.com', '(99) 99999-9999', 'Orçamento', 'teste', 'teste', '2025-09-08 23:49:26', NULL, NULL),
(6, 'tst', 'teste@teste.com', '(99) 99999-9999', 'Orçamento', 'teste', 'teste', '2025-09-08 23:49:27', NULL, NULL),
(7, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '(54) 99634-1795', 'Outro', 'testee', 'teste', '2025-09-17 00:22:24', NULL, NULL),
(8, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '(54) 99634-1795', 'Visita', 'teeestee', 'rree', '2025-09-17 00:29:02', NULL, NULL),
(9, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '(54) 99634-1795', 'Visita', 'teeestee', 'rree', '2025-09-17 00:29:04', NULL, NULL),
(10, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '(54) 99634-1795', 'Informações', 'teste', 'teste', '2025-09-17 00:31:56', NULL, NULL),
(11, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '54996341795', NULL, 'teste', NULL, '2025-09-17 00:36:48', 7, 'Terreno em Condomínio'),
(12, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '54996341795', NULL, 'teste', NULL, '2025-09-17 00:36:54', 7, 'Terreno em Condomínio'),
(13, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '54996341795', NULL, 'tytt', NULL, '2025-09-19 23:03:05', 9, 'Kitnet Estudantil'),
(14, 'Lukaian Oliveira de Macedo', 'admin@admin.com', '54996341795', NULL, '312', NULL, '2025-09-21 21:30:44', 464, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `imoveis`
--

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imoveis`
--

INSERT INTO `imoveis` (`id`, `titulo`, `descricao`, `tipo`, `cidade`, `bairro`, `endereco`, `preco`, `area`, `quartos`, `banheiros`, `garagem`, `imagens`, `destaque`, `created_at`, `updated_at`) VALUES
(1, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:39:57', '2025-09-21 22:39:57'),
(2, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:40:02', '2025-09-21 22:40:02'),
(3, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:40:05', '2025-09-21 22:40:05'),
(4, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:40:21', '2025-09-21 22:40:21'),
(5, 'Casa aconchegante no bairro Jardim', 'Casa ampla com quintal e piscina', 'casa', 'São Paulo', 'Jardim América', 'Rua das Flores, 123', 850000.00, 120.50, 3, 2, 2, 'casa1.jpg,casa2.jpg', 1, '2025-09-21 22:40:42', '2025-09-21 22:40:42'),
(6, 'Apartamento moderno', 'Apartamento com varanda e vista para o parque', 'apartamento', 'Rio de Janeiro', 'Copacabana', 'Av. Atlântica, 456', 1200000.00, 85.00, 2, 2, 1, 'apt1.jpg,apt2.jpg', 0, '2025-09-21 22:40:42', '2025-09-21 22:40:42'),
(7, 'Terreno residencial', 'Terreno plano ideal para construção', 'terreno', 'Belo Horizonte', 'Savassi', 'Rua do Sol, 789', 300000.00, 250.00, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:40:42', '2025-09-21 22:40:42'),
(8, 'Loja comercial no centro', 'Ponto comercial com grande fluxo de pessoas', 'comercial', 'Curitiba', 'Centro', 'Rua XV de Novembro, 100', 600000.00, 60.00, NULL, 1, 2, 'loja1.jpg', 1, '2025-09-21 22:40:42', '2025-09-21 22:40:42'),
(9, 'Apartamento compacto', 'Ideal para estudantes ou casal', 'apartamento', 'Porto Alegre', 'Moinhos de Vento', 'Rua das Laranjeiras, 55', 400000.00, 50.00, 1, 1, 0, 'apt3.jpg', 0, '2025-09-21 22:40:42', '2025-09-21 22:40:42'),
(10, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:40:46', '2025-09-21 22:40:46'),
(11, 'Casa de campo', 'Casa ampla com jardim e área de lazer', 'casa', 'Campinas', 'Jardim das Flores', 'Rua das Oliveiras, 10', 950000.00, 150.00, 4, 3, 2, 'casa_campo1.jpg,casa_campo2.jpg', 1, '2025-09-21 22:41:26', '2025-09-21 22:41:26'),
(12, 'Apartamento de luxo', 'Apartamento com vista para o mar e varanda gourmet', 'apartamento', 'Florianópolis', 'Praia Brava', 'Av. Beira Mar, 200', 1800000.00, 120.00, 3, 3, 2, 'apt_luxo1.jpg,apt_luxo2.jpg', 1, '2025-09-21 22:41:26', '2025-09-21 22:41:26'),
(13, 'Terreno comercial', 'Terreno bem localizado para comércio ou estacionamento', 'terreno', 'São Paulo', 'Moema', 'Rua das Acácias, 45', 1200000.00, 300.00, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:41:26', '2025-09-21 22:41:26'),
(14, 'Loja de esquina', 'Loja ampla, ideal para restaurante ou comércio', 'comercial', 'Rio de Janeiro', 'Ipanema', 'Rua Visconde de Pirajá, 150', 950000.00, 80.00, NULL, 2, 2, 'loja_esquina.jpg', 1, '2025-09-21 22:41:26', '2025-09-21 22:41:26'),
(15, 'Apartamento compacto central', 'Apartamento pequeno, ótimo para casal ou estudante', 'apartamento', 'Belo Horizonte', 'Savassi', 'Rua da Bahia, 500', 350000.00, 45.00, 1, 1, 0, 'apt_compacto1.jpg', 0, '2025-09-21 22:41:26', '2025-09-21 22:41:26'),
(16, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:41:33', '2025-09-21 22:41:33'),
(17, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:41:50', '2025-09-21 22:41:50'),
(18, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:41:54', '2025-09-21 22:41:54'),
(19, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:42:15', '2025-09-21 22:42:15'),
(20, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:42:19', '2025-09-21 22:42:19'),
(21, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:42:20', '2025-09-21 22:42:20'),
(22, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:42:24', '2025-09-21 22:42:24'),
(23, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:42:28', '2025-09-21 22:42:28'),
(24, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:42:29', '2025-09-21 22:42:29'),
(25, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:34', '2025-09-21 22:54:34'),
(26, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:35', '2025-09-21 22:54:35'),
(27, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:35', '2025-09-21 22:54:35'),
(28, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:35', '2025-09-21 22:54:35'),
(29, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:36', '2025-09-21 22:54:36'),
(30, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:36', '2025-09-21 22:54:36'),
(31, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:40', '2025-09-21 22:54:40'),
(32, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:54', '2025-09-21 22:54:54'),
(33, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:56', '2025-09-21 22:54:56'),
(34, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:57', '2025-09-21 22:54:57'),
(35, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:54:58', '2025-09-21 22:54:58'),
(36, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:55:00', '2025-09-21 22:55:00'),
(37, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:55:01', '2025-09-21 22:55:01'),
(38, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:55:02', '2025-09-21 22:55:02'),
(39, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:55:03', '2025-09-21 22:55:03'),
(40, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:55:16', '2025-09-21 22:55:16'),
(41, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 22:55:18', '2025-09-21 22:55:18'),
(42, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 23:11:08', '2025-09-21 23:11:08'),
(43, '', '', 'casa', '', '', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 0, '2025-09-21 23:34:20', '2025-09-21 23:34:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_sistema`
--

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

--
-- Estrutura para tabela `sessoes_ativas`
--

CREATE TABLE `sessoes_ativas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `data_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

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

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `status`, `criado_em`, `atualizado_em`, `ultimo_login`) VALUES
(1, 'Administrador', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-09-10 01:14:14', NULL, NULL),
(2, 'Administrador', 'admin@edu.com', '123456', 'admin', 1, '2025-09-11 00:27:35', NULL, NULL),
(3, 'Administrador', 'admin2@teste.com', '$2y$10$3Ij8u4MGBnVYBzvVk6POQeU6LZ8pWha8nMpoY8c8IFDcnD5tBq5ye', 'admin', 1, '2025-09-13 23:13:35', NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grupo` (`grupo`,`chave`);

--
-- Índices de tabela `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `imoveis`
--
ALTER TABLE `imoveis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `sessoes_ativas`
--
ALTER TABLE `sessoes_ativas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `contatos`
--
ALTER TABLE `contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `imoveis`
--
ALTER TABLE `imoveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sessoes_ativas`
--
ALTER TABLE `sessoes_ativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `sessoes_ativas`
--
ALTER TABLE `sessoes_ativas`
  ADD CONSTRAINT `sessoes_ativas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
