<?php
// Cadastro DIRETO sem validações - TESTE EMERGENCIAL
require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🚨 CADASTRO EMERGENCIAL - TESTE DIRETO</h1>";
echo "<p style='color: #666;'>Cadastro direto no banco sem validações</p>";

$success = '';
$error = '';

if (isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($nome)) {
        $error = "Nome é obrigatório";
    } else {
        try {
            // Inserção SUPER simples - só campos básicos
            $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email) VALUES (?, ?, ?, ?)";
            $result = db_query($sql, [$nome, $cpf, $telefone, $email]);
            
            if ($result) {
                $success = "✅ SUCESSO! Proprietário cadastrado com ID: {$result}";
                $_POST = [];
            } else {
                $error = "❌ Falha: resultado = " . var_export($result, true);
            }
            
        } catch (Exception $e) {
            $error = "❌ ERRO: " . $e->getMessage();
        }
    }
}

// Mostrar proprietários existentes
try {
    $proprietarios = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
    $total = db_query("SELECT COUNT(*) as count FROM proprietarios")[0]['count'];
} catch (Exception $e) {
    $proprietarios = [];
    $total = 0;
    if (empty($error)) {
        $error = "Erro ao buscar dados: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro Emergencial</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #218838; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🚨 CADASTRO EMERGENCIAL</h1>
    <p>Cadastro direto no banco - sem validações complicadas</p>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="info">
        <strong>Total de proprietários:</strong> <?= $total ?>
    </div>
    
    <h2>📝 Cadastrar Novo</h2>
    <form method="POST">
        <div class="form-group">
            <label>Nome Completo *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label>CPF/CNPJ (qualquer formato)</label>
            <input type="text" name="cpf" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" placeholder="123.456.789-00 ou qualquer coisa">
        </div>
        
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="(11) 99999-9999">
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@exemplo.com">
        </div>
        
        <button type="submit" name="cadastrar" class="btn">💾 CADASTRAR AGORA</button>
    </form>
    
    <?php if (!empty($proprietarios)): ?>
        <h2>📋 Últimos Cadastrados</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Email</th>
            </tr>
            <?php foreach ($proprietarios as $p): ?>
                <tr>
                    <td><?= $p['id_proprietario'] ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['cpf'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['telefone'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['email'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    
    <hr>
    <h3>🔗 Links Úteis</h3>
    <p>
        <a href="diagnostico-definitivo.php">🔧 Diagnóstico Completo</a> |
        <a href="private/imoveis/proprietarios-listar.php">📋 Lista Oficial</a> |
        <a href="private/imoveis/proprietario-cadastrar.php">📝 Cadastro Original</a>
    </p>
</body>
</html>