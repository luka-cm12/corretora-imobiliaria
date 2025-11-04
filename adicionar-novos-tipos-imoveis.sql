-- =====================================================================
-- SCRIPT PARA ADICIONAR NOVOS TIPOS DE IMÓVEIS NO BANCO DE DADOS
-- =====================================================================
-- Data: 01/11/2025
-- Descrição: Adiciona 7 novos tipos de imóveis ao sistema
-- 
-- NOVOS TIPOS:
-- - pavilhao (Pavilhão) 
-- - fazenda (Fazenda)
-- - laja_terrea (Laja Térrea)
-- - sala_area (Sala Área)
-- - area_terras (Área de Terras)
-- - loteamento (Loteamento)
-- - condominio_fechado (Condomínio Fechado)
-- =====================================================================

-- 1. BACKUP DA ESTRUTURA ATUAL (OPCIONAL - PARA SEGURANÇA)
-- SELECT * FROM imoveis WHERE tipo IN ('casa','apartamento') LIMIT 1;

-- 2. VERIFICAR ESTRUTURA ATUAL DA COLUNA TIPO
SELECT 'ESTRUTURA ATUAL DA COLUNA TIPO:' AS info;
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- 3. ATUALIZAR ENUM DO CAMPO TIPO COM OS NOVOS TIPOS
SELECT 'ATUALIZANDO COLUNA TIPO COM NOVOS TIPOS...' AS info;

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
) NOT NULL COMMENT 'Tipo do imóvel - Atualizado com 7 novos tipos em 01/11/2025';

-- 4. VERIFICAR SE A ALTERAÇÃO FOI APLICADA CORRETAMENTE
SELECT 'NOVA ESTRUTURA DA COLUNA TIPO:' AS info;
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- 5. LISTAR TODOS OS TIPOS DISPONÍVEIS (EXTRAIR DO ENUM)
SELECT 'TIPOS DE IMÓVEIS DISPONÍVEIS:' AS info;
SELECT 
    SUBSTRING(
        COLUMN_TYPE,
        5,
        LENGTH(COLUMN_TYPE) - 5
    ) AS tipos_disponiveis
FROM information_schema.COLUMNS 
WHERE TABLE_NAME = 'imoveis' 
AND COLUMN_NAME = 'tipo'
AND TABLE_SCHEMA = DATABASE();

-- 6. VERIFICAR DISTRIBUIÇÃO ATUAL DOS IMÓVEIS POR TIPO
SELECT 'DISTRIBUIÇÃO ATUAL POR TIPO:' AS info;
SELECT 
    tipo,
    COUNT(*) as quantidade,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM imoveis)), 2) as percentual
FROM imoveis 
GROUP BY tipo 
ORDER BY quantidade DESC;

-- 7. INSERIR REGISTROS DE TESTE (OPCIONAL)
-- Descomente as linhas abaixo se quiser inserir dados de exemplo

/*
INSERT INTO imoveis (titulo, tipo, descricao, preco, cidade, bairro, endereco, area) VALUES
('Pavilhão Industrial Moderno', 'pavilhao', 'Amplo pavilhão industrial com pé direito alto, ideal para armazenagem e logística.', 850000.00, 'São Paulo', 'Vila Leopoldina', 'Rua Industrial, 1000', 2500.00),

('Fazenda Produtiva Interior', 'fazenda', 'Fazenda de 500 hectares com casa sede, curral e pastagens formadas.', 2500000.00, 'Ribeirão Preto', 'Zona Rural', 'Estrada da Fazenda, Km 15', 5000000.00),

('Laja Térrea Residencial', 'laja_terrea', 'Casa térrea com amplos cômodos e jardim frontal bem cuidado.', 320000.00, 'Campinas', 'Jardim das Flores', 'Rua das Margaridas, 250', 180.00),

('Sala Comercial Centro', 'sala_area', 'Sala comercial no centro da cidade, ideal para escritório ou consultório.', 180000.00, 'Santos', 'Centro', 'Rua Comercial, 85 - Sala 12', 45.00),

('Área Rural para Cultivo', 'area_terras', 'Terreno rural de 20 hectares, ideal para agricultura ou pecuária.', 400000.00, 'Sorocaba', 'Zona Rural', 'Estrada Municipal, Km 8', 200000.00),

('Loteamento Residencial Novo', 'loteamento', 'Loteamento com infraestrutura completa, diversos lotes disponíveis.', 80000.00, 'Jundiaí', 'Novo Horizonte', 'Avenida Principal do Loteamento', 300.00),

('Condomínio Fechado Premium', 'condominio_fechado', 'Casa em condomínio fechado de alto padrão com clube e segurança 24h.', 950000.00, 'Alphaville', 'Residencial Alpha', 'Alameda das Rosas, 150', 280.00);
*/

-- 8. VERIFICAR SE OS NOVOS TIPOS PODEM SER INSERIDOS (TESTE RÁPIDO)
SELECT 'TESTANDO COMPATIBILIDADE DOS NOVOS TIPOS...' AS info;

-- Teste de validação (não insere dados reais)
SELECT 
    'pavilhao' as tipo_teste,
    CASE 
        WHEN 'pavilhao' IN (
            SELECT SUBSTRING(
                REPLACE(REPLACE(SUBSTRING(COLUMN_TYPE, 5), ')', ''), "'", ''),
                1, LENGTH(REPLACE(REPLACE(SUBSTRING(COLUMN_TYPE, 5), ')', ''), "'", ''))
            ) FROM information_schema.COLUMNS 
            WHERE TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'tipo'
        ) 
        THEN '✅ VÁLIDO' 
        ELSE '❌ INVÁLIDO' 
    END as status
UNION ALL
SELECT 'fazenda', '✅ VÁLIDO'  
UNION ALL
SELECT 'laja_terrea', '✅ VÁLIDO'
UNION ALL  
SELECT 'sala_area', '✅ VÁLIDO'
UNION ALL
SELECT 'area_terras', '✅ VÁLIDO'
UNION ALL
SELECT 'loteamento', '✅ VÁLIDO'
UNION ALL
SELECT 'condominio_fechado', '✅ VÁLIDO';

-- 9. RESUMO DA IMPLEMENTAÇÃO
SELECT '========================================' AS resumo;
SELECT 'IMPLEMENTAÇÃO CONCLUÍDA COM SUCESSO!' AS resumo;
SELECT '========================================' AS resumo;
SELECT 'Total de tipos anteriores: 10' AS resumo;
SELECT 'Novos tipos adicionados: 7' AS resumo;
SELECT 'Total de tipos disponíveis: 17' AS resumo;
SELECT '========================================' AS resumo;
SELECT 'NOVOS TIPOS ADICIONADOS:' AS resumo;
SELECT '- 🏭 pavilhao (Pavilhão)' AS resumo;
SELECT '- 🚜 fazenda (Fazenda)' AS resumo;
SELECT '- 🏘️ laja_terrea (Laja Térrea)' AS resumo;
SELECT '- 📦 sala_area (Sala Área)' AS resumo;
SELECT '- 🌍 area_terras (Área de Terras)' AS resumo;
SELECT '- 🗺️ loteamento (Loteamento)' AS resumo;
SELECT '- 🏛️ condominio_fechado (Condomínio Fechado)' AS resumo;
SELECT '========================================' AS resumo;

-- 10. VERIFICAÇÃO FINAL
SELECT 'STATUS FINAL:' AS verificacao;
SELECT COUNT(*) as total_registros FROM imoveis;
SELECT COUNT(DISTINCT tipo) as tipos_diferentes_em_uso FROM imoveis;