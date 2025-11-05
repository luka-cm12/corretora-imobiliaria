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
  // Validação mais permissiva: aceita 11 dígitos (com ou sem pontuação)
  $cpf = normalizar_documento($cpf);
  if (strlen($cpf) !== 11) return false;
  // recusa sequência de dígitos iguais (ex: 00000000000)
  if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
  return true;
}

function cnpj_valido(string $cnpj): bool {
  // Validação básica: aceita 14 dígitos (com ou sem pontuação)
  $cnpj = normalizar_documento($cnpj);
  if (strlen($cnpj) !== 14) return false;
  // recusa sequência de dígitos iguais (ex: 00000000000000)
  if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
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
  
  // Validação do documento baseado no tipo escolhido
  if ($documento !== '') {
    if ($tipo_documento === 'cpf' && !cpf_valido($documento)) {
      $errors[] = 'Informe um CPF válido com 11 dígitos.';
    } elseif ($tipo_documento === 'cnpj' && !cnpj_valido($documento)) {
      $errors[] = 'Informe um CNPJ válido com 14 dígitos.';
    }
  }
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Informe um e-mail válido.';
  }

  // Verificações de duplicidade (documento e e-mail)
  if (empty($errors)) {
    // Só verifica duplicidade de documento se foi informado
    if ($documento !== '') {
      $doc_norm = normalizar_documento($documento);
      // Verifica duplicidade do documento nos campos CPF ou CNPJ
      if ($tipo_documento === 'cpf') {
        $doc_dup = db_query(
          "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', ''), ' ', '') = ? LIMIT 1",
          [$doc_norm]
        );
        if (!empty($doc_dup)) {
          $errors[] = 'Já existe um proprietário cadastrado com este CPF.';
        }
      } else {
        // Verifica CNPJ na coluna apropriada (cpf por enquanto, até criar coluna cnpj)
        $doc_dup = db_query(
          "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', ''), ' ', '') = ? AND LENGTH(REPLACE(REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', ''), ' ', '')) = 14 LIMIT 1",
          [$doc_norm]
        );
        if (!empty($doc_dup)) {
          $errors[] = 'Já existe um proprietário cadastrado com este CNPJ.';
        }
      }
    }

    if ($email !== '') {
      $email_dup = db_query(
        "SELECT id_proprietario FROM proprietarios WHERE LOWER(email) = LOWER(?) LIMIT 1",
        [$email]
      );
      if (!empty($email_dup)) {
        $errors[] = 'Este e-mail já está cadastrado.';
      }
    }
  }

  if (empty($errors)) {
    // Por enquanto, salva tanto CPF quanto CNPJ no mesmo campo até criar coluna separada
    $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
    $result = db_query($sql, [$nome, $documento, $telefone, $email, $endereco]);

    if ($result > 0) {
      $success = 'Proprietário cadastrado com sucesso!';
      $_POST = []; // limpa form
    } else {
      $errors[] = 'Erro ao cadastrar proprietário.';
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
      <label id="label_documento">CPF</label>
      <input type="text" name="documento" id="documento" value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>" 
             placeholder="000.000.000-00" maxlength="18" 
             title="Informe o documento válido">
      <div class="form-text" id="texto_documento">Somente números ou no formato 000.000.000-00 (opcional)</div>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Telefone</label>
      <input type="text" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="(99) 99999-9999" maxlength="20" autocomplete="tel">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@exemplo.com" maxlength="150" autocomplete="email">
    </div>
  </div>

  <div class="form-group">
    <label>Endereço</label>
    <textarea name="endereco" rows="3" maxlength="255"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea>
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
    labelDocumento.textContent = 'CPF';
    documentoInput.placeholder = '000.000.000-00';
    documentoInput.maxLength = 14;
    textoDocumento.textContent = 'Somente números ou no formato 000.000.000-00 (opcional)';
  } else {
    labelDocumento.textContent = 'CNPJ';
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