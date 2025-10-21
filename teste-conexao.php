<?php
/**
 * Script de teste de conexão com o banco de dados
 * Use este arquivo para verificar se a conexão está funcionando após o deploy
 */

// Incluir configurações
require_once 'private/config/config.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão - Corretora Claudia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #27ae60; padding: 15px; background: #d5f4e6; border-radius: 4px; margin: 10px 0; }
        .error { color: #e74c3c; padding: 15px; background: #fdf2f2; border-radius: 4px; margin: 10px 0; }
        .info { color: #3498db; padding: 15px; background: #ebf3fd; border-radius: 4px; margin: 10px 0; }
        .config { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; }
        pre { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Teste de Conexão - Corretora Claudia</h1>
        
        <div class="info">
            <strong>Data/Hora:</strong> <?= date('d/m/Y H:i:s') ?><br>
            <strong>Servidor:</strong> <?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?><br>
            <strong>PHP Version:</strong> <?= PHP_VERSION ?>
        </div>

        <h2>📋 Configurações do Banco</h2>
        <div class="config">
            <strong>Host:</strong> <?= DB_HOST ?><br>
            <strong>Database:</strong> <?= DB_NAME ?><br>
            <strong>User:</strong> <?= DB_USER ?><br>
            <strong>Password:</strong> <?= str_repeat('*', strlen(DB_PASS)) ?>
        </div>

        <h2>🔌 Teste de Conexão</h2>
        <?php
        try {
            // Testar conexão
            $testConn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            echo '<div class="success">✅ <strong>Conexão bem-sucedida!</strong></div>';
            
            // Testar uma query simples
            $stmt = $testConn->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            echo '<div class="info"><strong>MySQL Version:</strong> ' . $result['version'] . '</div>';
            
            // Verificar se as tabelas existem
            echo '<h3>📊 Verificação das Tabelas</h3>';
            $tables = $testConn->query("SHOW TABLES")->fetchAll();
            
            if (count($tables) > 0) {
                echo '<div class="success">Encontradas ' . count($tables) . ' tabelas:</div>';
                echo '<pre>';
                foreach ($tables as $table) {
                    $tableName = array_values($table)[0];
                    
                    // Contar registros se for a tabela de imóveis
                    if ($tableName === 'imoveis') {
                        $count = $testConn->query("SELECT COUNT(*) as total FROM imoveis")->fetch();
                        echo "📋 {$tableName} ({$count['total']} registros)\n";
                    } else {
                        echo "📋 {$tableName}\n";
                    }
                }
                echo '</pre>';
            } else {
                echo '<div class="error">⚠️ Nenhuma tabela encontrada. Você precisa importar o banco de dados.</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">❌ <strong>Erro na conexão:</strong><br>' . $e->getMessage() . '</div>';
            
            echo '<h3>💡 Possíveis soluções:</h3>';
            echo '<ul>';
            echo '<li>Verifique se as credenciais do banco estão corretas</li>';
            echo '<li>Confirme se o banco de dados foi criado</li>';
            echo '<li>Verifique se o usuário tem permissões no banco</li>';
            echo '<li>Confirme se o servidor MySQL está rodando</li>';
            echo '</ul>';
        }
        ?>

        <h2>📁 Verificação de Arquivos</h2>
        <?php
        $criticalFiles = [
            'private/includes/db.php',
            'private/includes/functions.php',
            'private/includes/header.php',
            'private/includes/footer.php',
            'public/assets/css/style.css',
            'public/uploads/'
        ];
        
        echo '<div class="config">';
        foreach ($criticalFiles as $file) {
            $exists = file_exists($file);
            $icon = $exists ? '✅' : '❌';
            $status = $exists ? 'OK' : 'FALTANDO';
            echo "{$icon} {$file} - {$status}<br>";
        }
        echo '</div>';
        ?>

        <h2>🔒 Verificação de Permissões</h2>
        <?php
        $uploadDir = 'public/uploads/';
        if (is_dir($uploadDir)) {
            if (is_writable($uploadDir)) {
                echo '<div class="success">✅ Pasta uploads/ tem permissão de escrita</div>';
            } else {
                echo '<div class="error">❌ Pasta uploads/ NÃO tem permissão de escrita</div>';
                echo '<div class="info">Execute: chmod 755 public/uploads/</div>';
            }
        } else {
            echo '<div class="error">❌ Pasta uploads/ não existe</div>';
        }
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <small><strong>⚠️ IMPORTANTE:</strong> Remova este arquivo após verificar que tudo está funcionando!</small>
        </div>
    </div>
</body>
</html>