<?php
// Verificação rápida da estrutura da tabela e dados
require_once(__DIR__ . '/private/includes/db.php');

echo "<h2>🔍 Debug: Estrutura da Tabela e Dados</h2>";

try {
    // 1. Verificar estrutura da tabela
    echo "<h3>1. Estrutura da Tabela proprietarios:</h3>";
    $estrutura = db_query("SHOW COLUMNS FROM proprietarios");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($estrutura as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 2. Contar proprietários
    echo "<h3>2. Contagem de Proprietários:</h3>";
    $count = db_query("SELECT COUNT(*) as total FROM proprietarios")[0];
    echo "<p>Total de proprietários: <strong>{$count['total']}</strong></p>";

    // 3. Mostrar alguns registros
    if ($count['total'] > 0) {
        echo "<h3>3. Últimos 5 Proprietários:</h3>";
        $props = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Tipo Doc</th><th>CPF</th><th>CNPJ</th><th>Email</th></tr>";
        foreach ($props as $p) {
            echo "<tr>";
            echo "<td>{$p['id_proprietario']}</td>";
            echo "<td>" . htmlspecialchars($p['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($p['tipo_documento'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($p['cpf'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($p['cnpj'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($p['email'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 4. Testar inserção simples
    echo "<h3>4. Teste de Inserção:</h3>";
    
    // Tentar inserir um CPF de teste
    $test_result = db_query(
        "INSERT INTO proprietarios (nome, tipo_documento, cpf) VALUES (?, ?, ?)",
        ['TESTE INSERÇÃO CPF', 'cpf', '12345678901']
    );
    
    if ($test_result) {
        echo "<p style='color: green;'>✅ Inserção CPF funcionou - ID: {$test_result}</p>";
        
        // Remover o teste
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$test_result]);
        echo "<p>🗑️ Registro de teste removido</p>";
    } else {
        echo "<p style='color: red;'>❌ Falha na inserção CPF</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='private/imoveis/proprietarios-listar.php'>📋 Ir para Listagem</a> | ";
echo "<a href='cadastro-proprietario-fix.php'>➕ Cadastro EMERGENCIAL</a></p>";
?>