-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 06/11/2025 às 21:27
-- Versão do servidor: 8.0.43-34
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `hg856221_corretora`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `proprietarios`
--

CREATE TABLE `proprietarios` (
  `id_proprietario` int NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_general_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `endereco` text COLLATE utf8mb4_general_ci,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_documento` enum('cpf','cnpj') COLLATE utf8mb4_general_ci DEFAULT 'cpf' COMMENT 'Tipo do documento: cpf para pessoa física, cnpj para pessoa jurídica',
  `cnpj` varchar(18) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'CNPJ do proprietário pessoa jurídica (formato: 00.000.000/0000-00)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `proprietarios`
--

INSERT INTO `proprietarios` (`id_proprietario`, `nome`, `cpf`, `telefone`, `email`, `endereco`, `data_cadastro`, `tipo_documento`, `cnpj`) VALUES
(2, 'Alex Antônio Fistarol', '96050489068', '119941859005', '', '', '2025-10-20 20:41:42', 'cpf', NULL),
(3, 'PEDRO DE MATOS FERNANDES', '17958229020', '51997043280', 'monicafelisbertodesouza@gmail.com', 'RUA FLANBOIAN, N° 979 QUATRO LAGOS ARROIO DO SAL/RS', '2025-10-28 19:10:19', 'cpf', NULL),
(4, 'DIEGO', '32145687901', '54996600968', 'diego1@gmail.com', 'CAXIAS DO SUL/RS', '2025-10-28 19:36:08', 'cpf', NULL),
(7, 'Gilvan Gomes da Rosa', '02555021094', '48991822799', 'TESTE@TESTE.com', '', '2025-10-29 19:06:08', 'cpf', NULL),
(10, 'Ana', '75347148004', '54991216872', 'admin@corretora.com', '', '2025-10-29 20:15:57', 'cpf', NULL),
(11, 'QUELLEN DE FREITAS XAVIER', '34562189705', '4893505-5541', 'quellenxavier@gmail.com', 'RUA CLODOVEU GUILHERME ZANOTTO, 105 CASA 03 BAIRRO CHARQUEADAS CAXIAS DO SUL/RS', '2025-10-30 14:56:19', 'cpf', NULL),
(12, 'ESTANISLAU POZZOBON', '40795470053', '5499983-8803', 'pozzobon@raconcaxias.com.br', 'RUA ALVOREDO PELLINE, 248', '2025-10-30 15:25:23', 'cpf', NULL),
(13, 'ADAUTO VIEZZE', '56789009831', '5499974--5660', 'adautoziezze@gmail.com', 'RUA ALBINO ANTÔNIO ALBÉ, 24 BAIRRO DE LAZZER', '2025-10-30 15:54:43', 'cpf', NULL),
(14, 'ROSAGELA 1', '43278965431', '47 99646-4300', 'rosangela1@gmail.com', 'RUA ADELAR MOSCHEN, 579 BELA VISTA LOTEAMENTO PAIQUERÊ.', '2025-10-30 16:36:44', 'cpf', NULL),
(16, 'Jucerlei Mendes', '02568742004', '54990725665', 'lukaoliveiramacedo@yahoo.com', '', '2025-11-01 14:34:17', 'cpf', NULL),
(22, 'VIbru Participações e Empreendimentos Imobiliários', '', '54999874632', 'vibrucaxias@hotmail.com', '', '2025-11-05 16:10:32', 'cpf', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `proprietarios`
--
ALTER TABLE `proprietarios`
  ADD PRIMARY KEY (`id_proprietario`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `proprietarios`
--
ALTER TABLE `proprietarios`
  MODIFY `id_proprietario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
