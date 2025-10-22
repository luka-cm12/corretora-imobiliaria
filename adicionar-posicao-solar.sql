-- Adiciona coluna de posição solar na tabela de imóveis
-- Execute este SQL no seu banco de dados para adicionar o novo campo

-- Versão simples e compatível
ALTER TABLE imoveis 
ADD COLUMN posicao_solar VARCHAR(10) NULL 
COMMENT 'Posição solar do imóvel: norte, sul, leste, oeste';

-- Verificar se a coluna foi criada com sucesso
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'corretora_imobiliaria'
    AND TABLE_NAME = 'imoveis' 
    AND COLUMN_NAME = 'posicao_solar';

-- Opcional: Adicionar índice para melhor performance em consultas
-- CREATE INDEX idx_posicao_solar ON imoveis(posicao_solar);