# Corretora Imobiliária - Sistema de Gestão

## 📋 Estrutura do Projeto

corretora-base/
├── public/                     # Arquivos públicos
│   ├── assets/
│   │   ├── css/admin.css
|   |   |-style.css ✔
│   │   ├── images/
│   │   ├── js/ ✔
|   |   ├── main.js ✔
|   |   ├── lightbox.js ✔
|   |   ├── form-validation.js ✔
|   |   └── mobile-menu.js ✔
|   |   |
│   │   └── fonts/
│   ├── uploads/ .htaccess ✔  
|                     # Imagens dos imóveis
├── private/                    # Área administrativa
│   ├── admin/✔
│   ├── includes/✔✔
|   ├── header.php ✔
|   ├── footer.php ✔
|   ├── db.php ✔
|   ├── functions.php ✔
|   └── auth.php ✔
|   |
│   └── imoveis/adicionar.php ✔
├── index.php ✔                    # Página inicial
├── contato.php ✔
├── sobre.php ✔
├── imoveis.php ✔ 
├── imovel-detalhes.php ✔
├── busca.php ✔
├── corretora_base.sql          # Script de criação do banco
├── migration_add_columns.sql   # Script de migração
├── MIGRATION_GUIDE.md          # Guia de migração
└── README.md ✔

## 🚀 Instalação

### 1. Configurar o Banco de Dados

```sql
-- Criar o banco de dados
CREATE DATABASE corretora_base CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Importar a estrutura
SOURCE caminho/para/corretora_base.sql;
```

### 2. Configurar a Conexão

Edite os arquivos de configuração em `private/includes/db.php` e `private/config/config.php` com suas credenciais do banco de dados.

## 🔧 Migrações do Banco de Dados

Se você receber erros como **"Unknown column 'atualizado_em'"**, consulte o arquivo [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) para instruções detalhadas.

### Método Rápido:
Execute via navegador: `http://seu-site/private/admin/migrate_database.php`

Ou execute o arquivo SQL: `migration_add_columns.sql` no phpMyAdmin.

## 📚 Documentação

- **MIGRATION_GUIDE.md** - Guia completo de migração do banco de dados
- **corretora_base.sql** - Script completo de criação do banco
- **migration_add_columns.sql** - Script para adicionar colunas faltantes