<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');
require_once(__DIR__ . '/../includes/auth.php');
require_login();

$errors = [];
$success = '';

// CSRF token (gera se não existir)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  } catch (Exception $e) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
  }
}

function normalizar_documento(string $valor): string {
  return preg_replace('/\D+/', '', $valor);
}

function cpf_valido(string $cpf): bool {
  if (empty(trim($cpf))) return false;
  
  // Validação: aceita 11 dígitos (com ou sem pontuação)
  $cpf = normalizar_documento($cpf);
  if (strlen($cpf) !== 11) return false;
  // recusa sequência de dígitos iguais (ex: 00000000000)
  if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
  if (!ctype_digit($cpf)) return false;
  return true;
}

function cnpj_valido(string $cnpj): bool {
  if (empty(trim($cnpj))) return false;
  
  // Validação: aceita 14 dígitos (com ou sem pontuação)
  $cnpj = normalizar_documento($cnpj);
  if (strlen($cnpj) !== 14) return false;
  // recusa sequência de dígitos iguais (ex: 00000000000000)
  if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
  if (!ctype_digit($cnpj)) return false;
  return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Valida CSRF
  $csrf = $_POST['csrf_token'] ?? '';
  if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    $errors[] = 'Sessão expirada ou token inválido. Atualize a página e tente novamente.';
  }

  $nome = trim($_POST['nome'] ?? '');
  $tipo_documento = trim($_POST['tipo_documento'] ?? 'cpf');
  $documento = trim($_POST['documento'] ?? '');
  $telefone = trim($_POST['telefone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $endereco = trim($_POST['endereco'] ?? '');

  if ($nome === '') {
    $errors[] = 'O nome é obrigatório.';
  }
  
  // Validação do documento baseado no tipo escolhido (OPCIONAL)
  if ($documento !== '') {
    if ($tipo_documento === 'cpf' && !cpf_valido($documento)) {
      $errors[] = 'Informe um CPF válido com 11 dígitos. Exemplo: 123.456.789-01';
    } elseif ($tipo_documento === 'cnpj' && !cnpj_valido($documento)) {
      $errors[] = 'Informe um CNPJ válido com 14 dígitos. Exemplo: 12.345.678/0001-95';
    }
  }
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Informe um e-mail válido.';
  }

  if (empty($errors)) {
    try {
      // Detecta a estrutura da tabela automaticamente
      $columns_result = db_query("SHOW COLUMNS FROM proprietarios");
      $columns = [];
      foreach ($columns_result as $col) {
        $columns[] = $col['Field'];
      }
      
      $tem_tipo_documento = in_array('tipo_documento', $columns);
      $tem_cnpj = in_array('cnpj', $columns);
      
      // Verifica duplicidade baseada na estrutura disponível
      if ($documento !== '') {
        $doc_norm = normalizar_documento($documento);
        
        if ($tem_tipo_documento && $tem_cnpj) {
          // Nova estrutura - verifica nas colunas corretas
          if ($tipo_documento === 'cpf') {
            $doc_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE cpf = ? LIMIT 1", [$doc_norm]);
            if (!empty($doc_dup)) {
              $errors[] = 'Já existe um proprietário com este CPF.';
            }
          } else {
            $doc_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE cnpj = ? LIMIT 1", [$doc_norm]);
            if (!empty($doc_dup)) {
              $errors[] = 'Já existe um proprietário com este CNPJ.';
            }
          }
        } else {
          // Estrutura antiga - verifica na coluna cpf unificada
          $doc_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE cpf = ? LIMIT 1", [$doc_norm]);
          if (!empty($doc_dup)) {
            $errors[] = 'Já existe um proprietário com este documento.';
          }
        }
      }

      if ($email !== '' && empty($errors)) {
        $email_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE LOWER(email) = LOWER(?) LIMIT 1", [$email]);
        if (!empty($email_dup)) {
          $errors[] = 'Este e-mail já está cadastrado.';
        }
      }

      // Cadastra se não há erros
      if (empty($errors)) {
        $doc_limpo = $documento ? normalizar_documento($documento) : null;
        
        if ($tem_tipo_documento && $tem_cnpj) {
          // Nova estrutura com colunas separadas
          if ($tipo_documento === 'cpf') {
            $sql = "INSERT INTO proprietarios (nome, cpf, cnpj, tipo_documento, telefone, email, endereco, data_cadastro) VALUES (?, ?, NULL, 'cpf', ?, ?, ?, NOW())";
            $params = [$nome, $doc_limpo, $telefone ?: null, $email ?: null, $endereco ?: null];
          } else {
            $sql = "INSERT INTO proprietarios (nome, cpf, cnpj, tipo_documento, telefone, email, endereco, data_cadastro) VALUES (?, NULL, ?, 'cnpj', ?, ?, ?, NOW())";
            $params = [$nome, $doc_limpo, $telefone ?: null, $email ?: null, $endereco ?: null];
          }
        } else {
          // Estrutura antiga - usa coluna cpf para ambos
          $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())";
          $params = [$nome, $doc_limpo, $telefone ?: null, $email ?: null, $endereco ?: null];
        }

        $result = db_query($sql, $params);

        if ($result > 0) {
          $tipo_msg = ($tipo_documento === 'cpf') ? 'CPF' : 'CNPJ';
          $doc_info = $documento ? " ({$tipo_msg}: {$documento})" : '';
          $success = "✅ Proprietário '{$nome}' cadastrado com sucesso! ID: {$result}{$doc_info}";
          $_POST = []; // limpa form
        } else {
          $errors[] = 'Falha no cadastro. Tente novamente.';
        }
      }

    } catch (Exception $e) {
      $errors[] = 'Erro no sistema: ' . $e->getMessage();
    }
  }
}

