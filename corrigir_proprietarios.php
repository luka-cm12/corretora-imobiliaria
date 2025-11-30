<?php
// Corrigir tabela proprietários - Script de correção definitiva
require_once 'private/config/config.php'; // Usar config central em vez de db.php

echo "🔧 Iniciando correção da tabela proprietários...\n\n";

try {
    // 1. Verificar se a tabela existe
    echo "1️⃣ Verificando se a tabela proprietários existe...\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'proprietarios'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "❌ Tabela proprietários NÃO EXISTE. Criando...\n";
        
        $sql_create = "
        CREATE TABLE proprietarios (
            id_proprietario INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL,
            tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf',
            cpf VARCHAR(20) NULL,
            cnpj VARCHAR(20) NULL,
            telefone VARCHAR(20) NULL,
            email VARCHAR(150) NULL,
            endereco TEXT NULL,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cpf (cpf),
            INDEX idx_cnpj (cnpj),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql_create);
        echo "✅ Tabela proprietários criada com sucesso!\n";
    } else {
        echo "✅ Tabela proprietários já existe.\n";
    }
    
    // 2. Verificar estrutura atual
    echo "\n2️⃣ Verificando estrutura da tabela...\n";
    
    $stmt = $conn->prepare("SHOW COLUMNS FROM proprietarios");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $existing_columns = [];
    foreach ($columns as $col) {
        $existing_columns[] = $col['Field'];
        echo "   - {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
    // 3. Adicionar colunas que faltam
    echo "\n3️⃣ Verificando colunas necessárias...\n";
    
    $required_columns = [
        'tipo_documento' => "ALTER TABLE proprietarios ADD COLUMN tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf' AFTER nome",
        'cnpj' => "ALTER TABLE proprietarios ADD COLUMN cnpj VARCHAR(20) NULL AFTER cpf"
    ];
    
    foreach ($required_columns as $column => $sql) {
        if (!in_array($column, $existing_columns)) {
            echo "   Adicionando coluna {$column}...\n";
            try {
                $conn->exec($sql);
                echo "   ✅ Coluna {$column} adicionada.\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "   ⚠️ Coluna {$column} já existe.\n";
                } else {
                    echo "   ❌ Erro ao adicionar {$column}: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "   ✅ Coluna {$column} já existe.\n";
        }
    }
    
    // 4. Teste básico de funcionalidade
    echo "\n4️⃣ Testando funcionalidade básica...\n";
    
    // Teste de inserção
    $nome_teste = "TESTE_CORREÇÃO_" . date('His');
    $stmt = $conn->prepare("INSERT INTO proprietarios (nome, cpf) VALUES (?, ?)");
    $result = $stmt->execute([$nome_teste, '12345678901']);
    
    if ($result) {
        $id_teste = $conn->lastInsertId();
        echo "   ✅ Inserção funcionando - ID: {$id_teste}\n";
        
        // Teste de busca
        $stmt = $conn->prepare("SELECT * FROM proprietarios WHERE id_proprietario = ?");
        $stmt->execute([$id_teste]);
        $found = $stmt->fetch();
        
        if ($found) {
            echo "   ✅ Busca funcionando - Nome: {$found['nome']}\n";
            
            // Limpar teste
            $stmt = $conn->prepare("DELETE FROM proprietarios WHERE id_proprietario = ?");
            $stmt->execute([$id_teste]);
            echo "   🗑️ Registro de teste removido.\n";
        }
    }
    
    // 5. Contar registros existentes
    echo "\n5️⃣ Estatísticas da tabela...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM proprietarios");
    $stmt->execute();
    $total = $stmt->fetch();
    echo "   📊 Total de proprietários: {$total['total']}\n";
    
    if ($total['total'] > 0) {
        $stmt = $conn->prepare("SELECT id_proprietario, nome, cpf, cnpj FROM proprietarios ORDER BY id_proprietario DESC LIMIT 3");
        $stmt->execute();
        $samples = $stmt->fetchAll();
        
        echo "   📋 Últimos registros:\n";
        foreach ($samples as $sample) {
            $doc = $sample['cpf'] ?: $sample['cnpj'] ?: 'N/A';
            echo "      - ID {$sample['id_proprietario']}: {$sample['nome']} (Doc: {$doc})\n";
        }
    }
    
    echo "\n🎉 CORREÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "✅ A tabela proprietários está pronta para uso.\n";
    echo "✅ Os formulários devem funcionar normalmente agora.\n";
    
} catch (Exception $e) {
    echo "❌ ERRO DURANTE A CORREÇÃO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🔗 Próximos passos:\n";
echo "1. Teste o cadastro: private/imoveis/proprietario-cadastrar.php\n";
echo "2. Teste a edição: private/imoveis/proprietario-editar.php\n";
echo "3. Teste a listagem: private/imoveis/proprietarios-listar.php\n";
?>