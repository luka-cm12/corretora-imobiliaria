<?php
require_once 'private/config/config.php';

echo '<h2>Verificação da Área Administrativa</h2>';

try {
    // Verificar se a tabela usuarios existe
    $tables = $conn->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    
    if (empty($tables)) {
        echo '<div style="color: red;">❌ Tabela "usuarios" não existe!</div>';
        echo '<h3>Criando tabela usuarios...</h3>';
        
        // Criar tabela de usuários
        $createTable = "
        CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            nivel ENUM('admin', 'editor') DEFAULT 'admin',
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($createTable);
        echo '<div style="color: green;">✅ Tabela "usuarios" criada com sucesso!</div>';
    } else {
        echo '<div style="color: green;">✅ Tabela "usuarios" existe!</div>';
    }
    
    // Verificar estrutura da tabela usuarios
    $columns = $conn->query("DESCRIBE usuarios")->fetchAll();
    $columnNames = array_column($columns, 'Field');
    
    echo '<h3>📋 Estrutura da tabela usuarios:</h3>';
    echo '<ul>';
    foreach ($columnNames as $col) {
        echo '<li>' . htmlspecialchars($col) . '</li>';
    }
    echo '</ul>';
    
    // Verificar se há usuários cadastrados (ajustando consulta conforme colunas existentes)
    $selectFields = 'id, nome, email';
    if (in_array('nivel', $columnNames)) {
        $selectFields .= ', nivel';
    }
    if (in_array('status', $columnNames)) {
        $selectFields .= ', status';
    }
    
    $users = $conn->query("SELECT {$selectFields} FROM usuarios")->fetchAll();
    
    if (empty($users)) {
        echo '<div style="color: orange;">⚠️ Nenhum usuário encontrado!</div>';
        echo '<h3>Criando usuário administrador padrão...</h3>';
        
        // Criar usuário padrão (ajustando campos conforme estrutura da tabela)
        $email = 'admin@corretoraclaudia.com.br';
        $senha = 'admin123'; // Senha padrão - MUDE DEPOIS!
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Construir INSERT conforme colunas disponíveis
        $fields = 'nome, email, senha';
        $values = '?, ?, ?';
        $params = ['Administrador', $email, $senhaHash];
        
        if (in_array('nivel', $columnNames)) {
            $fields .= ', nivel';
            $values .= ', ?';
            $params[] = 'admin';
        }
        
        if (in_array('status', $columnNames)) {
            $fields .= ', status';
            $values .= ', ?';
            $params[] = 1;
        }
        
        $stmt = $conn->prepare("INSERT INTO usuarios ({$fields}) VALUES ({$values})");
        $stmt->execute($params);
        
        echo '<div style="color: green;">✅ Usuário administrador criado!</div>';
        echo '<div style="background: #f0f8ff; padding: 15px; border: 1px solid #0066cc; margin: 10px 0;">';
        echo '<strong>📋 DADOS DE ACESSO:</strong><br>';
        echo '<strong>Email:</strong> ' . $email . '<br>';
        echo '<strong>Senha:</strong> ' . $senha . '<br>';
        echo '<strong style="color: red;">⚠️ ALTERE A SENHA APÓS O PRIMEIRO LOGIN!</strong>';
        echo '</div>';
    } else {
        echo '<div style="color: green;">✅ Usuários encontrados:</div>';
        echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
        echo '<tr><th>ID</th><th>Nome</th><th>Email</th>';
        if (in_array('nivel', $columnNames)) echo '<th>Nível</th>';
        if (in_array('status', $columnNames)) echo '<th>Status</th>';
        echo '</tr>';
        
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . $user['id'] . '</td>';
            echo '<td>' . htmlspecialchars($user['nome']) . '</td>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            
            if (in_array('nivel', $columnNames)) {
                echo '<td>' . htmlspecialchars($user['nivel'] ?? 'N/A') . '</td>';
            }
            
            if (in_array('status', $columnNames)) {
                $status = isset($user['status']) ? ($user['status'] ? 'Ativo' : 'Inativo') : 'N/A';
                echo '<td>' . $status . '</td>';
            }
            
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '<hr>';
    echo '<h3>🔗 Links para testar:</h3>';
    echo '<p><a href="private/admin/teste-admin.php" target="_blank">🧪 Testar Acesso Admin</a></p>';
    echo '<p><a href="private/admin/login.php" target="_blank">➡️ Ir para Login Admin</a></p>';
    echo '<p><a href="private/admin/" target="_blank">➡️ Ir direto para Admin (vai redirecionar para login)</a></p>';
    
    echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 15px 0;">';
    echo '<strong>📝 PRÓXIMOS PASSOS:</strong><br>';
    echo '1. Acesse o link do login admin acima<br>';
    echo '2. Use as credenciais mostradas (se criou usuário novo)<br>';
    echo '3. Altere a senha após o login<br>';
    echo '4. <strong style="color: red;">REMOVA este arquivo (verificar-admin.php) por segurança!</strong>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div style="color: red;">❌ Erro: ' . $e->getMessage() . '</div>';
}
?>