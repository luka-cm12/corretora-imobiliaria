-- Script para adicionar os campos financeiros e atualizar tipos de imóveis
-- Execute este script no banco corretora_base

-- 1. Adicionar as colunas de valores internos
ALTER TABLE imoveis 
ADD COLUMN valor_condominio DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor mensal do condomínio para uso interno',
ADD COLUMN valor_iptu DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor anual do IPTU para uso interno';

-- 2. Atualizar o campo tipo para incluir todos os novos tipos
ALTER TABLE imoveis 
MODIFY COLUMN tipo ENUM(
    'casa',
    'casa_condominio', 
    'apartamento',
    'apartamento_mobiliado',
    'sobrado',
    'chacara',
    'semi_mobiliado',
    'terreno',
    'comercial'
) NOT NULL COMMENT 'Tipo do imóvel com todos os novos tipos implementados';

-- 3. Verificar as alterações
SHOW COLUMNS FROM imoveis;

-- 4. Exemplo de consulta para verificar os novos campos
SELECT id, titulo, tipo, preco, valor_condominio, valor_iptu 
FROM imoveis 
LIMIT 5;