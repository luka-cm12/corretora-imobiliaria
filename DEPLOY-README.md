# 🚀 DEPLOY RÁPIDO - Corretora Claudia
**Servidor: sh00066 (Hostgator)**

## ✅ Checklist Essencial

### 1. No Hostgator (cPanel):
- [ ] Criar banco: `sh00066_corretor_corretora`
- [ ] Criar usuário: `sh00066_corretor_admin` 
- [ ] Definir senha e anotar
- [ ] Importar banco de dados (arquivo .sql)

### 2. Upload de arquivos:
- [ ] Enviar TODOS os arquivos EXCETO `config.local.php`
- [ ] Incluir obrigatoriamente o `.htaccess`
- [ ] Definir permissões: pasta `public/uploads/` = 755

### 3. Configuração automática:
- [ ] Acessar: `https://seudominio.com/setup-deploy.php`
- [ ] Verificar se os campos já vêm preenchidos:
  - Banco: `sh00066_corretor_corretora`
  - Usuário: `sh00066_corretor_admin`
- [ ] Inserir apenas a senha que você definiu
- [ ] Clicar em "Configurar Sistema"
- [ ] **REMOVER** `setup-deploy.php` após configurar

### 4. Teste final:
- [ ] Site principal funcionando
- [ ] Teste conexão: `https://seudominio.com/teste-conexao.php`
- [ ] Admin: `https://seudominio.com/private/admin/`

## 📋 Informações do Servidor (sh00066):
- **Pacote**: M_100
- **MySQL**: 8.0.43-34
- **PHP**: Verificar versão compatível
- **Apache**: 2.4.65

## ⚠️ IMPORTANTE:
- **NÃO** envie o arquivo `config.local.php` (apenas para desenvolvimento local)
- **REMOVA** `setup-deploy.php` após configurar
- **REMOVA** `teste-conexao.php` após verificar que está funcionando
- O Hostgator adiciona automaticamente o prefixo `sh00066_` aos nomes de banco e usuário

## 📞 Suporte:
Consulte o arquivo `DEPLOY-HOSTGATOR.md` para instruções detalhadas.