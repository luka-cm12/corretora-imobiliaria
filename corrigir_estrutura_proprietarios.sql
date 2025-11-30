-- Script para corrigir a estrutura da tabela proprietarios
-- Permitir CPF e EMAIL como NULL para tornar campos opcionais

-- 1. Primeiro, vamos corrigir registros com valores vazios
UPDATE proprietarios SET cpf = NULL WHERE cpf = '';
UPDATE proprietarios SET cnpj = NULL WHERE cnpj = '';
UPDATE proprietarios SET email = NULL WHERE email = '';
UPDATE proprietarios SET telefone = NULL WHERE telefone = '';
UPDATE proprietarios SET endereco = NULL WHERE endereco = '';

-- 2. Remover as constraints UNIQUE e NOT NULL do CPF temporariamente
ALTER TABLE proprietarios DROP INDEX cpf;
ALTER TABLE proprietarios DROP INDEX email;

-- 3. Modificar as colunas para permitir NULL
ALTER TABLE proprietarios MODIFY cpf varchar(14) COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE proprietarios MODIFY cnpj varchar(18) COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE proprietarios MODIFY email varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL;

-- 5. Opcional: Recriar índices para valores únicos não nulos (comentado por compatibilidade)
-- CREATE UNIQUE INDEX idx_cpf_unique ON proprietarios (cpf) WHERE cpf IS NOT NULL;
-- CREATE UNIQUE INDEX idx_email_unique ON proprietarios (email) WHERE email IS NOT NULL;

-- 6. Verificar a estrutura final
SHOW COLUMNS FROM proprietarios;

-- 7. Testar inserção com campos opcionais
-- INSERT INTO proprietarios (nome) VALUES ('Teste Sem Documentos');

-- 8. Verificar dados após alterações
SELECT 
    id_proprietario,
    nome,
    CASE WHEN cpf IS NULL THEN 'SEM CPF' ELSE cpf END as cpf_status,
    CASE WHEN cnpj IS NULL THEN 'SEM CNPJ' ELSE cnpj END as cnpj_status,
    CASE WHEN email IS NULL THEN 'SEM EMAIL' ELSE email END as email_status,
    tipo_documento
FROM proprietarios 
ORDER BY id_proprietario;