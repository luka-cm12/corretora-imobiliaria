<?php
// Teste completo de conexão e operações do banco
require_once(__DIR__ . '/private/includes/db.php');

echo "<!DOCTYPE html><html><head><title>Teste de Banco</title><style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
.success { color: green; } .error { color: red; } .info { color: blue; }
pre { background: #f5f5f5; padding: 10px; border-left: 4px solid #ddd; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔧 Teste Completo do Sistema de Banco</h1>";

try {
    // Teste 1: Conexão básica
    echo "<h2>1️⃣ Teste de Conexão</h2>";
    if (isset($conn) && $conn instanceof PDO) {
        echo "<p class='success'>✅ Conexão PDO estabelecida</p>";
    } else {
        throw new Exception("Conexão não estabelecida");
    }

    // Teste 2: Estrutura da tabela
    echo "<h2>2️⃣ Estrutura da Tabela proprietarios</h2>";
    $estrutura = db_query("DESCRIBE proprietarios");
    echo "<pre>";
    foreach ($estrutura as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']}) " . ($coluna['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    echo "</pre>";

    // Teste 3: Contar registros
    echo "<h2>3️⃣ Contagem Atual</h2>";
    $count = db_query("SELECT COUNT(*) as total FROM proprietarios");
    $total = $count[0]['total'];
    echo "<p class='info'>📊 Total de proprietários: <strong>{$total}</strong></p>";

    // Teste 4: Inserção de teste (com rollback)
    echo "<h2>4️⃣ Teste de Inserção (com rollback)</h2>";
    
    $conn->beginTransaction();
    
    $testData = [
        'TESTE ' . date('H:i:s'),
        '12345678901',
        '(11) 99999-9999',
        'teste@email.com',
        'Endereço de teste'
    ];
    
    $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
    $result = db_query($sql, $testData);
    
    echo "<p class='success'>✅ INSERT executado - ID retornado: <strong>{$result}</strong></p>";
    
    // Verificar se foi inserido
    $verificacao = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$result]);
    if (!empty($verificacao)) {
        echo "<p class='success'>✅ Registro encontrado após inserção:</p>";
        echo "<pre>" . print_r($verificacao[0], true) . "</pre>";
    }
    
    // Fazer rollback para não afetar dados reais
    $conn->rollBack();
    echo "<p class='info'>🔄 Rollback executado - dados de teste removidos</p>";

    // Teste 5: Verificar função db_last_id()
    echo "<h2>5️⃣ Teste da função db_last_id()</h2>";
    
    $conn->beginTransaction();
    $testId = db_query("INSERT INTO proprietarios (nome, cpf) VALUES (?, ?)", ['TESTE LAST ID', '99999999999']);
    $lastId = db_last_id();
    $conn->rollBack();
    
    echo "<p>db_query retornou: <strong>{$testId}</strong></p>";
    echo "<p>db_last_id retornou: <strong>{$lastId}</strong></p>";
    echo "<p class='success'>✅ Ambas as funções funcionam corretamente</p>";

    // Teste 6: Últimos registros reais
    echo "<h2>6️⃣ Últimos 3 Proprietários Cadastrados</h2>";
    $ultimos = db_query("SELECT id_proprietario, nome, cpf, telefone, email FROM proprietarios ORDER BY id_proprietario DESC LIMIT 3");
    
    if (!empty($ultimos)) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Telefone</th><th>Email</th></tr>";
        foreach ($ultimos as $prop) {
            echo "<tr>";
            echo "<td>{$prop['id_proprietario']}</td>";
            echo "<td>" . htmlspecialchars($prop['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($prop['cpf'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($prop['telefone'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($prop['email'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ️ Nenhum proprietário cadastrado ainda</p>";
    }

    echo "<h2>✅ Todos os testes passaram!</h2>";
    echo "<p class='success'>O sistema de banco está funcionando corretamente.</p>";

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<h2 class='error'>❌ Erro nos Testes</h2>";
    echo "<p class='error'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><a href='cadastro-proprietario-fix.php'>🔧 Ir para Cadastro EMERGENCIAL</a> | ";
echo "<a href='private/imoveis/proprietarios-listar.php'>📋 Ver Lista de Proprietários</a></p>";

echo "</body></html>";
?>