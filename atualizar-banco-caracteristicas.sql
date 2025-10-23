-- Script SQL para adicionar suporte às novas características no banco de dados
-- Execute este script no seu banco MySQL/MariaDB

-- 1. Verificar se a coluna 'caracteristicas' já existe
-- (Este comando mostra a estrutura da tabela para verificação)
DESCRIBE imoveis;

-- 2. Se a coluna 'caracteristicas' NÃO existir, execute este comando:
ALTER TABLE imoveis ADD COLUMN caracteristicas TEXT NULL COMMENT 'Características do imóvel em formato JSON';

-- 3. Se a coluna 'posicao_solar' NÃO existir, execute este comando:
ALTER TABLE imoveis ADD COLUMN posicao_solar VARCHAR(10) NULL COMMENT 'Posição solar do imóvel (norte, sul, leste, oeste)';

-- 4. Verificar se as colunas internas existem (campos adicionais)
-- Execute apenas se você quiser os campos internos de administração:

-- Para valores de condomínio e IPTU:
ALTER TABLE imoveis ADD COLUMN valor_condominio DECIMAL(10,2) NULL COMMENT 'Valor mensal do condomínio';
ALTER TABLE imoveis ADD COLUMN valor_iptu DECIMAL(10,2) NULL COMMENT 'Valor mensal do IPTU';

-- Para controle interno de matrícula e exclusividade:
ALTER TABLE imoveis ADD COLUMN matricula VARCHAR(100) NULL COMMENT 'Número da matrícula do imóvel';
ALTER TABLE imoveis ADD COLUMN exclusividade TINYINT(1) DEFAULT 0 COMMENT 'Imóvel em exclusividade (0=não, 1=sim)';

-- Para controle de chaves:
ALTER TABLE imoveis ADD COLUMN chaves_tipo VARCHAR(255) NULL COMMENT 'Tipos de chaves disponíveis';
ALTER TABLE imoveis ADD COLUMN chaves_copias INT DEFAULT 0 COMMENT 'Quantidade de cópias das chaves';
ALTER TABLE imoveis ADD COLUMN observacoes_chaves TEXT NULL COMMENT 'Observações sobre as chaves';

-- Para áreas detalhadas:
ALTER TABLE imoveis ADD COLUMN area_privativa DECIMAL(8,2) NULL COMMENT 'Área privativa em m²';
ALTER TABLE imoveis ADD COLUMN area_comum DECIMAL(8,2) NULL COMMENT 'Área comum em m²';

-- Para outros campos administrativos:
ALTER TABLE imoveis ADD COLUMN parcelas_iptu VARCHAR(50) NULL COMMENT 'Número de parcelas do IPTU';
ALTER TABLE imoveis ADD COLUMN taxa_intermediacao DECIMAL(5,2) NULL COMMENT 'Taxa de intermediação em %';

-- 5. Adicionar campo CEP se não existir:
ALTER TABLE imoveis ADD COLUMN cep CHAR(8) NULL COMMENT 'CEP do imóvel (apenas números)';

-- 6. Verificar estrutura final da tabela:
SHOW COLUMNS FROM imoveis;

-- 7. Exemplo de inserção de características (formato JSON):
-- UPDATE imoveis SET caracteristicas = '["suite","piscina","churrasqueira","ar_condicionado"]' WHERE id = 1;

-- 8. Exemplo de consulta para buscar imóveis por características:
-- SELECT * FROM imoveis WHERE JSON_CONTAINS(caracteristicas, '"piscina"');

-- OBSERVAÇÕES IMPORTANTES:
-- - Execute um comando ALTER TABLE por vez
-- - Faça backup do banco antes de executar
-- - As novas características são armazenadas em JSON na coluna 'caracteristicas'
-- - Nem todas as colunas são obrigatórias, adicione apenas as que você vai usar