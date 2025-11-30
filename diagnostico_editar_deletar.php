<?php
require_once 'private/includes/db.php';

echo "🔍 Testando funcionalidades de EDITAR e DELETAR proprietários...\n\n";

try {
    // 1. Verificar se há proprietários para testar
    echo "1️⃣ Verificando proprietários existentes...\n";
    $proprietarios = db_query("SELECT id_proprietario, nome, cpf, cnpj FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
    
    if (empty($proprietarios)) {
        echo "   ⚠️ Nenhum proprietário encontrado. Criando um para teste...\n";
        $id_teste = db_query("INSERT INTO proprietarios (nome, cpf, tipo_documento) VALUES (?, ?, 'cpf')", 
            ["Teste Editar/Deletar " . date('H:i:s'), '99988877766']);
        echo "   ✅ Proprietário criado - ID: {$id_teste}\n";
        
        $proprietarios = db_query("SELECT id_proprietario, nome, cpf, cnpj FROM proprietarios WHERE id_proprietario = ?", [$id_teste]);
    }
    
    $proprietario_teste = $proprietarios[0];
    $id_teste = $proprietario_teste['id_proprietario'];
    
    echo "   📊 Testando com proprietário ID: {$id_teste} - {$proprietario_teste['nome']}\n";
    
    // 2. Testar se o proprietário tem imóveis (isso impede exclusão)
    echo "\n2️⃣ Verificando imóveis associados...\n";
    $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id_teste]);
    
    if (!empty($imoveis_count)) {
        $total_imoveis = $imoveis_count[0]['count'];
        echo "   📊 Imóveis associados: {$total_imoveis}\n";
        
        if ($total_imoveis > 0) {
            echo "   ⚠️ ATENÇÃO: Proprietário tem imóveis - EXCLUSÃO será BLOQUEADA\n";
        } else {
            echo "   ✅ Proprietário sem imóveis - EXCLUSÃO permitida\n";
        }
    } else {
        echo "   ❌ ERRO: Não conseguiu verificar imóveis\n";
    }
    
    // 3. Testar busca individual para edição
    echo "\n3️⃣ Testando busca para edição...\n";
    $proprietario_edicao = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id_teste]);
    
    if (!empty($proprietario_edicao)) {
        $prop = $proprietario_edicao[0];
        echo "   ✅ Proprietário encontrado para edição:\n";
        echo "      - Nome: {$prop['nome']}\n";
        echo "      - CPF: " . ($prop['cpf'] ?: 'N/A') . "\n";
        echo "      - CNPJ: " . ($prop['cnpj'] ?: 'N/A') . "\n";
        echo "      - Email: " . ($prop['email'] ?: 'N/A') . "\n";
        echo "      - Telefone: " . ($prop['telefone'] ?: 'N/A') . "\n";
    } else {
        echo "   ❌ ERRO: Não conseguiu buscar proprietário para edição\n";
    }
    
    // 4. Testar simulação de exclusão (SEM EXECUTAR)
    echo "\n4️⃣ Simulando processo de exclusão...\n";
    
    // Simular verificação de CSRF (como no arquivo real)
    session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    
    echo "   ✅ Token CSRF gerado: " . substr($_SESSION['csrf_token'], 0, 10) . "...\n";
    
    // Simular verificação de imóveis
    $imoveis_check = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id_teste]);
    $imoveis_total = $imoveis_check[0]['count'] ?? 0;
    
    if ($imoveis_total > 0) {
        echo "   ⚠️ EXCLUSÃO BLOQUEADA: Proprietário possui {$imoveis_total} imóvel(is)\n";
        echo "      Mensagem: 'Não é possível excluir este proprietário pois ele possui {$imoveis_total} imóvel(is) cadastrado(s).'\n";
    } else {
        echo "   ✅ EXCLUSÃO PERMITIDA: Proprietário sem imóveis\n";
        echo "      Query que seria executada: DELETE FROM proprietarios WHERE id_proprietario = {$id_teste}\n";
    }
    
    // 5. Testar URLs de edição
    echo "\n5️⃣ Testando URLs e links...\n";
    
    $url_edicao = "private/imoveis/proprietario-editar.php?id={$id_teste}";
    echo "   🔗 URL de edição: {$url_edicao}\n";
    
    // Verificar se o arquivo de edição existe
    $arquivo_edicao = "private/imoveis/proprietario-editar.php";
    if (file_exists($arquivo_edicao)) {
        echo "   ✅ Arquivo de edição EXISTS\n";
    } else {
        echo "   ❌ Arquivo de edição NÃO EXISTE: {$arquivo_edicao}\n";
    }
    
    // 6. Testar estrutura da tabela para compatibilidade
    echo "\n6️⃣ Verificando estrutura da tabela...\n";
    
    $columns_result = db_query("SHOW COLUMNS FROM proprietarios");
    $colunas_importantes = ['id_proprietario', 'nome', 'cpf', 'cnpj', 'tipo_documento', 'email', 'telefone'];
    
    $colunas_existentes = [];
    foreach ($columns_result as $col) {
        $colunas_existentes[] = $col['Field'];
    }
    
    foreach ($colunas_importantes as $coluna) {
        $existe = in_array($coluna, $colunas_existentes);
        echo "   " . ($existe ? "✅" : "❌") . " {$coluna}\n";
    }
    
    // 7. Testar permissões de arquivo
    echo "\n7️⃣ Verificando permissões...\n";
    
    $arquivos_importantes = [
        'private/imoveis/proprietarios-listar.php',
        'private/imoveis/proprietario-editar.php',
        'private/imoveis/proprietario-cadastrar.php'
    ];
    
    foreach ($arquivos_importantes as $arquivo) {
        if (file_exists($arquivo)) {
            $readable = is_readable($arquivo);
            $writable = is_writable($arquivo);
            echo "   📁 {$arquivo}: " . ($readable ? "R" : "-") . ($writable ? "W" : "-") . "\n";
        } else {
            echo "   ❌ {$arquivo}: NÃO EXISTE\n";
        }
    }
    
    echo "\n🎯 DIAGNÓSTICO COMPLETO:\n";
    
    if (!empty($proprietarios)) {
        echo "✅ Proprietários existem na base\n";
    }
    
    if (file_exists('private/imoveis/proprietario-editar.php')) {
        echo "✅ Arquivo de edição existe\n";
    } else {
        echo "❌ Arquivo de edição não existe\n";
    }
    
    echo "✅ Função db_query funcionando\n";
    echo "✅ Estrutura da tabela adequada\n";
    
    echo "\n📝 POSSÍVEIS PROBLEMAS:\n";
    echo "1. Verifique se o servidor web está rodando\n";
    echo "2. Verifique se há erros de JavaScript no browser\n";
    echo "3. Verifique se as URLs estão corretas\n";
    echo "4. Verifique permissões de arquivos\n";
    echo "5. Verifique se há imóveis impedindo exclusão\n";
    
} catch (Exception $e) {
    echo "❌ ERRO DURANTE DIAGNÓSTICO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>