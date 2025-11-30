-- =====================================================
-- SCRIPT COMPLETO PARA SUPORTE A CPF/CNPJ PROPRIETÁRIOS
-- =====================================================
-- Execute TUDO no seu MySQL (localhost ou Hostgator)
-- Adiciona funcionalidade de CPF OU CNPJ para proprietários
-- =====================================================

-- 1. Verificar se a tabela proprietarios existe
-- Se NÃO existir, criar a tabela completa
CREATE TABLE IF NOT EXISTS proprietarios (
    id_proprietario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf',
    cpf VARCHAR(20) NULL,
    cnpj VARCHAR(20) NULL,
    telefone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    endereco TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cpf (cpf),
    INDEX idx_cnpj (cnpj),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Para tabelas EXISTENTES: adicionar colunas que faltam
-- ATENÇÃO: Execute apenas se a tabela já existir e faltar essas colunas

-- Adicionar tipo_documento (use apenas se não tiver esta coluna)
-- ALTER TABLE proprietarios ADD COLUMN tipo_documento VARCHAR(10) DEFAULT 'cpf';

-- Adicionar coluna CNPJ (use apenas se não tiver esta coluna)  
-- ALTER TABLE proprietarios ADD COLUMN cnpj VARCHAR(20) NULL;

-- 3. VERIFICAR se tudo foi criado corretamente
DESCRIBE proprietarios;

-- 4. CONTAR registros existentes
SELECT 
    COUNT(*) as 'Total de Proprietários',
    SUM(CASE WHEN tipo_documento = 'cpf' OR (tipo_documento IS NULL AND cpf IS NOT NULL) THEN 1 ELSE 0 END) as 'Com CPF',
    SUM(CASE WHEN tipo_documento = 'cnpj' AND cnpj IS NOT NULL THEN 1 ELSE 0 END) as 'Com CNPJ'
FROM proprietarios;

-- =====================================================
-- EXEMPLOS DE USO:
-- =====================================================

-- Proprietário pessoa física (CPF):
-- INSERT INTO proprietarios (nome, tipo_documento, cpf, telefone, email) 
-- VALUES ('João Silva', 'cpf', '123.456.789-00', '(11) 99999-9999', 'joao@email.com');

-- Proprietário pessoa jurídica (CNPJ):
-- INSERT INTO proprietarios (nome, tipo_documento, cnpj, telefone, email) 
-- VALUES ('Empresa LTDA', 'cnpj', '12.345.678/0001-90', '(11) 3333-3333', 'contato@empresa.com');

-- Buscar por CPF:
-- SELECT * FROM proprietarios WHERE tipo_documento = 'cpf' AND cpf LIKE '%123.456.789%';

-- Buscar por CNPJ:
-- SELECT * FROM proprietarios WHERE tipo_documento = 'cnpj' AND cnpj LIKE '%12.345.678%';

-- =====================================================