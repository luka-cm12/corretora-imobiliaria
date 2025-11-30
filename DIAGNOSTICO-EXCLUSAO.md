# 🔧 DIAGNÓSTICO - Exclusão de Proprietários

## 🚨 PROBLEMA
A funcionalidade de exclusão de proprietários não está funcionando na página `proprietarios-listar.php`.

## 🧪 TESTES DISPONÍVEIS

Execute os testes na seguinte ordem para identificar o problema:

### 1. 🎯 **TESTE SQL PURO** (Mais importante)
**Arquivo:** `teste-sql-puro.php`
- Testa exclusão diretamente no banco com PDO puro
- Não usa a função `db_query()`
- Cria proprietário teste e tenta excluir
- **EXECUTE ESTE PRIMEIRO!**

### 2. 🔍 **DIAGNÓSTICO COMPLETO**
**Arquivo:** `diagnostico-exclusao-completo.php`
- Verifica estrutura da tabela
- Lista constraints e índices
- Identifica proprietários com problemas
- Permite teste individual de cada proprietário

### 3. 🖥️ **TESTE DA INTERFACE**
**Arquivo:** `teste-exclusao-direto.php`
- Replica exatamente o comportamento da página original
- Mostra logs detalhados do processo
- Formulários idênticos aos reais

### 4. 🛠️ **TESTE MYSQL DIRETO**
**Arquivo:** `teste-mysql-direto.php`
- Verifica foreign keys e triggers
- Testa constraints do banco
- Cria e exclui proprietário de teste

## 🔍 DEBUG ATIVADO

O arquivo `proprietarios-listar.php` foi modificado para mostrar logs detalhados:
- Informações do POST recebido
- Validação CSRF
- Verificação de imóveis associados
- Resultado da exclusão
- Erros detalhados

## 📋 CHECKLIST DE VERIFICAÇÃO

### ✅ Estrutura Verificada:
- [x] Campo `id_proprietario` existe
- [x] Tabela `proprietarios` existe
- [x] Tabela `imoveis` existe com campo `id_proprietario`

### 🔍 Verificar:
- [ ] **Constraints de Foreign Key** - podem estar bloqueando
- [ ] **Triggers** - podem estar interferindo  
- [ ] **Campos NOT NULL** - CPF vazio pode causar problemas
- [ ] **Unique Keys** - CPF/Email duplicados
- [ ] **Função `db_query()`** - pode ter bug

### 🧪 Testes a Executar:

1. **PRIMEIRO:** Execute `teste-sql-puro.php`
   - Se funcionar: o problema está na função `db_query()`
   - Se não funcionar: o problema está no banco de dados

2. **SEGUNDO:** Execute `diagnostico-exclusao-completo.php`
   - Verifique constraints e triggers
   - Identifique proprietários problemáticos

3. **TERCEIRO:** Use a página original com debug ativado
   - Tente excluir um proprietário sem imóveis
   - Analise os logs que aparecerão na tela

## 🎯 PRÓXIMOS PASSOS

### Se o teste SQL puro FUNCIONAR:
- O problema está na função `db_query()` ou na lógica PHP
- Verificar arquivo `private/includes/db.php`
- Comparar com SQL direto

### Se o teste SQL puro NÃO FUNCIONAR:
- O problema está no banco de dados
- Verificar constraints, triggers, permissions
- Verificar estrutura da tabela

## 🔧 CORREÇÃO TEMPORÁRIA

Se precisar de uma solução imediata, pode usar SQL direto:

```php
// Substituir a linha:
$linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id]);

// Por:
global $conn;
$stmt = $conn->prepare("DELETE FROM proprietarios WHERE id_proprietario = ?");
$stmt->execute([$id]);
$linhas_afetadas = $stmt->rowCount();
```

## 📞 RELATÓRIO DO TESTE

Após executar os testes, documente:

1. **Teste SQL Puro:** ✅ Funcionou / ❌ Não funcionou
2. **Erro específico:** _descreva aqui_
3. **Constraints encontradas:** _liste aqui_
4. **Triggers encontrados:** _liste aqui_
5. **Logs de debug:** _cole aqui_

---

**EXECUTE OS TESTES E REPORTE OS RESULTADOS!** 🚀