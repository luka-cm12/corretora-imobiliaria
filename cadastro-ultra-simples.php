<?php
require_once(__DIR__ . '/private/includes/db.php');
require_once(__DIR__ . '/private/includes/auth.php');
require_login();

$errors = [];
$success = '';

// CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $errors[] = 'Token inválido. Recarregue a página.';
    }

    $nome = trim($_POST['nome'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento'] ?? 'cpf');
    $documento = trim($_POST['documento'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    // Validações básicas
    if (empty($nome)) {
        $errors[] = 'Nome é obrigatório.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }

    // Inserção SEM validação de documento
    if (empty($errors)) {
        try {
            // Tentar estrutura nova primeiro
            try {
                if ($tipo_documento === 'cpf') {
                    $sql = "INSERT INTO proprietarios (nome, tipo_documento, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?, ?)";
                    $params = [$nome, $tipo_documento, $documento, $telefone, $email, $endereco];
                } else {
                    $sql = "INSERT INTO proprietarios (nome, tipo_documento, cnpj, telefone, email, endereco) VALUES (?, ?, ?, ?, ?, ?)";
                    $params = [$nome, $tipo_documento, $documento, $telefone, $email, $endereco];
                }
                $result = db_query($sql, $params);
                $success = "✅ Sucesso! Proprietário cadastrado com ID: {$result}";
                $_POST = [];
            } catch (Exception $e1) {
                // Fallback para estrutura antiga
                $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
                $result = db_query($sql, [$nome, $documento, $telefone, $email, $endereco]);
                $success = "✅ Sucesso (modo compatibilidade)! ID: {$result}";
                $_POST = [];
            }
        } catch (Exception $e) {
            $errors[] = "❌ Erro: " . $e->getMessage();
        }
    }
}

$page_title = 'Cadastro Simplificado';
include __DIR__ . '/private/includes/admin-header.php';
?>

<style>
.simple-form { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
.btn { padding: 12px 24px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; }
.btn-success { background: #28a745; color: white; }
.btn-success:hover { background: #218838; }
.alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<div class="simple-form">
    <h1>🚀 Cadastro ULTRA SIMPLIFICADO</h1>
    <p style="color: #666;">Sem validações chatas - cadastre qualquer coisa!</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="form-group">
            <label>Nome Completo *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required maxlength="150">
        </div>

        <div class="form-group">
            <label>Tipo de Documento</label>
            <select name="tipo_documento">
                <option value="cpf" <?= ($_POST['tipo_documento'] ?? 'cpf') === 'cpf' ? 'selected' : '' ?>>CPF</option>
                <option value="cnpj" <?= ($_POST['tipo_documento'] ?? 'cpf') === 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
            </select>
        </div>

        <div class="form-group">
            <label>CPF/CNPJ (qualquer formato)</label>
            <input type="text" name="documento" value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>" placeholder="Digite qualquer coisa - sem validação" maxlength="25">
            <small style="color: #666;">Aceita QUALQUER formato - números, pontos, traços, etc.</small>
        </div>

        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="(11) 99999-9999" maxlength="20">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@exemplo.com" maxlength="150">
        </div>

        <div class="form-group">
            <label>Endereço</label>
            <textarea name="endereco" rows="3" maxlength="255" placeholder="Endereço completo"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Cadastrar Agora</button>
        <a href="private/imoveis/proprietarios-listar.php" class="btn" style="background: #6c757d; color: white; margin-left: 10px;">📋 Ver Lista</a>
    </form>

    <hr style="margin: 30px 0;">
    
    <h3>🔧 Debug Info</h3>
    <?php
    try {
        $total = db_query("SELECT COUNT(*) as count FROM proprietarios")[0]['count'];
        echo "<p>Total de proprietários: <strong>{$total}</strong></p>";
        
        if ($total > 0) {
            $ultimo = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 1")[0];
            echo "<p>Último cadastrado: <strong>" . htmlspecialchars($ultimo['nome']) . "</strong> (ID: {$ultimo['id_proprietario']})</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</div>

<?php include __DIR__ . '/private/includes/admin-footer.php'; ?>