-- =====================================================
-- SCRIPT PARA ADICIONAR SUPORTE A CPF/CNPJ PROPRIETÁRIOS
-- =====================================================
-- Execute este comando no seu banco de dados MySQL
-- Adiciona funcionalidade de CPF ou CNPJ para proprietários
-- =====================================================

-- Adicionar coluna tipo_documento para identificar se é CPF ou CNPJ
ALTER TABLE proprietarios ADD tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf' 
COMMENT 'Tipo do documento: cpf para pessoa física, cnpj para pessoa jurídica';

-- Adicionar coluna cnpj separada (opcional - manter cpf para compatibilidade)
ALTER TABLE proprietarios ADD cnpj VARCHAR(18) NULL 
COMMENT 'CNPJ do proprietário pessoa jurídica (formato: 00.000.000/0000-00)';

-- Verificar se as colunas foram adicionadas
DESCRIBE proprietarios;

-- Mostrar informações das novas colunas
SELECT 
    COLUMN_NAME as 'Nome da Coluna',
    COLUMN_TYPE as 'Tipo',
    IS_NULLABLE as 'Permite NULL',
    COLUMN_DEFAULT as 'Valor Padrão',
    COLUMN_COMMENT as 'Comentário'
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'proprietarios' 
    AND COLUMN_NAME IN ('tipo_documento', 'cnpj');

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