-- =============================================
-- ADICIONAR COLUNA FINALIDADE (VENDA/LOCAÇÃO)
-- =============================================
-- Data: 01/11/2025
-- Adiciona funcionalidade para separar imóveis
-- de venda e locação no sistema
-- =============================================

-- 1. Verificar estrutura atual
SHOW COLUMNS FROM imoveis LIKE 'finalidade';

-- 2. Adicionar coluna finalidade
ALTER TABLE imoveis ADD COLUMN finalidade ENUM('venda', 'locacao') NOT NULL DEFAULT 'venda' 
COMMENT 'Finalidade do imóvel: venda ou locação';

-- 3. Verificar se foi adicionada
SHOW COLUMNS FROM imoveis LIKE 'finalidade';

-- 4. Atualizar registros existentes (opcional)
-- Por padrão todos ficam como 'venda', mas você pode ajustar conforme necessário:

-- Exemplo: Definir alguns tipos como locação por padrão
-- UPDATE imoveis SET finalidade = 'locacao' WHERE tipo = 'comercial';

-- 5. Ver distribuição atual
SELECT finalidade, COUNT(*) as quantidade FROM imoveis GROUP BY finalidade;

-- 6. Ver distribuição por tipo e finalidade
SELECT tipo, finalidade, COUNT(*) as quantidade 
FROM imoveis 
GROUP BY tipo, finalidade 
ORDER BY tipo, finalidade;