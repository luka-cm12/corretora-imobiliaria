# 📝 Alteração: CPF e Email Opcionais no Cadastro de Proprietário

## 📋 Resumo da Alteração

**Data:** 01/11/2025  
**Arquivo modificado:** `private/imoveis/proprietario-cadastrar.php`  
**Objetivo:** Remover obrigatoriedade dos campos CPF e email no cadastro de proprietários

---

## 🔄 Alterações Realizadas

### 1. **Validação do CPF**

**ANTES:**
```php
if ($cpf === '') {
    $errors[] = 'O CPF é obrigatório.';
} else {
    if (!cpf_valido($cpf)) {
        $errors[] = 'Informe um CPF com 11 dígitos.';
    }
}
```

**DEPOIS:**
```php
if ($cpf !== '' && !cpf_valido($cpf)) {
    $errors[] = 'Informe um CPF com 11 dígitos.';
}
```

### 2. **Verificação de Duplicidade**

**ANTES:**
```php
// Sempre verificava duplicidade do CPF
$cpf_norm = normalizar_cpf($cpf);
$cpf_dup = db_query(
    "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? LIMIT 1",
    [$cpf_norm]
);
if (!empty($cpf_dup)) {
    $errors[] = 'Já existe um proprietário cadastrado com este CPF.';
}
```

**DEPOIS:**
```php
// Só verifica duplicidade se CPF foi informado
if ($cpf !== '') {
    $cpf_norm = normalizar_cpf($cpf);
    $cpf_dup = db_query(
        "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? LIMIT 1",
        [$cpf_norm]
    );
    if (!empty($cpf_dup)) {
        $errors[] = 'Já existe um proprietário cadastrado com este CPF.';
    }
}
```

### 3. **Campo HTML**

**ANTES:**
```html
<label>CPF *</label>
<input type="text" name="cpf" required maxlength="14" ...>
<div class="form-text">Somente números ou no formato 000.000.000-00</div>
```

**DEPOIS:**
```html
<label>CPF</label>
<input type="text" name="cpf" maxlength="14" ...>
<div class="form-text">Somente números ou no formato 000.000.000-00 (opcional)</div>
```

---

## ✅ Melhorias Implementadas

| Aspecto | Antes | Depois |
|---------|-------|--------|
| **CPF** | Obrigatório (*) | Opcional |
| **Email** | Opcional | Opcional (mantido) |
| **Validação CPF** | Sempre validava | Só valida se preenchido |
| **Duplicidade CPF** | Sempre verificava | Só verifica se preenchido |
| **Interface** | Asterisco (*) no label | Sem asterisco |
| **Texto de ajuda** | Não indicava opcionalidade | Indica "(opcional)" |

---

## 🧪 Cenários de Teste

### ✅ Casos que devem funcionar:
1. **Apenas nome preenchido** - Cadastro deve ser bem-sucedido
2. **Nome + telefone** - Cadastro deve funcionar
3. **Nome + endereço** - Cadastro deve funcionar
4. **CPF válido preenchido** - Deve validar e verificar duplicidade
5. **Email válido preenchido** - Deve validar formato

### ❌ Casos que devem gerar erro:
1. **Nome vazio** - Campo nome continua obrigatório
2. **CPF inválido** - Se preenchido, deve ter formato correto
3. **Email inválido** - Se preenchido, deve ter formato válido
4. **CPF duplicado** - Se preenchido, não pode ser igual a outro cadastrado

---

## 📊 Impacto no Sistema

### **Benefícios:**
- ✅ **Maior flexibilidade** no cadastro de proprietários
- ✅ **Menos barreiras** para completar o cadastro
- ✅ **Melhor experiência do usuário**
- ✅ **Compatibilidade mantida** com validações existentes

### **Funcionalidades preservadas:**
- ✅ **Validação de CPF** quando preenchido
- ✅ **Verificação de duplicidade** de CPF quando preenchido
- ✅ **Validação de email** quando preenchido
- ✅ **Verificação de duplicidade** de email quando preenchido
- ✅ **Campos obrigatórios** - apenas nome permanece obrigatório

---

## 🔧 Arquivos Relacionados

### **Modificado:**
- `private/imoveis/proprietario-cadastrar.php` - Formulário de cadastro

### **Criados:**
- `teste-proprietario-opcional.php` - Página de teste e documentação

### **Podem ser afetados:**
- Qualquer relatório que dependa de CPF obrigatório
- Integrações que assumam CPF sempre preenchido

---

## 🚀 Como Testar

1. **Acesse:** `private/imoveis/proprietario-cadastrar.php`
2. **Teste:** Cadastre proprietário apenas com nome
3. **Verifique:** Se o cadastro foi bem-sucedido
4. **Teste:** Cadastre com CPF inválido (deve dar erro)
5. **Teste:** Cadastre com email inválido (deve dar erro)

**Página de teste:** `teste-proprietario-opcional.php`

---

## 📝 Observações

- **Banco de dados:** Não foram necessárias alterações na estrutura
- **Compatibilidade:** Proprietários existentes não são afetados
- **Flexibilidade:** Sistema permite tanto uso com CPF quanto sem CPF
- **Validação:** Quando preenchidos, CPF e email são validados normalmente

---

## ✨ Resultado Final

O sistema agora oferece **maior flexibilidade** no cadastro de proprietários, permitindo cadastros rápidos apenas com informações básicas, enquanto mantém todas as validações de qualidade quando campos opcionais são preenchidos.