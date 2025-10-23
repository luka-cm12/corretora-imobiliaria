-- COMANDOS SQL ESSENCIAIS PARA AS NOVAS CARACTERÍSTICAS
-- Execute no phpMyAdmin ou terminal MySQL

-- 1. ESSENCIAL: Coluna para armazenar características (JSON)
ALTER TABLE imoveis ADD caracteristicas TEXT NULL;

-- 2. ESSENCIAL: Coluna para posição solar  
ALTER TABLE imoveis ADD posicao_solar VARCHAR(10) NULL;

-- 3. ESSENCIAL: Coluna para CEP
ALTER TABLE imoveis ADD cep CHAR(8) NULL;

-- VERIFICAR SE AS COLUNAS FORAM CRIADAS:
DESCRIBE imoveis;

-- EXEMPLO DE USO DAS CARACTERÍSTICAS:
-- UPDATE imoveis SET caracteristicas = '["suite","piscina","churrasqueira"]' WHERE id = 1;
-- UPDATE imoveis SET posicao_solar = 'norte' WHERE id = 1;