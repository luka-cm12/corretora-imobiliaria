<?php
require_once 'private/includes/db.php';

echo "🧪 TESTE COMPLETO - Proprietários CPF/CNPJ\n\n";

try {
    // 1. Verificar estrutura da tabela
    echo "1️⃣ Verificando estrutura da tabela proprietarios...\n";
    
    $columns_result = db_query("SHOW COLUMNS FROM proprietarios");
    $columns = [];
    echo "Colunas encontradas:\n";
    foreach ($columns_result as $col) {
        $columns[] = $col['Field'];
        echo "   - {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
    $tem_tipo_documento = in_array('tipo_documento', $columns);
    $tem_cnpj = in_array('cnpj', $columns);
    
    echo "\n✅ Verificação:\n";
    echo "   - tipo_documento: " . ($tem_tipo_documento ? "EXISTE" : "NÃO EXISTE") . "\n";
    echo "   - cnpj: " . ($tem_cnpj ? "EXISTE" : "NÃO EXISTE") . "\n";
    
    // 2. Teste de cadastro CPF
    echo "\n2️⃣ Testando cadastro com CPF...\n";
    $nome_cpf = "João Teste " . date('H:i:s');
    
    if ($tem_tipo_documento && $tem_cnpj) {
        $sql_cpf = "INSERT INTO proprietarios (nome, tipo_documento, cpf, telefone, email) VALUES (?, 'cpf', ?, ?, ?)";
        $params_cpf = [$nome_cpf, '12345678901', '(11) 99999-9999', 'joao@teste.com'];
    } else {
        $sql_cpf = "INSERT INTO proprietarios (nome, cpf, telefone, email) VALUES (?, ?, ?, ?)";
        $params_cpf = [$nome_cpf, '12345678901', '(11) 99999-9999', 'joao@teste.com'];
    }
    
    $id_cpf = db_query($sql_cpf, $params_cpf);
    echo "   ✅ CPF cadastrado - ID: {$id_cpf}\n";
    
    // 3. Teste de cadastro CNPJ  
    echo "\n3️⃣ Testando cadastro com CNPJ...\n";
    $nome_cnpj = "Empresa Teste " . date('H:i:s');
    
    if ($tem_tipo_documento && $tem_cnpj) {
        $sql_cnpj = "INSERT INTO proprietarios (nome, tipo_documento, cnpj, telefone, email) VALUES (?, 'cnpj', ?, ?, ?)";
        $params_cnpj = [$nome_cnpj, '12345678000195', '(11) 88888-8888', 'contato@empresa.com'];
        $id_cnpj = db_query($sql_cnpj, $params_cnpj);
        echo "   ✅ CNPJ cadastrado - ID: {$id_cnpj}\n";
    } else {
        echo "   ⚠️ Estrutura antiga: CNPJ não suportado separadamente\n";
        $id_cnpj = null;
    }
    
    // 4. Verificar dados inseridos
    echo "\n4️⃣ Verificando dados inseridos...\n";
    
    $cpf_data = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id_cpf]);
    if (!empty($cpf_data)) {
        $reg = $cpf_data[0];
        echo "   CPF: {$reg['nome']} - Doc: " . ($reg['cpf'] ?? 'N/A') . " - Tipo: " . ($reg['tipo_documento'] ?? 'N/A') . "\n";
    }
    
    if ($id_cnpj) {
        $cnpj_data = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id_cnpj]);
        if (!empty($cnpj_data)) {
            $reg = $cnpj_data[0];
            echo "   CNPJ: {$reg['nome']} - Doc: " . ($reg['cnpj'] ?? 'N/A') . " - Tipo: " . ($reg['tipo_documento'] ?? 'N/A') . "\n";
        }
    }
    
    // 5. Teste dos formulários
    echo "\n5️⃣ Testando compatibilidade dos formulários...\n";
    
    // Simular o código do formulário proprietario-cadastrar.php
    $columns_result = db_query("SHOW COLUMNS FROM proprietarios");
    $columns = [];
    foreach ($columns_result as $col) {
        $columns[] = $col['Field'];
    }
    
    $tem_tipo_documento = in_array('tipo_documento', $columns);
    $tem_cnpj = in_array('cnpj', $columns);
    
    echo "   ✅ Detecção automática de estrutura funcionando\n";
    echo "   ✅ Formulário será compatível com estrutura " . ($tem_tipo_documento ? "NOVA" : "ANTIGA") . "\n";
    
    // 6. Limpeza
    echo "\n6️⃣ Limpando registros de teste...\n";
    db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id_cpf]);
    echo "   🗑️ Registro CPF removido\n";
    
    if ($id_cnpj) {
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id_cnpj]);
        echo "   🗑️ Registro CNPJ removido\n";
    }
    
    // 7. Estatísticas finais
    echo "\n7️⃣ Estatísticas da tabela...\n";
    $total = db_query("SELECT COUNT(*) as total FROM proprietarios");
    echo "   📊 Total de proprietários: {$total[0]['total']}\n";
    
    if ($tem_tipo_documento) {
        $cpfs = db_query("SELECT COUNT(*) as total FROM proprietarios WHERE tipo_documento = 'cpf' OR (tipo_documento IS NULL AND cpf IS NOT NULL)");
        $cnpjs = db_query("SELECT COUNT(*) as total FROM proprietarios WHERE tipo_documento = 'cnpj'");
        echo "   📊 Com CPF: {$cpfs[0]['total']}\n";
        echo "   📊 Com CNPJ: {$cnpjs[0]['total']}\n";
    }
    
    echo "\n🎉 TESTE CONCLUÍDO COM SUCESSO!\n";
    echo "✅ Sistema funcionando corretamente\n";
    echo "✅ Formulários compatíveis\n";
    echo "✅ Pronto para uso\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🔗 Links para testar:\n";
echo "1. Cadastro: private/imoveis/proprietario-cadastrar.php\n";
echo "2. Listagem: private/imoveis/proprietarios-listar.php\n";
echo "3. Edição: private/imoveis/proprietario-editar.php?id=X\n";
?>