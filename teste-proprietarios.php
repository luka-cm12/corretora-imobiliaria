<?php
// Teste rápido de cadastro e listagem
require_once(__DIR__ . '/private/includes/db.php');
require_once(__DIR__ . '/private/includes/auth.php');

// Pular autenticação para teste
// require_login();

echo "<h1>🧪 Teste Rápido - Proprietários</h1>";

try {
    // 1. Verificar conexão
    echo "<h2>1️⃣ Teste de Conexão</h2>";
    $test = db_query("SELECT 1 as teste");
    echo "<p style='color: green;'>✅ Conexão funcionando</p>";
    
    // 2. Verificar estrutura
    echo "<h2>2️⃣ Estrutura da Tabela</h2>";
    $colunas = db_query("DESCRIBE proprietarios");
    echo "<p>Colunas: ";
    foreach ($colunas as $col) {
        echo "<code>{$col['Field']}</code> ";
    }
    echo "</p>";
    
    // 3. Contar proprietários
    echo "<h2>3️⃣ Contagem</h2>";
    $count = db_query("SELECT COUNT(*) as total FROM proprietarios")[0];
    echo "<p>Total: <strong>{$count['total']}</strong> proprietários</p>";
    
    // 4. Listar alguns
    if ($count['total'] > 0) {
        echo "<h2>4️⃣ Últimos Proprietários</h2>";
        $props = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Telefone</th><th>Email</th></tr>";
        foreach ($props as $p) {
            echo "<tr>";
            echo "<td>{$p['id_proprietario']}</td>";
            echo "<td>" . htmlspecialchars($p['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($p['cpf'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($p['telefone'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($p['email'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Teste de inserção (se pedido)
    if (isset($_GET['testar_insercao'])) {
        echo "<h2>5️⃣ Teste de Inserção</h2>";
        
        $nome = 'Teste ' . date('H:i:s');
        $result = db_query("INSERT INTO proprietarios (nome, cpf, telefone) VALUES (?, ?, ?)", 
                          [$nome, '12345678901', '(11) 99999-9999']);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Inserção OK - ID: {$result}</p>";
            
            // Verificar
            $novo = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$result]);
            if (!empty($novo)) {
                echo "<p>✅ Encontrado: " . htmlspecialchars($novo[0]['nome']) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Falha na inserção</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Links de teste:</strong></p>";
echo "<p><a href='?testar_insercao=1'>🧪 Testar Inserção</a></p>";
echo "<p><a href='corrigir-banco-emergencial.php'>🔧 Corrigir Banco</a></p>";
echo "<p><a href='private/imoveis/proprietarios-listar.php'>📋 Ver Listagem</a></p>";
echo "<p><a href='private/imoveis/proprietario-cadastrar.php'>➕ Cadastrar</a></p>";
?>