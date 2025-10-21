<?php
require_once 'private/config/config.php';

echo '<h2>🔧 Atualização da Estrutura da Tabela usuarios</h2>';

try {
    // Verificar se a tabela usuarios existe
    $tables = $conn->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    
    if (empty($tables)) {
        echo '<div style="color: red;">❌ Tabela "usuarios" não existe! Execute primeiro o verificar-admin.php</div>';
        exit;
    }
    
    // Verificar estrutura atual
    $columns = $conn->query("DESCRIBE usuarios")->fetchAll();
    $columnNames = array_column($columns, 'Field');
    
    echo '<h3>📋 Estrutura atual da tabela:</h3>';
    echo '<ul>';
    foreach ($columnNames as $col) {
        echo '<li>' . htmlspecialchars($col) . '</li>';
    }
    echo '</ul>';
    
    $needsUpdate = false;
    $updates = [];
    
    // Verificar se precisa adicionar coluna 'nivel'
    if (!in_array('nivel', $columnNames)) {
        $updates[] = "ADD COLUMN nivel ENUM('admin', 'editor') DEFAULT 'admin' AFTER senha";
        $needsUpdate = true;
        echo '<div style="color: orange;">⚠️ Coluna "nivel" não existe - será adicionada</div>';
    } else {
        echo '<div style="color: green;">✅ Coluna "nivel" existe</div>';
    }
    
    // Verificar se precisa adicionar coluna 'status'
    if (!in_array('status', $columnNames)) {
        $updates[] = "ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER nivel";
        $needsUpdate = true;
        echo '<div style="color: orange;">⚠️ Coluna "status" não existe - será adicionada</div>';
    } else {
        echo '<div style="color: green;">✅ Coluna "status" existe</div>';
    }
    
    // Verificar se precisa adicionar timestamps
    if (!in_array('created_at', $columnNames)) {
        $updates[] = "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $needsUpdate = true;
        echo '<div style="color: orange;">⚠️ Coluna "created_at" não existe - será adicionada</div>';
    } else {
        echo '<div style="color: green;">✅ Coluna "created_at" existe</div>';
    }
    
    if (!in_array('updated_at', $columnNames)) {
        $updates[] = "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        $needsUpdate = true;
        echo '<div style="color: orange;">⚠️ Coluna "updated_at" não existe - será adicionada</div>';
    } else {
        echo '<div style="color: green;">✅ Coluna "updated_at" existe</div>';
    }
    
    // Aplicar atualizações se necessário
    if ($needsUpdate) {
        echo '<h3>🔄 Aplicando atualizações...</h3>';
        
        foreach ($updates as $update) {
            try {
                $sql = "ALTER TABLE usuarios " . $update;
                $conn->exec($sql);
                echo '<div style="color: green;">✅ ' . $update . '</div>';
            } catch (Exception $e) {
                echo '<div style="color: red;">❌ Erro ao executar: ' . $update . ' - ' . $e->getMessage() . '</div>';
            }
        }
        
        echo '<h3>📋 Estrutura atualizada:</h3>';
        $newColumns = $conn->query("DESCRIBE usuarios")->fetchAll();
        echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
        echo '<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>';
        foreach ($newColumns as $col) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Default']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
    } else {
        echo '<div style="color: green;">✅ Tabela já está com a estrutura correta!</div>';
    }
    
    // Verificar usuários existentes
    echo '<h3>👥 Verificando usuários...</h3>';
    $users = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch();
    
    if ($users['total'] == 0) {
        echo '<div style="color: orange;">⚠️ Nenhum usuário encontrado. Criando administrador padrão...</div>';
        
        $email = 'admin@corretoraclaudia.com.br';
        $senha = 'admin123';
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel, status) VALUES (?, ?, ?, 'admin', 1)");
        $stmt->execute(['Administrador', $email, $senhaHash]);
        
        echo '<div style="color: green;">✅ Usuário administrador criado!</div>';
        echo '<div style="background: #f0f8ff; padding: 15px; border: 1px solid #0066cc; margin: 10px 0;">';
        echo '<strong>📋 DADOS DE ACESSO:</strong><br>';
        echo '<strong>Email:</strong> ' . $email . '<br>';
        echo '<strong>Senha:</strong> ' . $senha . '<br>';
        echo '<strong style="color: red;">⚠️ ALTERE A SENHA APÓS O PRIMEIRO LOGIN!</strong>';
        echo '</div>';
    } else {
        echo '<div style="color: green;">✅ Encontrados ' . $users['total'] . ' usuário(s) na base de dados</div>';
    }
    
    echo '<hr>';
    echo '<h3>🔗 Próximos passos:</h3>';
    echo '<p><a href="private/admin/teste-admin.php" target="_blank">🧪 Testar Acesso Admin</a></p>';
    echo '<p><a href="private/admin/login.php" target="_blank">➡️ Ir para Login Admin</a></p>';
    
    echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 15px 0;">';
    echo '<strong>📝 FINALIZAÇÃO:</strong><br>';
    echo '1. Teste o login admin com as credenciais mostradas<br>';
    echo '2. Altere a senha após o primeiro acesso<br>';
    echo '3. <strong style="color: red;">REMOVA este arquivo (atualizar-usuarios.php) por segurança!</strong><br>';
    echo '4. <strong style="color: red;">REMOVA também o verificar-admin.php por segurança!</strong>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div style="color: red;">❌ Erro: ' . $e->getMessage() . '</div>';
    echo '<div style="color: blue;">💡 Dica: Verifique se o banco de dados está conectado corretamente</div>';
}
?>