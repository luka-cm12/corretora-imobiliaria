-- =====================================================
-- SCRIPT PARA ADICIONAR CARACTERÍSTICA "ESTACIONAMENTO"
-- =====================================================
-- Execute este comando no seu banco de dados MySQL
-- Adiciona a coluna 'estacionamento' na tabela imoveis
-- =====================================================

-- Adicionar coluna estacionamento
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS estacionamento TINYINT(1) DEFAULT 0 
COMMENT 'Indica se o imóvel possui estacionamento (0=não, 1=sim)';

-- Verificar se a coluna foi adicionada
DESCRIBE imoveis;

-- Mostrar informações da nova coluna
SELECT 
    COLUMN_NAME as 'Nome da Coluna',
    COLUMN_TYPE as 'Tipo',
    IS_NULLABLE as 'Permite NULL',
    COLUMN_DEFAULT as 'Valor Padrão',
    COLUMN_COMMENT as 'Comentário'
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'imoveis' 
    AND COLUMN_NAME = 'estacionamento';

-- =====================================================
-- EXEMPLO DE USO:
-- =====================================================
-- Para marcar um imóvel como tendo estacionamento:
-- UPDATE imoveis SET estacionamento = 1 WHERE id = 1;

-- Para buscar imóveis com estacionamento:
-- SELECT * FROM imoveis WHERE estacionamento = 1;
-- =====================================================