<?php
// Teste para debug dos problemas de edição e exclusão de proprietários
require_once(__DIR__ . '/private/includes/db.php');

echo "<h2>🔧 Teste de Funcionalidades - Proprietários</h2>";

try {
    echo "<h3>✅ Testando conexão com banco...</h3>";
    $test_query = db_query("SELECT COUNT(*) as total FROM proprietarios");
    echo "✅ Conexão OK! Total de proprietários: " . $test_query[0]['total'] . "<br><br>";
    
    echo "<h3>📋 Estrutura da tabela proprietarios:</h3>";
    $structure = db_query("DESCRIBE proprietarios");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($structure as $field) {
        echo "<tr>";
        echo "<td>{$field['Field']}</td>";
        echo "<td>{$field['Type']}</td>";
        echo "<td>{$field['Null']}</td>";
        echo "<td>{$field['Key']}</td>";
        echo "<td>{$field['Default']}</td>";
        echo "<td>{$field['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<h3>👤 Proprietários existentes:</h3>";
    $proprietarios = db_query("SELECT * FROM proprietarios LIMIT 5");
    if (empty($proprietarios)) {
        echo "❌ Nenhum proprietário encontrado!<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Telefone</th><th>Email</th></tr>";
        foreach ($proprietarios as $prop) {
            echo "<tr>";
            echo "<td>{$prop['id_proprietario']}</td>";
            echo "<td>{$prop['nome']}</td>";
            echo "<td>{$prop['cpf']}</td>";
            echo "<td>{$prop['telefone']}</td>";
            echo "<td>{$prop['email']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    echo "<h3>🏠 Verificando relação com imóveis:</h3>";
    $imoveis_rel = db_query("
        SELECT p.id_proprietario, p.nome, COUNT(i.id_imovel) as total_imoveis 
        FROM proprietarios p 
        LEFT JOIN imoveis i ON p.id_proprietario = i.id_proprietario 
        GROUP BY p.id_proprietario 
        LIMIT 10
    ");
    
    if (!empty($imoveis_rel)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID Proprietário</th><th>Nome</th><th>Total Imóveis</th></tr>";
        foreach ($imoveis_rel as $rel) {
            echo "<tr>";
            echo "<td>{$rel['id_proprietario']}</td>";
            echo "<td>{$rel['nome']}</td>";
            echo "<td>{$rel['total_imoveis']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    echo "<h3>🔑 Testando sessão PHP:</h3>";
    session_start();
    if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado']) {
        echo "✅ Usuário admin logado<br>";
        echo "✅ ID Admin: " . ($_SESSION['admin_id'] ?? 'N/A') . "<br>";
        echo "✅ Nome Admin: " . ($_SESSION['admin_nome'] ?? 'N/A') . "<br>";
    } else {
        echo "❌ Usuário não está logado como admin<br>";
    }
    
    if (isset($_SESSION['csrf_token'])) {
        echo "✅ CSRF Token presente: " . substr($_SESSION['csrf_token'], 0, 10) . "...<br>";
    } else {
        echo "❌ CSRF Token não encontrado<br>";
    }
    echo "<br>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ ERRO:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre><br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre><br>";
}

echo "<h3>📝 Instruções:</h3>";
echo "<ol>";
echo "<li>Se houver erros de conexão, verifique o arquivo config.php</li>";
echo "<li>Se proprietários não aparecem, verifique se existem registros</li>";
echo "<li>Se não está logado, faça login na área administrativa</li>";
echo "<li>Se CSRF token não existe, será criado automaticamente</li>";
echo "</ol>";
echo "<br><a href='private/imoveis/proprietarios-listar.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 Ir para Lista de Proprietários</a>";
?>