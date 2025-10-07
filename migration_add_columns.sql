-- ================================================
-- Script de Migração SQL
-- Adiciona colunas faltantes na tabela usuarios
-- ================================================
-- 
-- Execute este script se você receber o erro:
-- "Unknown column 'atualizado_em' in 'field list'"
--
-- INSTRUÇÕES:
-- 1. Acesse o phpMyAdmin
-- 2. Selecione o banco de dados 'corretora_base'
-- 3. Clique na aba "SQL"
-- 4. Cole este script completo
-- 5. Clique em "Executar"
-- ================================================

-- Desabilita verificações temporariamente
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- ================================================
-- Adiciona coluna atualizado_em se não existir
-- ================================================

-- Verifica se a coluna existe
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'usuarios' 
    AND COLUMN_NAME = 'atualizado_em'
);

-- Adiciona a coluna se não existir
SET @sql_add_atualizado_em = IF(
    @column_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN atualizado_em TIMESTAMP NULL DEFAULT NULL AFTER criado_em',
    'SELECT "Coluna atualizado_em já existe" AS mensagem'
);

PREPARE stmt FROM @sql_add_atualizado_em;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================
-- Adiciona coluna ultimo_login se não existir
-- ================================================

-- Verifica se a coluna existe
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'usuarios' 
    AND COLUMN_NAME = 'ultimo_login'
);

-- Adiciona a coluna se não existir
SET @sql_add_ultimo_login = IF(
    @column_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN ultimo_login TIMESTAMP NULL DEFAULT NULL AFTER atualizado_em',
    'SELECT "Coluna ultimo_login já existe" AS mensagem'
);

PREPARE stmt FROM @sql_add_ultimo_login;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================
-- Restaura configurações
-- ================================================

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- ================================================
-- Verifica o resultado
-- ================================================

SELECT 'Migração concluída! Verifique a estrutura abaixo:' AS Status;

SHOW COLUMNS FROM usuarios;
