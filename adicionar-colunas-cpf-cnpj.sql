-- Script para adicionar colunas de CPF/CNPJ separados
-- Execute este script para atualizar a estrutura da tabela proprietarios

-- Verificar se as colunas existem antes de adicionar
-- Adicionar coluna tipo_documento
ALTER TABLE proprietarios 
ADD COLUMN tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf' 
AFTER nome;

-- Adicionar coluna cnpj
ALTER TABLE proprietarios 
ADD COLUMN cnpj VARCHAR(20) NULL 
AFTER cpf;

-- Atualizar registros existentes baseado no tamanho do documento na coluna cpf
UPDATE proprietarios 
SET tipo_documento = 'cnpj', 
    cnpj = cpf, 
    cpf = NULL 
WHERE LENGTH(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '')) >= 14 
  AND cpf IS NOT NULL 
  AND cpf != '';

-- Atualizar registros com CPF para definir tipo correto
UPDATE proprietarios 
SET tipo_documento = 'cpf' 
WHERE LENGTH(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '')) = 11 
  AND cpf IS NOT NULL 
  AND cpf != '';

-- Verificar os resultados
SELECT 
    id_proprietario,
    nome,
    tipo_documento,
    cpf,
    cnpj,
    telefone,
    email
FROM proprietarios 
ORDER BY id_proprietario DESC 
LIMIT 10;

-- Estatísticas após migração
SELECT 
    tipo_documento,
    COUNT(*) as total,
    COUNT(CASE WHEN tipo_documento = 'cpf' THEN cpf END) as cpf_preenchidos,
    COUNT(CASE WHEN tipo_documento = 'cnpj' THEN cnpj END) as cnpj_preenchidos
FROM proprietarios 
GROUP BY tipo_documento;