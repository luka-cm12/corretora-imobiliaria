-- Script para corrigir dados inconsistentes na tabela proprietarios
-- Execute este script no phpMyAdmin ou MySQL para corrigir os problemas

-- 1. Normalizar CPFs (remover pontuação)
UPDATE proprietarios 
SET cpf = REGEXP_REPLACE(cpf, '[^0-9]', '') 
WHERE cpf IS NOT NULL AND cpf != '';

-- 2. Normalizar CNPJs (remover pontuação)  
UPDATE proprietarios 
SET cnpj = REGEXP_REPLACE(cnpj, '[^0-9]', '') 
WHERE cnpj IS NOT NULL AND cnpj != '';

-- 3. Corrigir registro com CPF vazio mas tipo CPF (ID 22)
UPDATE proprietarios 
SET cpf = NULL, tipo_documento = 'cpf'
WHERE id_proprietario = 22 AND cpf = '';

-- 4. Verificar dados após correção
SELECT 
    id_proprietario,
    nome,
    cpf,
    cnpj,
    tipo_documento,
    CASE 
        WHEN tipo_documento = 'cpf' AND (cpf IS NULL OR LENGTH(cpf) != 11) THEN 'CPF INVÁLIDO'
        WHEN tipo_documento = 'cnpj' AND (cnpj IS NULL OR LENGTH(cnpj) != 14) THEN 'CNPJ INVÁLIDO'
        ELSE 'OK'
    END as status_validacao
FROM proprietarios 
ORDER BY id_proprietario;

-- 5. Mostrar apenas registros com problemas
SELECT 
    id_proprietario,
    nome,
    cpf,
    cnpj,
    tipo_documento,
    'PROBLEMA' as status
FROM proprietarios 
WHERE 
    (tipo_documento = 'cpf' AND (cpf IS NULL OR LENGTH(cpf) != 11))
    OR 
    (tipo_documento = 'cnpj' AND (cnpj IS NULL OR LENGTH(cnpj) != 14))
ORDER BY id_proprietario;