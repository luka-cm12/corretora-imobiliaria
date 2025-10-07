# Guia de Migração do Banco de Dados

## Problema Resolvido

Se você está recebendo o erro:
```
Fatal error: Uncaught PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'atualizado_em' in 'field list'
```

Isso significa que sua tabela `usuarios` está faltando colunas que foram adicionadas em versões mais recentes.

## Como Resolver

### Opção 1: Executar o Script de Migração Automática

1. Acesse via navegador: `http://seu-site/private/admin/migrate_database.php`
2. O script irá verificar e adicionar as colunas faltantes automaticamente
3. Após a execução, você pode fazer login normalmente

### Opção 2: Adicionar as Colunas Manualmente via SQL

Execute os seguintes comandos SQL no seu phpMyAdmin ou cliente MySQL:

```sql
-- Verifica se as colunas existem antes de adicionar
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMP NULL DEFAULT NULL AFTER criado_em;

ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS ultimo_login TIMESTAMP NULL DEFAULT NULL AFTER atualizado_em;
```

**Nota para MySQL 5.6 ou anterior:** Se você receber erro com `IF NOT EXISTS`, use:

```sql
-- Para MySQL 5.6 ou anterior (sem IF NOT EXISTS)
-- Execute este comando para verificar se a coluna já existe:
SHOW COLUMNS FROM usuarios LIKE 'atualizado_em';

-- Se a coluna não existir, execute:
ALTER TABLE usuarios ADD COLUMN atualizado_em TIMESTAMP NULL DEFAULT NULL AFTER criado_em;

-- Verifique a coluna ultimo_login:
SHOW COLUMNS FROM usuarios LIKE 'ultimo_login';

-- Se não existir, execute:
ALTER TABLE usuarios ADD COLUMN ultimo_login TIMESTAMP NULL DEFAULT NULL AFTER atualizado_em;
```

### Opção 3: Recriar a Tabela com o Schema Atualizado

Se você tem uma instalação nova ou pode recriar a tabela, use o arquivo `corretora_base.sql` que já contém a estrutura completa atualizada.

**ATENÇÃO:** Isso irá apagar todos os dados existentes!

```sql
-- Faça backup primeiro!
DROP TABLE IF EXISTS usuarios;

-- Execute o arquivo corretora_base.sql
SOURCE caminho/para/corretora_base.sql;
```

## Estrutura Atual da Tabela Usuarios

Após a migração, a tabela `usuarios` deve ter as seguintes colunas:

- `id` INT (PRIMARY KEY, AUTO_INCREMENT)
- `nome` VARCHAR(100)
- `email` VARCHAR(100) (UNIQUE)
- `senha` VARCHAR(255)
- `perfil` ENUM('admin', 'corretor')
- `status` TINYINT(1)
- `criado_em` TIMESTAMP
- **`atualizado_em` TIMESTAMP** ← Coluna adicionada
- **`ultimo_login` TIMESTAMP** ← Coluna adicionada

## Verificação

Para verificar se as colunas foram adicionadas corretamente:

```sql
DESCRIBE usuarios;
```

ou

```sql
SHOW COLUMNS FROM usuarios;
```

## Perguntas Frequentes

**P: Por que essas colunas estão faltando?**
R: Provavelmente você criou o banco de dados antes dessas colunas serem adicionadas ao projeto, ou usou uma versão antiga do script SQL.

**P: É seguro executar o script de migração múltiplas vezes?**
R: Sim! O script verifica se as colunas já existem antes de tentar adicioná-las.

**P: Vou perder meus dados?**
R: Não! O comando `ALTER TABLE ADD COLUMN` apenas adiciona novas colunas sem afetar os dados existentes.

**P: E se eu tiver outros erros de colunas faltantes?**
R: Execute o script de migração ou verifique se sua estrutura de banco corresponde ao arquivo `corretora_base.sql`.
