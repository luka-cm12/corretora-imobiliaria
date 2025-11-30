-- Exemplos de inserção para testar CNPJ

-- 1. Inserir empresa apenas com CNPJ
INSERT INTO proprietarios (nome, cpf, cnpj, tipo_documento, telefone, email, endereco, data_cadastro) 
VALUES ('Empresa Exemplo LTDA', NULL, '12345678000195', 'cnpj', '11999887766', 'empresa@exemplo.com', 'Rua das Empresas, 123', NOW());

-- 2. Inserir pessoa física apenas com CPF  
INSERT INTO proprietarios (nome, cpf, cnpj, tipo_documento, telefone, email, endereco, data_cadastro) 
VALUES ('João da Silva', '12345678901', NULL, 'cpf', '11988776655', 'joao@email.com', 'Rua das Pessoas, 456', NOW());

-- 3. Inserir apenas com nome (todos documentos opcionais)
INSERT INTO proprietarios (nome, cpf, cnpj, tipo_documento, telefone, email, endereco, data_cadastro) 
VALUES ('Maria Sem Documentos', NULL, NULL, 'cpf', NULL, NULL, NULL, NOW());

-- 4. Verificar os dados inseridos
SELECT 
    id_proprietario,
    nome,
    CASE WHEN cpf IS NOT NULL THEN CONCAT('CPF: ', cpf) ELSE 'SEM CPF' END as documento_pf,
    CASE WHEN cnpj IS NOT NULL THEN CONCAT('CNPJ: ', cnpj) ELSE 'SEM CNPJ' END as documento_pj,
    tipo_documento,
    COALESCE(telefone, 'SEM TELEFONE') as telefone,
    COALESCE(email, 'SEM EMAIL') as email
FROM proprietarios 
WHERE nome LIKE '%Exemplo%' OR nome LIKE '%João%' OR nome LIKE '%Maria Sem%'
ORDER BY id_proprietario DESC;