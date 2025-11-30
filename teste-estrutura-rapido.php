<?php
require_once 'private/config/config.php';

echo "<h1>Teste de Estrutura do Banco</h1>";

try {
  echo "<h2>Conexão</h2>";
  global $conn;
  echo "✅ Conexão estabelecida<br>";
  
  echo "<h2>Estrutura da Tabela Proprietarios</h2>";
  $stmt = $conn->prepare("SHOW COLUMNS FROM proprietarios");
  $stmt->execute();
  $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo "<table border='1' style='border-collapse: collapse;'>";
  echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
  
  foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>{$col['Field']}</td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "<td>{$col['Extra']}</td>";
    echo "</tr>";
  }
  echo "</table>";
  
  echo "<h2>Teste de Cadastro</h2>";
  
  // Teste CPF
  $nome_teste = 'Teste CPF ' . date('H:i:s');
  $cpf_teste = '12345678901';
  
  $sql = "INSERT INTO proprietarios (nome, cpf) VALUES (?, ?)";
  $stmt = $conn->prepare($sql);
  $result = $stmt->execute([$nome_teste, $cpf_teste]);
  $id_cpf = $conn->lastInsertId();
  
  if ($result) {
    echo "✅ CPF cadastrado com sucesso! ID: {$id_cpf}<br>";
  } else {
    echo "❌ Erro ao cadastrar CPF<br>";
  }
  
  // Teste CNPJ (se existir coluna)
  $columns_names = array_column($columns, 'Field');
  if (in_array('cnpj', $columns_names)) {
    $nome_teste2 = 'Teste CNPJ ' . date('H:i:s');
    $cnpj_teste = '12345678000195';
    
    $sql = "INSERT INTO proprietarios (nome, cnpj, tipo_documento) VALUES (?, ?, 'cnpj')";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$nome_teste2, $cnpj_teste]);
    $id_cnpj = $conn->lastInsertId();
    
    if ($result) {
      echo "✅ CNPJ cadastrado com sucesso! ID: {$id_cnpj}<br>";
    } else {
      echo "❌ Erro ao cadastrar CNPJ<br>";
    }
  } else {
    echo "⚠️ Coluna CNPJ não existe - usando CPF para ambos<br>";
    
    $nome_teste2 = 'Teste CNPJ via CPF ' . date('H:i:s');
    $cnpj_teste = '12345678000195';
    
    $sql = "INSERT INTO proprietarios (nome, cpf) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$nome_teste2, $cnpj_teste]);
    $id_cnpj = $conn->lastInsertId();
    
    if ($result) {
      echo "✅ CNPJ (via CPF) cadastrado com sucesso! ID: {$id_cnpj}<br>";
    } else {
      echo "❌ Erro ao cadastrar CNPJ via CPF<br>";
    }
  }
  
  echo "<h2>Registros Criados</h2>";
  $proprietarios = $conn->query("SELECT * FROM proprietarios WHERE id_proprietario >= {$id_cpf} ORDER BY id_proprietario DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
  
  echo "<table border='1' style='border-collapse: collapse;'>";
  echo "<tr><th>ID</th><th>Nome</th><th>CPF</th>";
  if (in_array('cnpj', $columns_names)) echo "<th>CNPJ</th><th>Tipo</th>";
  echo "<th>Data</th></tr>";
  
  foreach ($proprietarios as $p) {
    echo "<tr>";
    echo "<td>{$p['id_proprietario']}</td>";
    echo "<td>{$p['nome']}</td>";
    echo "<td>{$p['cpf']}</td>";
    if (in_array('cnpj', $columns_names)) {
      echo "<td>" . ($p['cnpj'] ?? '') . "</td>";
      echo "<td>" . ($p['tipo_documento'] ?? '') . "</td>";
    }
    echo "<td>" . ($p['data_cadastro'] ?? '') . "</td>";
    echo "</tr>";
  }
  echo "</table>";
  
} catch (Exception $e) {
  echo "❌ Erro: " . $e->getMessage();
}

echo "<p><a href='private/imoveis/proprietario-cadastrar.php'>📝 Cadastrar Proprietário</a></p>";
echo "<p><a href='private/imoveis/proprietarios-listar.php'>📋 Listar Proprietários</a></p>";
?>