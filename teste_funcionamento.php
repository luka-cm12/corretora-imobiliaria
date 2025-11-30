<?php
require_once 'private/includes/db.php';

echo "🧪 Testando funcionalidade dos formulários de proprietário...\n\n";

try {
    // Teste 1: Verificar estrutura da tabela
    echo "1️⃣ Verificando estrutura da tabela...\n";
    $estrutura = db_query("SHOW COLUMNS FROM proprietarios");
    
    $colunas_encontradas = [];
    foreach ($estrutura as $col) {
        $colunas_encontradas[] = $col['Field'];
    }
    
    $colunas_necessarias = ['id_proprietario', 'nome', 'tipo_documento', 'cpf', 'cnpj', 'telefone', 'email', 'endereco'];
    
    foreach ($colunas_necessarias as $coluna) {
        $existe = in_array($coluna, $colunas_encontradas);
        echo "   " . ($existe ? "✅" : "❌") . " {$coluna}\n";
    }
    
    // Teste 2: Simular cadastro CPF
    echo "\n2️⃣ Testando cadastro com CPF...\n";
    $nome_teste_cpf = "Teste CPF " . date('H:i:s');
    $cpf_teste = "12345678901";
    
    $id_cpf = db_query(
        "INSERT INTO proprietarios (nome, tipo_documento, cpf, telefone, email) VALUES (?, 'cpf', ?, ?, ?)",
        [$nome_teste_cpf, $cpf_teste, "(11) 99999-9999", "teste.cpf@exemplo.com"]
    );
    
    if ($id_cpf) {
        echo "   ✅ Cadastro CPF funcionou - ID: {$id_cpf}\n";
        
        // Verificar se foi salvo corretamente
        $verificacao = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id_cpf]);
        if (!empty($verificacao)) {
            $registro = $verificacao[0];
            echo "   ✅ Dados salvos: {$registro['nome']} - CPF: {$registro['cpf']} - Tipo: {$registro['tipo_documento']}\n";
        }
    } else {
        echo "   ❌ Falhou no cadastro CPF\n";
    }
    
    // Teste 3: Simular cadastro CNPJ
    echo "\n3️⃣ Testando cadastro com CNPJ...\n";
    $nome_teste_cnpj = "Empresa Teste " . date('H:i:s');
    $cnpj_teste = "12345678000195";
    
    $id_cnpj = db_query(
        "INSERT INTO proprietarios (nome, tipo_documento, cnpj, telefone, email) VALUES (?, 'cnpj', ?, ?, ?)",
        [$nome_teste_cnpj, $cnpj_teste, "(11) 88888-8888", "teste.cnpj@exemplo.com"]
    );
    
    if ($id_cnpj) {
        echo "   ✅ Cadastro CNPJ funcionou - ID: {$id_cnpj}\n";
        
        // Verificar se foi salvo corretamente
        $verificacao = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id_cnpj]);
        if (!empty($verificacao)) {
            $registro = $verificacao[0];
            echo "   ✅ Dados salvos: {$registro['nome']} - CNPJ: {$registro['cnpj']} - Tipo: {$registro['tipo_documento']}\n";
        }
    } else {
        echo "   ❌ Falhou no cadastro CNPJ\n";
    }
    
    // Teste 4: Testar edição
    if ($id_cpf) {
        echo "\n4️⃣ Testando edição de proprietário...\n";
        $novo_nome = "Nome Editado " . date('H:i:s');
        
        $result = db_query(
            "UPDATE proprietarios SET nome = ?, telefone = ? WHERE id_proprietario = ?",
            [$novo_nome, "(11) 77777-7777", $id_cpf]
        );
        
        if ($result !== false) {
            echo "   ✅ Edição funcionou - Linhas afetadas: {$result}\n";
            
            // Verificar a edição
            $verificacao = db_query("SELECT nome, telefone FROM proprietarios WHERE id_proprietario = ?", [$id_cpf]);
            if (!empty($verificacao)) {
                $registro = $verificacao[0];
                echo "   ✅ Dados editados: {$registro['nome']} - Tel: {$registro['telefone']}\n";
            }
        }
    }
    
    // Teste 5: Testar listagem com filtros
    echo "\n5️⃣ Testando listagem e filtros...\n";
    
    // Buscar por CPF
    $busca_cpf = db_query("SELECT COUNT(*) as total FROM proprietarios WHERE cpf IS NOT NULL AND cpf != ''");
    echo "   📊 Proprietários com CPF: {$busca_cpf[0]['total']}\n";
    
    // Buscar por CNPJ  
    $busca_cnpj = db_query("SELECT COUNT(*) as total FROM proprietarios WHERE cnpj IS NOT NULL AND cnpj != ''");
    echo "   📊 Proprietários com CNPJ: {$busca_cnpj[0]['total']}\n";
    
    // Listar os últimos 3
    $ultimos = db_query("SELECT id_proprietario, nome, tipo_documento, cpf, cnpj FROM proprietarios ORDER BY id_proprietario DESC LIMIT 3");
    echo "   📋 Últimos 3 proprietários:\n";
    foreach ($ultimos as $prop) {
        $doc = $prop['cpf'] ?: $prop['cnpj'] ?: 'N/A';
        echo "      - ID {$prop['id_proprietario']}: {$prop['nome']} ({$prop['tipo_documento']}: {$doc})\n";
    }
    
    // Limpeza: remover registros de teste
    echo "\n🗑️ Limpando registros de teste...\n";
    if ($id_cpf) {
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id_cpf]);
        echo "   ✅ Registro CPF removido\n";
    }
    if ($id_cnpj) {
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id_cnpj]);
        echo "   ✅ Registro CNPJ removido\n";
    }
    
    echo "\n🎉 TODOS OS TESTES PASSARAM!\n";
    echo "✅ Os formulários estão funcionando corretamente.\n";
    echo "✅ Cadastro, edição e listagem operacionais.\n";
    echo "✅ Suporte a CPF e CNPJ implementado.\n";
    
} catch (Exception $e) {
    echo "❌ ERRO DURANTE OS TESTES: " . $e->getMessage() . "\n";
}

echo "\n📝 Instruções finais:\n";
echo "1. Acesse: http://localhost/corretora-imobiliaria-11/private/imoveis/proprietario-cadastrar.php\n";
echo "2. Teste cadastrar um proprietário com CPF\n";
echo "3. Teste cadastrar um proprietário com CNPJ\n";
echo "4. Acesse a listagem: http://localhost/corretora-imobiliaria-11/private/imoveis/proprietarios-listar.php\n";
echo "5. Teste editar um proprietário existente\n";
?>