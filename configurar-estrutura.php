<?php
require_once 'private/config/config.php';

echo "<h1>🔧 Configuração da Estrutura do Banco</h1>";

try {
  global $conn;
  echo "✅ Conectado ao banco<br>";
  
  // Verificar colunas atuais
  $stmt = $conn->query("SHOW COLUMNS FROM proprietarios");
  $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
  
  echo "<h2>Colunas atuais:</h2>";
  echo implode(', ', $columns) . "<br><br>";
  
  $alteracoes = [];
  
  // Adicionar coluna tipo_documento se não existir
  if (!in_array('tipo_documento', $columns)) {
    echo "➕ Adicionando coluna tipo_documento...<br>";
    $conn->exec("ALTER TABLE proprietarios ADD COLUMN tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf' AFTER cpf");
    $alteracoes[] = 'tipo_documento';
  } else {
    echo "✅ Coluna tipo_documento já existe<br>";
  }
  
  // Adicionar coluna cnpj se não existir
  if (!in_array('cnpj', $columns)) {
    echo "➕ Adicionando coluna cnpj...<br>";
    $conn->exec("ALTER TABLE proprietarios ADD COLUMN cnpj VARCHAR(18) NULL AFTER tipo_documento");
    $alteracoes[] = 'cnpj';
  } else {
    echo "✅ Coluna cnpj já existe<br>";
  }
  
  // Adicionar coluna data_cadastro se não existir
  if (!in_array('data_cadastro', $columns)) {
    echo "➕ Adicionando coluna data_cadastro...<br>";
    $conn->exec("ALTER TABLE proprietarios ADD COLUMN data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP");
    $alteracoes[] = 'data_cadastro';
  } else {
    echo "✅ Coluna data_cadastro já existe<br>";
  }
  
  if (count($alteracoes) > 0) {
    echo "<br>🎉 <strong>Alterações aplicadas:</strong> " . implode(', ', $alteracoes) . "<br>";
  } else {
    echo "<br>✅ <strong>Estrutura já está atualizada!</strong><br>";
  }
  
  // Verificar estrutura final
  echo "<h2>Estrutura final:</h2>";
  $stmt = $conn->query("SHOW COLUMNS FROM proprietarios");
  $columns_final = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
  echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
  
  foreach ($columns_final as $col) {
    echo "<tr>";
    echo "<td><strong>{$col['Field']}</strong></td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
  }
  echo "</table>";
  
  echo "<h2>🧪 Teste de Funcionamento</h2>";
  
  // Teste inserção CPF
  $nome_cpf = 'Teste Estrutura CPF ' . date('H:i:s');
  $stmt = $conn->prepare("INSERT INTO proprietarios (nome, cpf, tipo_documento) VALUES (?, ?, 'cpf')");
  $result_cpf = $stmt->execute([$nome_cpf, '11111111111']);
  $id_cpf = $conn->lastInsertId();
  
  if ($result_cpf) {
    echo "✅ CPF testado - ID: {$id_cpf}<br>";
  }
  
  // Teste inserção CNPJ
  $nome_cnpj = 'Teste Estrutura CNPJ ' . date('H:i:s');
  $stmt = $conn->prepare("INSERT INTO proprietarios (nome, cnpj, tipo_documento) VALUES (?, ?, 'cnpj')");
  $result_cnpj = $stmt->execute([$nome_cnpj, '22222222222222']);
  $id_cnpj = $conn->lastInsertId();
  
  if ($result_cnpj) {
    echo "✅ CNPJ testado - ID: {$id_cnpj}<br>";
  }
  
} catch (Exception $e) {
  echo "<div style='background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;'>";
  echo "❌ <strong>Erro:</strong> " . $e->getMessage();
  echo "</div>";
}

echo "<br><hr><br>";
echo "<p><a href='teste-estrutura-rapido.php' style='background: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🔍 Ver Estrutura Atual</a></p>";
echo "<p><a href='private/imoveis/proprietario-cadastrar.php' style='background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>📝 Testar Cadastro</a></p>";
?>