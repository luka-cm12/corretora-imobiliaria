-- ======================================================================
-- SCRIPT COMPLETO PARA ADICIONAR TODAS AS NOVAS COLUNAS NO BANCO
-- ======================================================================
-- Execute este script no seu banco de dados MySQL/MariaDB
-- IMPORTANTE: Execute uma seção por vez para facilitar a identificação de problemas
-- Compatível com: corretora_base, hg856221_corretor_corretora, etc.
-- ======================================================================

-- VERIFICAÇÃO INICIAL - Mostra estrutura atual da tabela
DESCRIBE imoveis;

-- ======================================================================
-- 1. CARACTERÍSTICAS EM JSON
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS caracteristicas TEXT NULL 
COMMENT 'Características do imóvel em formato JSON';

-- ======================================================================
-- 2. POSIÇÃO SOLAR
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS posicao_solar VARCHAR(10) NULL 
COMMENT 'Posição solar do imóvel: norte, sul, leste, oeste';

-- ======================================================================
-- 3. CEP
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS cep CHAR(8) NULL 
COMMENT 'CEP do imóvel (apenas números)';

-- ======================================================================
-- 4. VALORES FINANCEIROS INTERNOS
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS valor_condominio DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Valor mensal do condomínio para uso interno';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS valor_iptu DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Valor anual do IPTU para uso interno';

-- ======================================================================
-- 5. CAMPOS ADMINISTRATIVOS
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS matricula VARCHAR(100) NULL 
COMMENT 'Número da matrícula do imóvel';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS parcelas_iptu VARCHAR(50) NULL 
COMMENT 'Número de parcelas do IPTU';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS exclusividade TINYINT(1) DEFAULT 0 
COMMENT 'Imóvel em exclusividade (0=não, 1=sim)';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS taxa_intermediacao DECIMAL(5,2) DEFAULT 0.00 
COMMENT 'Taxa de intermediação em percentual';

-- ======================================================================
-- 6. ÁREAS DETALHADAS
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS area_privativa DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Área privativa do imóvel em metros quadrados';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS area_comum DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Área comum do condomínio em metros quadrados';

-- ======================================================================
-- 7. CONTROLE DE CHAVES
-- ======================================================================
ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS chaves_tipo VARCHAR(255) NULL 
COMMENT 'Tipos de chaves disponíveis (separados por vírgula)';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS chaves_copias INT DEFAULT 0 
COMMENT 'Quantidade de cópias das chaves';

ALTER TABLE imoveis ADD COLUMN IF NOT EXISTS observacoes_chaves TEXT NULL 
COMMENT 'Observações sobre as chaves do imóvel';

-- 8. ATUALIZAR ENUM DO TIPO DE IMÓVEL
-- Adiciona todos os novos tipos incluindo 'loft'
ALTER TABLE imoveis MODIFY COLUMN tipo ENUM(
    'casa',
    'casa_condominio', 
    'apartamento',
    'apartamento_mobiliado',
    'sobrado',
    'chacara',
    'semi_mobiliado',
    'terreno',
    'loft',
    'comercial'
) NOT NULL COMMENT 'Tipo do imóvel com todos os novos tipos implementados';

-- ======================================================================
-- VERIFICAÇÕES E RELATÓRIOS
-- ======================================================================

-- Verificar todas as colunas adicionadas
SELECT 'VERIFICAÇÃO FINAL - ESTRUTURA DA TABELA IMOVEIS' AS titulo;

DESCRIBE imoveis;

-- Contar colunas por tipo
SELECT 
    'RESUMO DAS COLUNAS ADICIONADAS' AS titulo,
    COUNT(CASE WHEN COLUMN_NAME IN ('caracteristicas', 'posicao_solar', 'cep') THEN 1 END) AS 'Básicas',
    COUNT(CASE WHEN COLUMN_NAME IN ('valor_condominio', 'valor_iptu') THEN 1 END) AS 'Financeiras',
    COUNT(CASE WHEN COLUMN_NAME IN ('matricula', 'parcelas_iptu', 'exclusividade', 'taxa_intermediacao') THEN 1 END) AS 'Administrativas',
    COUNT(CASE WHEN COLUMN_NAME IN ('area_privativa', 'area_comum') THEN 1 END) AS 'Áreas',
    COUNT(CASE WHEN COLUMN_NAME IN ('chaves_tipo', 'chaves_copias', 'observacoes_chaves') THEN 1 END) AS 'Chaves'
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'imoveis';

-- Verificar tipos de imóvel disponíveis
SELECT 'TIPOS DE IMÓVEL DISPONÍVEIS' AS titulo;
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- ======================================================================
-- EXEMPLOS DE USO DAS NOVAS COLUNAS
-- ======================================================================

-- Exemplo de como inserir características em JSON:
-- UPDATE imoveis SET caracteristicas = '["suite","piscina","churrasqueira","ar_condicionado","garagem_coberta"]' WHERE id = 1;

-- Exemplo de como inserir posição solar:
-- UPDATE imoveis SET posicao_solar = 'norte' WHERE id = 1;

-- Exemplo de busca por características:
-- SELECT * FROM imoveis WHERE JSON_CONTAINS(caracteristicas, '"piscina"');

-- Exemplo de busca por posição solar:
-- SELECT * FROM imoveis WHERE posicao_solar = 'norte';

-- ======================================================================
-- OBSERVAÇÕES IMPORTANTES:
-- ======================================================================
-- 1. Execute este script em ambiente de teste primeiro
-- 2. Faça BACKUP completo do banco antes da execução
-- 3. Todas as novas colunas permitem NULL para compatibilidade
-- 4. As características são armazenadas em formato JSON
-- 5. Os valores padrão são definidos onde apropriado
-- 6. Execute uma seção por vez se houver problemas
-- ======================================================================