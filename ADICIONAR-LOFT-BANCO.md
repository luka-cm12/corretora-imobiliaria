# 🏙️ Adicionando Tipo LOFT ao Banco de Dados

## 📋 Instruções para Executar

### **Método 1: phpMyAdmin**
1. Acesse o phpMyAdmin (http://localhost/phpmyadmin)
2. Selecione seu banco de dados (ex: `corretora_base`)
3. Clique na aba **SQL**
4. Copie e cole o conteúdo do arquivo `adicionar-tipo-loft.sql`
5. Clique em **Executar**

### **Método 2: Terminal MySQL**
```bash
# Conectar ao MySQL
mysql -u root -p

# Selecionar o banco
USE corretora_base;

# Executar o script
SOURCE adicionar-tipo-loft.sql;
```

### **Método 3: Comando Direto**
```sql
ALTER TABLE imoveis MODIFY COLUMN tipo ENUM('casa','casa_condominio','apartamento','apartamento_mobiliado','sobrado','chacara','semi_mobiliado','terreno','loft','comercial') NOT NULL;
```

## ✅ Verificação

Após executar o script, você pode verificar se funcionou:

```sql
-- Ver a estrutura da coluna tipo
SHOW COLUMNS FROM imoveis LIKE 'tipo';

-- Deve mostrar todos os tipos incluindo 'loft'
DESCRIBE imoveis;
```

## 🎯 Resultado Esperado

O campo `tipo` deve aceitar agora **10 tipos**:
- casa
- casa_condominio  
- apartamento
- apartamento_mobiliado
- sobrado
- chacara
- semi_mobiliado
- terreno
- **loft** ← NOVO!
- comercial

## 🔧 Em Caso de Erro

Se der erro, pode ser porque:
1. **Já existe o tipo**: Neste caso está tudo ok, ignore o erro
2. **Permissões**: Certifique-se de ter privilégios ALTER na tabela
3. **Tabela não existe**: Verifique se está no banco correto

## 📁 Arquivos Relacionados

- `adicionar-tipo-loft.sql` - Script principal
- `adicionar-campos-financeiros.sql` - Script anterior para outros campos
- Códigos PHP já atualizados com o novo tipo 'loft'