<?php
require_once(__DIR__ . '/private/includes/auth.php');
require_login();
require_once(__DIR__ . '/private/includes/db.php');

// Mini teste integrado de exclusão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gerar CSRF token se não existir
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

$message = '';

// Processar teste de exclusão
if ($_POST['test_delete'] ?? false) {
    $message = "<h3>Resultado do teste:</h3>";
    
    try {
        // Criar proprietário de teste
        $nome_teste = "TESTE EXCLUSÃO " . date('H:i:s');
        $insert_id = db_query("INSERT INTO proprietarios (nome, created_at) VALUES (?, NOW())", [$nome_teste]);
        $message .= "<p>✅ Proprietário criado com ID: {$insert_id}</p>";
        
        // Verificar se não tem imóveis
        $imoveis = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$insert_id])[0]['count'];
        $message .= "<p>ℹ️ Imóveis: {$imoveis}</p>";
        
        // Simular o processo de exclusão da página original
        if ($imoveis == 0) {
            $linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$insert_id]);
            $message .= "<p>📊 Linhas afetadas: {$linhas_afetadas}</p>";
            
            if ($linhas_afetadas > 0) {
                $message .= "<p style='color: green;'>✅ <strong>SUCESSO!</strong> A exclusão funcionou corretamente.</p>";
            } else {
                $message .= "<p style='color: red;'>❌ <strong>FALHA!</strong> Nenhuma linha foi excluída.</p>";
            }
        }
    } catch (Exception $e) {
        $message .= "<p style='color: red;'>❌ <strong>ERRO:</strong> " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teste de Exclusão de Proprietário</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .button { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .button:hover { background: #c82333; }
        .message { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Teste de Exclusão de Proprietário</h1>
    
    <p>Este é um teste integrado para verificar se a função de exclusão está funcionando corretamente.</p>
    
    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="test_delete" value="1">
            
            <button type="submit" class="button" onclick="return confirm('Executar teste de exclusão?')">
                🗑️ Executar Teste de Exclusão
            </button>
        </form>
        
        <p><small>Este teste criará um proprietário temporário e tentará excluí-lo imediatamente.</small></p>
    </div>
    
    <?php if ($message): ?>
        <div class="message">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <hr>
    
    <h3>Proprietários atuais no sistema:</h3>
    
    <?php
    try {
        $proprietarios = db_query("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
            FROM proprietarios p 
            ORDER BY p.id_proprietario DESC 
            LIMIT 10
        ");
        
        if (empty($proprietarios)) {
            echo "<p>Nenhum proprietário encontrado.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Imóveis</th><th>Pode Excluir?</th></tr>";
            
            foreach ($proprietarios as $prop) {
                $pode_excluir = $prop['total_imoveis'] == 0 ? 'Sim' : 'Não';
                $cor = $prop['total_imoveis'] == 0 ? 'green' : 'red';
                
                echo "<tr>";
                echo "<td>{$prop['id_proprietario']}</td>";
                echo "<td>" . htmlspecialchars($prop['nome']) . "</td>";
                echo "<td>" . htmlspecialchars($prop['email'] ?? 'N/A') . "</td>";
                echo "<td>{$prop['total_imoveis']}</td>";
                echo "<td style='color: {$cor}; font-weight: bold;'>{$pode_excluir}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Erro ao listar proprietários: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <hr>
    <p><a href="private/imoveis/proprietarios-listar.php">← Voltar para a listagem de proprietários</a></p>
    
</body>
</html>