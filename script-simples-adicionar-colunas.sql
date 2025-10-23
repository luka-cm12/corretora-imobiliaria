-- ======================================================================
-- SCRIPT ALTERNATIVO - VERSÃO COMPATÍVEL COM MYSQL 5.7 E ANTERIOR
-- ======================================================================
-- Use este script se o comando "IF NOT EXISTS" não funcionar
-- Execute uma linha por vez no phpMyAdmin ou terminal MySQL
-- ======================================================================

-- Verificar estrutura atual
DESCRIBE imoveis;

-- ======================================================================
-- ADICIONAR COLUNAS - EXECUTE APENAS AS QUE NÃO EXISTEM
-- ======================================================================

-- 1. Características em JSON
ALTER TABLE imoveis ADD caracteristicas TEXT NULL COMMENT 'Características do imóvel em formato JSON';

-- 2. Posição solar
ALTER TABLE imoveis ADD posicao_solar VARCHAR(10) NULL COMMENT 'Posição solar do imóvel: norte, sul, leste, oeste';

-- 3. CEP
ALTER TABLE imoveis ADD cep CHAR(8) NULL COMMENT 'CEP do imóvel (apenas números)';

-- 4. Valores financeiros
ALTER TABLE imoveis ADD valor_condominio DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor mensal do condomínio';
ALTER TABLE imoveis ADD valor_iptu DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor anual do IPTU';

-- 5. Campos administrativos
ALTER TABLE imoveis ADD matricula VARCHAR(100) NULL COMMENT 'Número da matrícula do imóvel';
ALTER TABLE imoveis ADD parcelas_iptu VARCHAR(50) NULL COMMENT 'Número de parcelas do IPTU';
ALTER TABLE imoveis ADD exclusividade TINYINT(1) DEFAULT 0 COMMENT 'Imóvel em exclusividade (0=não, 1=sim)';
ALTER TABLE imoveis ADD taxa_intermediacao DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Taxa de intermediação em %';

-- 6. Áreas detalhadas
ALTER TABLE imoveis ADD area_privativa DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área privativa em m²';
ALTER TABLE imoveis ADD area_comum DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área comum em m²';

-- 7. Controle de chaves
ALTER TABLE imoveis ADD chaves_tipo VARCHAR(255) NULL COMMENT 'Tipos de chaves disponíveis';
ALTER TABLE imoveis ADD chaves_copias INT DEFAULT 0 COMMENT 'Quantidade de cópias das chaves';
ALTER TABLE imoveis ADD observacoes_chaves TEXT NULL COMMENT 'Observações sobre as chaves';

-- ======================================================================
-- ATUALIZAR ENUM DO TIPO DE IMÓVEL
-- ======================================================================
ALTER TABLE imoveis MODIFY tipo ENUM(
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
) NOT NULL COMMENT 'Tipo do imóvel com todos os tipos implementados';

-- ======================================================================
-- VERIFICAÇÃO FINAL
-- ======================================================================
DESCRIBE imoveis;

-- Mostrar tipos disponíveis
SHOW COLUMNS FROM imoveis LIKE 'tipo';