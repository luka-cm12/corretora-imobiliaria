-- ======================================================
-- SCRIPT PARA ADICIONAR TIPO 'LOFT' NO BANCO DE DADOS
-- ======================================================
-- Execute este script no seu banco de dados MySQL
-- (corretora_base, hg856221_corretor_corretora, etc.)

-- 1. ATUALIZAR ENUM DO CAMPO TIPO
ALTER TABLE imoveis MODIFY COLUMN tipo ENUM('casa','casa_condominio','apartamento','apartamento_mobiliado','sobrado','chacara','semi_mobiliado','terreno','loft','comercial') NOT NULL COMMENT 'Tipo do imóvel incluindo Loft';

-- 2. VERIFICAR A ALTERAÇÃO
DESCRIBE imoveis;

-- 3. TESTAR OS TIPOS DISPONÍVEIS
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- 4. CONSULTAR DISTRIBUIÇÃO ATUAL POR TIPO
SELECT tipo, COUNT(*) as quantidade FROM imoveis GROUP BY tipo ORDER BY tipo;