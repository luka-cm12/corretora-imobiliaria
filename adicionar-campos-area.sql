-- ======================================================
-- SCRIPT PARA ADICIONAR CAMPOS DE ÁREA DETALHADA
-- ======================================================
-- Execute este script no seu banco de dados MySQL
-- (corretora_base, hg856221_corretor_corretora, etc.)

-- 1. ADICIONAR COLUNAS DE ÁREA PRIVATIVA E ÁREA COMUM
ALTER TABLE imoveis 
ADD COLUMN area_privativa DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área privativa do imóvel em metros quadrados',
ADD COLUMN area_comum DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área comum do condomínio em metros quadrados';

-- 2. VERIFICAR AS ALTERAÇÕES
DESCRIBE imoveis;

-- 3. CONSULTAR ESTRUTURA DAS NOVAS COLUNAS
SHOW COLUMNS FROM imoveis WHERE Field LIKE '%area%';

-- 4. CONSULTAR DADOS EXISTENTES (OPCIONAL)
SELECT id, titulo, area, area_privativa, area_comum 
FROM imoveis 
LIMIT 5;