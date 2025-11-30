<?php
// Teste FINAL - SQL Puro
require_once(__DIR__ . '/private/config/config.php');

echo "<h1>🎯 TESTE FINAL - SQL PURO</h1>";

try {
    // Usar a conexão PDO direta do config.php
    global $conn;
    
    echo "<h2>Informações da Conexão:</h2>";
    echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
    echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>User:</strong> " . DB_USER . "</p>";
    
    // 1. Criar proprietário de teste
    $nome = "TESTE_SQL_PURO_" . date('His');
    $cpf = "999" . rand(10000000, 99999999);
    
    echo "<h2>1. Criando proprietário de teste</h2>";
    echo "<p><strong>Nome:</strong> {$nome}</p>";
    echo "<p><strong>CPF:</strong> {$cpf}</p>";
    
    $stmt = $conn->prepare("INSERT INTO proprietarios (nome, cpf, data_cadastro) VALUES (?, ?, NOW())");
    $result = $stmt->execute([$nome, $cpf]);
    
    if (!$result) {
        throw new Exception("Falha na inserção: " . implode(", ", $stmt->errorInfo()));
    }
    
    $insert_id = $conn->lastInsertId();
    echo "<p style='color: green;'>✅ Proprietário criado com ID: <strong>{$insert_id}</strong></p>";
    
    // 2. Verificar se foi inserido
    echo "<h2>2. Verificando inserção</h2>";
    $stmt = $conn->prepare("SELECT * FROM proprietarios WHERE id_proprietario = ?");
    $stmt->execute([$insert_id]);
    $proprietario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proprietario) {
        throw new Exception("Proprietário não encontrado após inserção!");
    }
    
    echo "<p style='color: green;'>✅ Proprietário confirmado no banco:</p>";
    echo "<pre>" . print_r($proprietario, true) . "</pre>";
    
    // 3. Verificar se tem imóveis
    echo "<h2>3. Verificando imóveis</h2>";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?");
    $stmt->execute([$insert_id]);
    $imoveis_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p><strong>Imóveis associados:</strong> {$imoveis_count}</p>";
    
    if ($imoveis_count > 0) {
        echo "<p style='color: red;'>❌ Proprietário tem imóveis - não pode ser excluído</p>";
    } else {
        // 4. TENTAR EXCLUSÃO
        echo "<h2>4. EXECUTANDO EXCLUSÃO</h2>";
        
        echo "<p><strong>SQL:</strong> <code>DELETE FROM proprietarios WHERE id_proprietario = {$insert_id}</code></p>";
        
        $stmt = $conn->prepare("DELETE FROM proprietarios WHERE id_proprietario = ?");
        $result = $stmt->execute([$insert_id]);
        
        echo "<p><strong>Execute result:</strong> " . ($result ? 'TRUE' : 'FALSE') . "</p>";
        
        $rows_affected = $stmt->rowCount();
        echo "<p><strong>Rows affected:</strong> {$rows_affected}</p>";
        
        if ($rows_affected > 0) {
            echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 SUCESSO! EXCLUSÃO FUNCIONOU!</p>";
            
            // Verificar se realmente foi excluído
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM proprietarios WHERE id_proprietario = ?");
            $stmt->execute([$insert_id]);
            $ainda_existe = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($ainda_existe == 0) {
                echo "<p style='color: green; font-weight: bold;'>✅ CONFIRMADO: Proprietário foi removido do banco</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ ERRO: Proprietário ainda existe no banco!</p>";
            }
            
        } else {
            echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ FALHA! NENHUMA LINHA FOI AFETADA</p>";
            
            // Investigar por que não funcionou
            echo "<h3>🔍 Investigando falha...</h3>";
            
            // Verificar se ainda existe
            $stmt = $conn->prepare("SELECT * FROM proprietarios WHERE id_proprietario = ?");
            $stmt->execute([$insert_id]);
            $ainda_existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ainda_existe) {
                echo "<p style='color: orange;'>⚠️ Proprietário ainda existe no banco:</p>";
                echo "<pre>" . print_r($ainda_existe, true) . "</pre>";
            } else {
                echo "<p style='color: red;'>❓ Proprietário não foi encontrado (mas DELETE não afetou linhas?!)</p>";
            }
            
            // Verificar erros PDO
            $error_info = $stmt->errorInfo();
            if ($error_info[0] !== '00000') {
                echo "<p style='color: red;'><strong>Erro PDO:</strong> " . print_r($error_info, true) . "</p>";
            }
        }
    }
    
    // 5. Tentar com um proprietário real
    echo "<hr><h2>5. TESTE COM PROPRIETÁRIO REAL</h2>";
    
    // Buscar proprietário sem imóveis
    $stmt = $conn->prepare("
        SELECT p.* 
        FROM proprietarios p 
        WHERE NOT EXISTS (
            SELECT 1 FROM imoveis WHERE id_proprietario = p.id_proprietario
        ) 
        LIMIT 1
    ");
    $stmt->execute();
    $prop_real = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prop_real) {
        echo "<p style='color: orange;'>⚠️ Nenhum proprietário real sem imóveis encontrado</p>";
    } else {
        echo "<p>👤 Proprietário encontrado: <strong>ID {$prop_real['id_proprietario']} - {$prop_real['nome']}</strong></p>";
        
        if (isset($_POST['excluir_real'])) {
            $id_real = (int)$_POST['excluir_real'];
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
            echo "<h3>🧪 EXCLUINDO PROPRIETÁRIO REAL - ID: {$id_real}</h3>";
            
            $stmt = $conn->prepare("DELETE FROM proprietarios WHERE id_proprietario = ?");
            $result = $stmt->execute([$id_real]);
            $rows_affected = $stmt->rowCount();
            
            echo "<p><strong>Resultado:</strong> " . ($result ? 'TRUE' : 'FALSE') . "</p>";
            echo "<p><strong>Linhas afetadas:</strong> {$rows_affected}</p>";
            
            if ($rows_affected > 0) {
                echo "<p style='color: green; font-weight: bold; font-size: 16px;'>🎉 PROPRIETÁRIO REAL EXCLUÍDO COM SUCESSO!</p>";
                echo "<p style='color: green;'>✅ A funcionalidade de exclusão ESTÁ FUNCIONANDO!</p>";
            } else {
                echo "<p style='color: red; font-weight: bold; font-size: 16px;'>❌ FALHA NA EXCLUSÃO DO PROPRIETÁRIO REAL</p>";
                
                $error_info = $stmt->errorInfo();
                if ($error_info[0] !== '00000') {
                    echo "<p style='color: red;'><strong>Erro:</strong> " . print_r($error_info, true) . "</p>";
                }
            }
            
            echo "</div>";
        } else {
            echo "<form method='POST' style='margin: 20px 0;'>";
            echo "<input type='hidden' name='excluir_real' value='{$prop_real['id_proprietario']}'>";
            echo "<button type='submit' style='background: #dc3545; color: white; padding: 15px 25px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;' ";
            echo "onclick='return confirm(\"🚨 ATENÇÃO!\\n\\nIsso excluirá o proprietário REAL:\\n{$prop_real['nome']}\\n\\nEsta ação NÃO pode ser desfeita!\\n\\nTem CERTEZA ABSOLUTA?\");'>";
            echo "🗑️ EXCLUIR PROPRIETÁRIO REAL (TESTE DEFINITIVO)";
            echo "</button>";
            echo "</form>";
            
            echo "<p style='color: red; font-weight: bold;'>⚠️ CUIDADO: Isso excluirá um proprietário real do sistema!</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ ERRO:</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<details><summary>Stack Trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
    echo "</div>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    h1, h2, h3 { color: #333; }
    h1 { background: #007bff; color: white; padding: 15px; border-radius: 5px; }
    h2 { border-bottom: 2px solid #007bff; padding-bottom: 5px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; border: 1px solid #dee2e6; }
    code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
</style>

<hr>
<p><a href="private/imoveis/proprietarios-listar.php">← Voltar para listagem</a></p>