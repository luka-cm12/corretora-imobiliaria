<?php
// Versão simplificada para cadastro de proprietário - EMERGENCY FIX
require_once(__DIR__ . '/private/includes/db.php');
require_once(__DIR__ . '/private/includes/auth.php');
require_login();

$errors = [];
$success = '';

// Gerar token CSRF
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação básica
    $nome = trim($_POST['nome'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    
    // Apenas validações essenciais
    if (empty($nome)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    // Se não há erros, inserir
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
            $result = db_query($sql, [$nome, $documento, $telefone, $email, $endereco]);
            
            if ($result) {
                $success = "✅ Proprietário cadastrado com sucesso! ID: {$result}";
                $_POST = []; // Limpar formulário
            } else {
                $errors[] = "❌ Falha ao inserir no banco";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Erro: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Simplificado - Proprietário</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #218838; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: inline-block; margin-bottom: 20px; background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <a href="private/imoveis/proprietario-cadastrar.php" class="back-link">← Voltar ao Original</a>
    
    <h2>🚑 Cadastro EMERGENCIAL - Proprietário</h2>
    <p style="color: #666;">Versão simplificada para resolver problemas de cadastro</p>
    
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
            <label>CPF/CNPJ (opcional)</label>
            <input type="text" name="documento" value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>" placeholder="Apenas números" maxlength="20">
        </div>
        
        <div class="form-group">
            <label>Telefone (opcional)</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="(11) 99999-9999" maxlength="20">
        </div>
        
        <div class="form-group">
            <label>Email (opcional)</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@exemplo.com" maxlength="150">
        </div>
        
        <div class="form-group">
            <label>Endereço (opcional)</label>
            <textarea name="endereco" rows="3" maxlength="255" placeholder="Endereço completo"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea>
        </div>
        
        <button type="submit">💾 Cadastrar Proprietário</button>
    </form>
    
    <hr style="margin: 40px 0;">
    
    <h3>🔧 Debug Info</h3>
    <?php
    try {
        $total = db_query("SELECT COUNT(*) as count FROM proprietarios")[0]['count'];
        echo "<p>✅ Total de proprietários: <strong>{$total}</strong></p>";
        
        $last = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 1");
        if (!empty($last)) {
            echo "<p>📋 Último proprietário: <strong>" . htmlspecialchars($last[0]['nome']) . "</strong> (ID: {$last[0]['id_proprietario']})</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    
    <p><a href="private/imoveis/proprietarios-listar.php" style="color: #007bff;">📋 Ver Lista de Proprietários</a></p>
</body>
</html>