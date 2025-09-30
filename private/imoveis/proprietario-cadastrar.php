<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

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

function normalizar_cpf(string $valor): string {
  return preg_replace('/\D+/', '', $valor);
}

function cpf_valido(string $cpf): bool {
  // Validação mais permissiva: aceita 11 dígitos (com ou sem pontuação)
  $cpf = normalizar_cpf($cpf);
  if (strlen($cpf) !== 11) return false;
  // recusa sequência de dígitos iguais (ex: 00000000000)
  if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
  return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Valida CSRF
  $csrf = $_POST['csrf_token'] ?? '';
  if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    $errors[] = 'Sessão expirada ou token inválido. Atualize a página e tente novamente.';
  }

  $nome = trim($_POST['nome'] ?? '');
  $cpf = trim($_POST['cpf'] ?? '');
  $telefone = trim($_POST['telefone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $endereco = trim($_POST['endereco'] ?? '');

  if ($nome === '') {
    $errors[] = 'O nome é obrigatório.';
  }
  if ($cpf === '') {
    $errors[] = 'O CPF é obrigatório.';
  } else {
    if (!cpf_valido($cpf)) {
      $errors[] = 'Informe um CPF com 11 dígitos.';
    }
  }
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Informe um e-mail válido.';
  }

  // Verificações de duplicidade (CPF e e-mail)
  if (empty($errors)) {
    $cpf_norm = normalizar_cpf($cpf);
    // Compara CPF normalizado contra CPF armazenado removendo pontuação
    $cpf_dup = db_query(
      "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? LIMIT 1",
      [$cpf_norm]
    );
    if (!empty($cpf_dup)) {
      $errors[] = 'Já existe um proprietário cadastrado com este CPF.';
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
    $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
    $result = db_query($sql, [$nome, $cpf, $telefone, $email, $endereco]);

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
      <label>CPF *</label>
      <input type="text" name="cpf" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" placeholder="000.000.000-00" required maxlength="14" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}|\d{11}" title="Informe 11 dígitos ou no formato 000.000.000-00">
      <div class="form-text">Somente números ou no formato 000.000.000-00</div>
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

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>