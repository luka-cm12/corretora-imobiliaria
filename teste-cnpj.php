<?php
// Teste específico para CNPJ - verificar se funciona
require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🧪 Teste Específico - CNPJ</h1>";

// 1. Verificar estrutura da tabela
echo "<h2>1️⃣ Estrutura da Tabela</h2>";
try {
    $colunas = db_query("SHOW COLUMNS FROM proprietarios");
    $campos = array_column($colunas, 'Field');
    
    echo "<p>Campos existentes: <code>" . implode(', ', $campos) . "</code></p>";
    
    $tem_tipo_documento = in_array('tipo_documento', $campos);
    $tem_cnpj = in_array('cnpj', $campos);
    
    echo "<p>• tipo_documento: " . ($tem_tipo_documento ? "✅ EXISTE" : "❌ NÃO EXISTE") . "</p>";
    echo "<p>• cnpj: " . ($tem_cnpj ? "✅ EXISTE" : "❌ NÃO EXISTE") . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao verificar estrutura: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Teste de inserção CNPJ
echo "<h2>2️⃣ Teste de Inserção CNPJ</h2>";

if (isset($_POST['testar'])) {
    $nome = $_POST['nome'] ?? 'Teste CNPJ ' . date('H:i:s');
    $cnpj = $_POST['cnpj'] ?? '12.345.678/0001-90';
    
    try {
        if ($tem_tipo_documento && $tem_cnpj) {
            // Estrutura nova
            echo "<p>🔄 Tentando inserção com estrutura NOVA...</p>";
            $sql = "INSERT INTO proprietarios (nome, tipo_documento, cnpj) VALUES (?, ?, ?)";
            $result = db_query($sql, [$nome, 'cnpj', $cnpj]);
            echo "<p style='color: green;'>✅ SUCESSO com estrutura nova! ID: {$result}</p>";
        } else {
            // Estrutura antiga
            echo "<p>🔄 Tentando inserção com estrutura ANTIGA...</p>";
            $sql = "INSERT INTO proprietarios (nome, cpf) VALUES (?, ?)";
            $result = db_query($sql, [$nome, $cnpj]);
            echo "<p style='color: green;'>✅ SUCESSO com estrutura antiga! ID: {$result}</p>";
        }
        
        // Verificar se foi inserido
        $check = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$result]);
        if (!empty($check)) {
            echo "<h3>Dados inseridos:</h3>";
            echo "<pre>" . print_r($check[0], true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ ERRO na inserção: " . $e->getMessage() . "</p>";
        echo "<p><strong>SQL tentado:</strong> " . ($sql ?? 'N/A') . "</p>";
        echo "<p><strong>Parâmetros:</strong> " . json_encode([$nome, ($tem_cnpj ? 'cnpj' : ''), $cnpj]) . "</p>";
    }
}

// 3. Últimos registros
echo "<h2>3️⃣ Últimos Registros</h2>";
try {
    $ultimos = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
    
    if (!empty($ultimos)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Tipo</th><th>CPF</th><th>CNPJ</th></tr>";
        
        foreach ($ultimos as $reg) {
            echo "<tr>";
            echo "<td>{$reg['id_proprietario']}</td>";
            echo "<td>" . htmlspecialchars($reg['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['tipo_documento'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($reg['cpf'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($reg['cnpj'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum registro encontrado.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar registros: " . $e->getMessage() . "</p>";
}
?>

<hr>

<h2>🧪 Testar Inserção CNPJ</h2>
<form method="POST">
    <p>
        <label>Nome:</label><br>
        <input type="text" name="nome" value="Empresa Teste CNPJ" style="width: 300px; padding: 5px;">
    </p>
    <p>
        <label>CNPJ:</label><br>
        <input type="text" name="cnpj" value="12.345.678/0001-90" style="width: 200px; padding: 5px;">
    </p>
    <p>
        <button type="submit" name="testar" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px;">
            🧪 Testar Inserção CNPJ
        </button>
    </p>
</form>

<p>
    <a href="private/imoveis/proprietario-cadastrar.php">📝 Voltar para Cadastro Original</a> |
    <a href="private/imoveis/proprietarios-listar.php">📋 Ver Lista</a>
</p>