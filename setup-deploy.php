<?php
/**
 * Script de configuração pós-deploy
 * Execute este arquivo UMA VEZ após fazer o upload para configurar automaticamente
 * REMOVA este arquivo após a execução por segurança
 */

// Verificar se já foi executado
$configFile = 'private/config/.deploy_completed';
if (file_exists($configFile)) {
    die('❌ Este script já foi executado. Remova o arquivo setup-deploy.php por segurança.');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Deploy - Corretora Claudia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .success { color: #27ae60; padding: 15px; background: #d5f4e6; border-radius: 4px; margin: 10px 0; }
        .error { color: #e74c3c; padding: 15px; background: #fdf2f2; border-radius: 4px; margin: 10px 0; }
        .warning { color: #f39c12; padding: 15px; background: #fef9e7; border-radius: 4px; margin: 10px 0; }
        .info { color: #3498db; padding: 15px; background: #ebf3fd; border-radius: 4px; margin: 10px 0; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Setup Deploy - Corretora Claudia</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';
            
            if (empty($dbName) || empty($dbUser)) {
                echo '<div class="error">❌ Por favor, preencha todos os campos obrigatórios.</div>';
            } else {
                // Testar conexão
                try {
                    $testConn = new PDO(
                        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                        $dbUser,
                        $dbPass,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    echo '<div class="success">✅ Conexão com banco de dados bem-sucedida!</div>';
                    
                    // Atualizar arquivo de configuração
                    $configPath = 'private/config/config.php';
                    $configContent = file_get_contents($configPath);
                    
                    // Substituir a linha da senha
                    $newLine = "if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: (\$__isLocal ? '' : '{$dbPass}'));";
                    $configContent = preg_replace(
                        '/if \(!defined\(\'DB_PASS\'\)\) define\(\'DB_PASS\',.*?\);/',
                        $newLine,
                        $configContent
                    );
                    
                    if (file_put_contents($configPath, $configContent)) {
                        echo '<div class="success">✅ Arquivo de configuração atualizado!</div>';
                        
                        // Criar diretório uploads se não existir
                        $uploadsDir = 'public/uploads';
                        if (!is_dir($uploadsDir)) {
                            if (mkdir($uploadsDir, 0755, true)) {
                                echo '<div class="success">✅ Diretório uploads criado!</div>';
                            } else {
                                echo '<div class="warning">⚠️ Não foi possível criar o diretório uploads. Crie manualmente.</div>';
                            }
                        } else {
                            echo '<div class="success">✅ Diretório uploads já existe!</div>';
                        }
                        
                        // Marcar como concluído
                        file_put_contents($configFile, date('Y-m-d H:i:s'));
                        
                        echo '<div class="info">
                            <h3>🎉 Setup concluído com sucesso!</h3>
                            <p><strong>PRÓXIMOS PASSOS:</strong></p>
                            <ul>
                                <li>✅ Teste seu site: <a href="index.php" target="_blank">Ir para o site</a></li>
                                <li>✅ Teste a conexão: <a href="teste-conexao.php" target="_blank">Teste de conexão</a></li>
                                <li>✅ Acesse o admin: <a href="private/admin/" target="_blank">Painel Admin</a></li>
                                <li>🔒 <strong>REMOVA este arquivo (setup-deploy.php) por segurança!</strong></li>
                            </ul>
                        </div>';
                        
                    } else {
                        echo '<div class="error">❌ Erro ao atualizar arquivo de configuração.</div>';
                    }
                    
                } catch (PDOException $e) {
                    echo '<div class="error">❌ Erro na conexão: ' . $e->getMessage() . '</div>';
                    echo '<div class="info">
                        <strong>Verifique:</strong><br>
                        • Se o banco de dados foi criado<br>
                        • Se o usuário tem permissões<br>
                        • Se a senha está correta
                    </div>';
                }
            }
        } else {
        ?>
        
        <div class="info">
            <h3>📋 Configuração do Banco de Dados</h3>
            <p>Insira as credenciais do banco de dados do Hostgator:</p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Host do Banco:</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label>Nome do Banco:</label>
                <input type="text" name="db_name" value="sh00066_corretor_corretora" required>
                <small>Formato: sh00066_nomeDoBanco (baseado no servidor sh00066)</small>
            </div>
            
            <div class="form-group">
                <label>Usuário do Banco:</label>
                <input type="text" name="db_user" value="sh00066_corretor_admin" required>
                <small>Formato: sh00066_nomeUsuario (baseado no servidor sh00066)</small>
            </div>
            
            <div class="form-group">
                <label>Senha do Banco:</label>
                <input type="password" name="db_pass" placeholder="Senha que você definiu" required>
                <small>Senha que você definiu no cPanel</small>
            </div>
            
            <button type="submit" class="btn">🚀 Configurar Sistema</button>
        </form>
        
        <?php } ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
            <strong>⚠️ IMPORTANTE:</strong> Remova este arquivo após a configuração por motivos de segurança.
        </div>
    </div>
</body>
</html>