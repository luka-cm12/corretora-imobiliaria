<?php
// Teste completo do sistema de cadastro de proprietários
// Testa tanto CPF quanto CNPJ

require_once 'private/config/config.php';

$errors = [];
$success = [];
$debug_info = [];

function limparDocumento($doc) {
  return preg_replace('/\D/', '', $doc);
}

try {
  $debug_info[] = "🔍 Conectando ao banco de dados...";
  $db = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $success[] = "✅ Conexão com banco estabelecida";
  
  // Verifica estrutura da tabela
  $debug_info[] = "🔍 Verificando estrutura da tabela proprietarios...";
  $stmt = $db->prepare("SHOW COLUMNS FROM proprietarios");
  $stmt->execute();
  $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
  
  $tem_tipo_documento = in_array('tipo_documento', $columns);
  $tem_cnpj = in_array('cnpj', $columns);
  
  $debug_info[] = "• Coluna 'tipo_documento': " . ($tem_tipo_documento ? '✅ Existe' : '❌ Não existe');
  $debug_info[] = "• Coluna 'cnpj': " . ($tem_cnpj ? '✅ Existe' : '❌ Não existe');
  
  if ($tem_tipo_documento && $tem_cnpj) {
    $success[] = "✅ Estrutura nova (com colunas separadas) está disponível";
  } else {
    $debug_info[] = "⚠️ Estrutura antiga detectada - usando coluna 'cpf' unificada";
  }
  
  // Teste 1: Cadastro com CPF
  $debug_info[] = "\n🧪 TESTE 1: Cadastrando proprietário com CPF...";
  
  $cpf_teste = '12345678901';
  $nome_cpf = 'Teste CPF ' . date('H:i:s');
  
  if ($tem_tipo_documento && $tem_cnpj) {
    // Nova estrutura
    $sql = "INSERT INTO proprietarios (nome, cpf, tipo_documento, email, telefone, endereco, data_cadastro) 
            VALUES (?, ?, 'cpf', ?, ?, ?, NOW())";
    $params = [$nome_cpf, $cpf_teste, 'teste@cpf.com', '11999999999', 'Endereço Teste CPF'];
  } else {
    // Estrutura antiga
    $sql = "INSERT INTO proprietarios (nome, cpf, email, telefone, endereco, data_cadastro) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $params = [$nome_cpf, $cpf_teste, 'teste@cpf.com', '11999999999', 'Endereço Teste CPF'];
  }
  
  $stmt = $db->prepare($sql);
  $result_cpf = $stmt->execute($params);
  $id_cpf = $db->lastInsertId();
  
  if ($result_cpf && $id_cpf) {
    $success[] = "✅ CPF cadastrado com sucesso! ID: {$id_cpf}";
  } else {
    $errors[] = "❌ Falha no cadastro do CPF";
  }
  
  // Teste 2: Cadastro com CNPJ
  $debug_info[] = "\n🧪 TESTE 2: Cadastrando proprietário com CNPJ...";
  
  $cnpj_teste = '12345678000195';
  $nome_cnpj = 'Teste CNPJ ' . date('H:i:s');
  
  if ($tem_tipo_documento && $tem_cnpj) {
    // Nova estrutura
    $sql = "INSERT INTO proprietarios (nome, tipo_documento, cnpj, email, telefone, endereco, data_cadastro) 
            VALUES (?, 'cnpj', ?, ?, ?, ?, NOW())";
    $params = [$nome_cnpj, $cnpj_teste, 'teste@cnpj.com', '11888888888', 'Endereço Teste CNPJ'];
  } else {
    // Estrutura antiga - CNPJ vai para coluna cpf
    $sql = "INSERT INTO proprietarios (nome, cpf, email, telefone, endereco, data_cadastro) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $params = [$nome_cnpj, $cnpj_teste, 'teste@cnpj.com', '11888888888', 'Endereço Teste CNPJ'];
  }
  
  $stmt = $db->prepare($sql);
  $result_cnpj = $stmt->execute($params);
  $id_cnpj = $db->lastInsertId();
  
  if ($result_cnpj && $id_cnpj) {
    $success[] = "✅ CNPJ cadastrado com sucesso! ID: {$id_cnpj}";
  } else {
    $errors[] = "❌ Falha no cadastro do CNPJ";
  }
  
  // Teste 3: Verificar se os dados foram salvos corretamente
  $debug_info[] = "\n🔍 TESTE 3: Verificando dados salvos...";
  
  if ($tem_tipo_documento && $tem_cnpj) {
    $stmt = $db->prepare("SELECT id, nome, tipo_documento, cpf, cnpj FROM proprietarios WHERE id IN (?, ?)");
    $stmt->execute([$id_cpf, $id_cnpj]);
  } else {
    $stmt = $db->prepare("SELECT id, nome, cpf FROM proprietarios WHERE id IN (?, ?)");
    $stmt->execute([$id_cpf, $id_cnpj]);
  }
  
  $proprietarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  foreach ($proprietarios as $prop) {
    if ($tem_tipo_documento && $tem_cnpj) {
      $debug_info[] = "• ID {$prop['id']}: {$prop['nome']} - Tipo: {$prop['tipo_documento']} - CPF: {$prop['cpf']} - CNPJ: {$prop['cnpj']}";
    } else {
      $debug_info[] = "• ID {$prop['id']}: {$prop['nome']} - CPF/CNPJ: {$prop['cpf']}";
    }
  }
  
  $success[] = "✅ Verificação dos dados concluída";
  
} catch (Exception $e) {
  $errors[] = "❌ Erro durante o teste: " . $e->getMessage();
  $debug_info[] = "STACK TRACE: " . $e->getTraceAsString();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Teste Completo - Cadastro de Proprietários</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; color: #155724; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .error { background: #f8d7da; color: #721c24; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .debug { background: #f8f9fa; color: #495057; padding: 10px; margin: 5px 0; border-radius: 4px; font-family: monospace; white-space: pre-line; }
    .header { background: #007bff; color: white; padding: 15px; margin: -20px -20px 20px -20px; }
    .actions { margin-top: 20px; }
    .btn { background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 10px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>🧪 Teste Completo do Sistema de Cadastro</h1>
    <p>Testando cadastro de proprietários com CPF e CNPJ</p>
  </div>

  <?php if (!empty($success)): ?>
    <h2>✅ Sucessos</h2>
    <?php foreach ($success as $msg): ?>
      <div class="success"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <h2>❌ Erros</h2>
    <?php foreach ($errors as $error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <h2>🔍 Log de Debug</h2>
  <?php foreach ($debug_info as $info): ?>
    <div class="debug"><?= htmlspecialchars($info) ?></div>
  <?php endforeach; ?>

  <div class="actions">
    <a href="private/imoveis/proprietario-cadastrar.php" class="btn">📝 Cadastrar Proprietário</a>
    <a href="private/imoveis/proprietarios-listar.php" class="btn">📋 Listar Proprietários</a>
    <a href="executar-script-cpf-cnpj.php" class="btn">🔧 Executar Setup Database</a>
  </div>
</body>
</html>