-- =============================================
-- ATUALIZAÇÃO BANCO: NOVOS TIPOS DE IMÓVEIS
-- =============================================
-- Data: 01/11/2025
-- Novos tipos: pavilhao, fazenda, laja_terrea, 
--              sala_area, area_terras, loteamento, 
--              condominio_fechado
-- =============================================

-- 1. Verificar estrutura atual
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- 2. Atualizar coluna tipo com novos valores
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
    'comercial',
    'pavilhao',
    'fazenda',
    'laja_terrea',
    'sala_area',
    'area_terras',
    'loteamento',
    'condominio_fechado'
) NOT NULL COMMENT 'Tipos de imóveis - Expandido em 01/11/2025';

-- 3. Verificar se foi aplicado
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- 4. Ver distribuição atual
SELECT tipo, COUNT(*) as quantidade FROM imoveis GROUP BY tipo ORDER BY tipo;

-- 5. Resumo final
SELECT COUNT(*) as total_imoveis, COUNT(DISTINCT tipo) as tipos_diferentes FROM imoveis;