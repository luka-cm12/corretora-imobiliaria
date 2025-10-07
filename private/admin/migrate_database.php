<?php
/**
 * Script de migração do banco de dados
 * Adiciona colunas que podem estar faltando em instalações antigas
 */

require_once(__DIR__ . '/../config/config.php');

// Array para armazenar mensagens
$mensagens = [];
$erros = [];

try {
    // Verifica se a coluna atualizado_em existe na tabela usuarios
    $stmt = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'atualizado_em'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Adiciona a coluna atualizado_em se ela não existir
        $conn->exec("ALTER TABLE usuarios ADD COLUMN atualizado_em TIMESTAMP NULL DEFAULT NULL AFTER criado_em");
        $mensagens[] = "✓ Coluna 'atualizado_em' adicionada à tabela 'usuarios'";
    } else {
        $mensagens[] = "✓ Coluna 'atualizado_em' já existe na tabela 'usuarios'";
    }
    
    // Verifica se a coluna ultimo_login existe na tabela usuarios
    $stmt = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'ultimo_login'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Adiciona a coluna ultimo_login se ela não existir
        $conn->exec("ALTER TABLE usuarios ADD COLUMN ultimo_login TIMESTAMP NULL DEFAULT NULL AFTER atualizado_em");
        $mensagens[] = "✓ Coluna 'ultimo_login' adicionada à tabela 'usuarios'";
    } else {
        $mensagens[] = "✓ Coluna 'ultimo_login' já existe na tabela 'usuarios'";
    }
    
} catch (PDOException $e) {
    $erros[] = "✗ Erro ao executar migração: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migração do Banco de Dados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .success {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Migração do Banco de Dados</h1>
        
        <?php if (!empty($mensagens)): ?>
            <div class="success">
                <h3>Migrações executadas com sucesso:</h3>
                <ul>
                    <?php foreach ($mensagens as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erros)): ?>
            <div class="error">
                <h3>Erros encontrados:</h3>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <p>
            <a href="login.php" class="btn">Voltar para Login</a>
            <a href="dashboard.php" class="btn">Ir para Dashboard</a>
        </p>
        
        <hr style="margin-top: 30px;">
        <p style="color: #666; font-size: 14px;">
            <strong>Nota:</strong> Este script verifica e adiciona colunas que podem estar faltando 
            no banco de dados. É seguro executá-lo várias vezes.
        </p>
    </div>
</body>
</html>
