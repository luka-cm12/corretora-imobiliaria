-- ======================================================================
-- SCRIPT BÁSICO PARA ADICIONAR COLUNAS - MYSQL PURO
-- ======================================================================
-- Execute no phpMyAdmin, MySQL Workbench ou terminal MySQL
-- Se alguma coluna já existir, ignore o erro e continue
-- ======================================================================

-- Verificar estrutura atual
DESCRIBE imoveis;

-- ======================================================================
-- CARACTERÍSTICAS E POSIÇÃO SOLAR
-- ======================================================================
ALTER TABLE imoveis ADD caracteristicas TEXT;
ALTER TABLE imoveis ADD posicao_solar VARCHAR(10);
ALTER TABLE imoveis ADD cep CHAR(8);

-- ======================================================================
-- VALORES FINANCEIROS INTERNOS
-- ======================================================================
ALTER TABLE imoveis ADD valor_condominio DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE imoveis ADD valor_iptu DECIMAL(10,2) DEFAULT 0.00;

-- ======================================================================
-- CAMPOS ADMINISTRATIVOS
-- ======================================================================
ALTER TABLE imoveis ADD matricula VARCHAR(100);
ALTER TABLE imoveis ADD parcelas_iptu VARCHAR(50);
ALTER TABLE imoveis ADD exclusividade TINYINT(1) DEFAULT 0;
ALTER TABLE imoveis ADD taxa_intermediacao DECIMAL(5,2) DEFAULT 0.00;

-- ======================================================================
-- ÁREAS DETALHADAS
-- ======================================================================
ALTER TABLE imoveis ADD area_privativa DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE imoveis ADD area_comum DECIMAL(10,2) DEFAULT 0.00;

-- ======================================================================
-- CONTROLE DE CHAVES
-- ======================================================================
ALTER TABLE imoveis ADD chaves_tipo VARCHAR(255);
ALTER TABLE imoveis ADD chaves_copias INT DEFAULT 0;
ALTER TABLE imoveis ADD observacoes_chaves TEXT;

-- ======================================================================
-- VERIFICAÇÃO FINAL
-- ======================================================================
DESCRIBE imoveis;