$page_title = 'Cadastrar Proprietário';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="breadcrumb">
  <a href="listar.php">Imóveis</a> / <span>Cadastrar Proprietário</span>
  <div style="float:right;"><a href="adicionar.php">Voltar para cadastro de imóveis</a></div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul style="margin-left: 18px;">
      <?php foreach ($errors as $msg): ?>
        <li><?= htmlspecialchars($msg) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="imovel-form">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
  <div class="form-row">
    <div class="form-group">
      <label>Nome *</label>
      <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required maxlength="150" autocomplete="name" autofocus>
    </div>
    <div class="form-group">
      <label>Tipo de Documento</label>
      <select name="tipo_documento" id="tipo_documento" onchange="alterarTipoDocumento()">
        <option value="cpf" <?= ($_POST['tipo_documento'] ?? 'cpf') === 'cpf' ? 'selected' : '' ?>>CPF (Pessoa Física)</option>
        <option value="cnpj" <?= ($_POST['tipo_documento'] ?? 'cpf') === 'cnpj' ? 'selected' : '' ?>>CNPJ (Pessoa Jurídica)</option>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group" style="width: 100%;">
      <label id="label_documento">CPF (opcional)</label>
      <input type="text" name="documento" id="documento" value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>" 
             placeholder="000.000.000-00" maxlength="18" 
             title="Campo opcional - informe se tiver documento">
      <div class="form-text" id="texto_documento">Somente números ou no formato 000.000.000-00 (opcional)</div>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Telefone (opcional)</label>
      <input type="text" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="(99) 99999-9999" maxlength="20" autocomplete="tel">
    </div>
    <div class="form-group">
      <label>Email (opcional)</label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@exemplo.com" maxlength="150" autocomplete="email">
    </div>
  </div>

  <div class="form-group">
    <label>Endereço (opcional)</label>
    <textarea name="endereco" rows="3" maxlength="255" placeholder="Endereço completo do proprietário"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea>
  </div>

  <div class="form-actions" style="display:flex; gap:10px;">
    <button type="submit" class="btn-primary">Salvar Proprietário</button>
    <a href="adicionar.php" class="btn-cancel">Cancelar</a>
  </div>
  <div class="form-text">Campos marcados com * são obrigatórios.</div>
</form>

<script>
function alterarTipoDocumento() {
  const tipoSelect = document.getElementById('tipo_documento');
  const documentoInput = document.getElementById('documento');
  const labelDocumento = document.getElementById('label_documento');
  const textoDocumento = document.getElementById('texto_documento');
  
  if (tipoSelect.value === 'cpf') {
    labelDocumento.textContent = 'CPF (opcional)';
    documentoInput.placeholder = '000.000.000-00';
    documentoInput.maxLength = 14;
    textoDocumento.textContent = 'Somente números ou no formato 000.000.000-00 (opcional)';
  } else {
    labelDocumento.textContent = 'CNPJ (opcional)';
    documentoInput.placeholder = '00.000.000/0000-00';
    documentoInput.maxLength = 18;
    textoDocumento.textContent = 'Somente números ou no formato 00.000.000/0000-00 (opcional)';
  }
  
  // Limpa o valor atual quando muda o tipo
  documentoInput.value = '';
}

// Executa quando a página carrega para definir o estado inicial
document.addEventListener('DOMContentLoaded', function() {
  alterarTipoDocumento();
});

// Máscaras para CPF e CNPJ
document.getElementById('documento').addEventListener('input', function(e) {
  const tipoSelect = document.getElementById('tipo_documento');
  let valor = e.target.value.replace(/\D/g, '');
  
  if (tipoSelect.value === 'cpf') {
    // Máscara CPF: 000.000.000-00
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  } else {
    // Máscara CNPJ: 00.000.000/0000-00
    valor = valor.replace(/(\d{2})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1/$2');
    valor = valor.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
  }
  
  e.target.value = valor;
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>