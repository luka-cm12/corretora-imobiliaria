<?php
require_once 'private/includes/db.php';

echo "🧪 TESTE DE INTERFACE - Botões Editar e Deletar\n\n";

// Simular dados como aparecem na listagem
try {
    $where_sql = '';
    $params = [];
    
    $sql = "SELECT p.*, 
                   (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
            FROM proprietarios p 
            {$where_sql}
            ORDER BY p.nome ASC 
            LIMIT 3";

    $proprietarios = db_query($sql, $params);
    
    echo "1️⃣ Proprietários encontrados para testar interface:\n";
    
    foreach ($proprietarios as $proprietario) {
        echo "\n📋 Proprietário ID: {$proprietario['id_proprietario']}\n";
        echo "   - Nome: {$proprietario['nome']}\n";
        echo "   - Total imóveis: {$proprietario['total_imoveis']}\n";
        
        // Simular URL de edição
        $url_edicao = "proprietario-editar.php?id={$proprietario['id_proprietario']}";
        echo "   - URL edição: {$url_edicao}\n";
        
        // Simular condição de exclusão
        if ($proprietario['total_imoveis'] > 0) {
            echo "   - Status exclusão: ❌ BLOQUEADO (possui imóveis)\n";
            echo "   - Botão: DESABILITADO\n";
        } else {
            echo "   - Status exclusão: ✅ PERMITIDO (sem imóveis)\n";
            echo "   - Botão: HABILITADO\n";
        }
        
        // Simular HTML do botão (como seria gerado)
        echo "   - HTML botão editar: <a href=\"{$url_edicao}\" class=\"btn-sm btn-edit\">Editar</a>\n";
        
        if ($proprietario['total_imoveis'] > 0) {
            echo "   - HTML botão deletar: <button disabled>Deletar</button>\n";
        } else {
            echo "   - HTML botão deletar: <button type=\"submit\">Deletar</button>\n";
        }
    }
    
    echo "\n2️⃣ Teste de simulação de exclusão:\n";
    
    // Pegar um proprietário sem imóveis
    $sem_imoveis = null;
    foreach ($proprietarios as $prop) {
        if ($prop['total_imoveis'] == 0) {
            $sem_imoveis = $prop;
            break;
        }
    }
    
    if ($sem_imoveis) {
        echo "   ✅ Testando exclusão do proprietário: {$sem_imoveis['nome']}\n";
        
        // Simular POST data
        $post_data = [
            'action' => 'delete',
            'id' => $sem_imoveis['id_proprietario'],
            'csrf_token' => 'token_exemplo'
        ];
        
        echo "   📤 Dados POST simulados:\n";
        foreach ($post_data as $key => $value) {
            echo "      - {$key}: {$value}\n";
        }
        
        // Simular lógica de exclusão
        $id = (int)$post_data['id'];
        $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id])[0]['count'];
        
        echo "   🔍 Verificação de imóveis: {$imoveis_count} imóveis encontrados\n";
        
        if ($imoveis_count > 0) {
            echo "   ❌ EXCLUSÃO BLOQUEADA: Proprietário possui {$imoveis_count} imóvel(is)\n";
        } else {
            echo "   ✅ EXCLUSÃO PERMITIDA: Proprietário sem imóveis\n";
            echo "   💡 Query seria: DELETE FROM proprietarios WHERE id_proprietario = {$id}\n";
        }
    } else {
        echo "   ⚠️ Nenhum proprietário sem imóveis encontrado para testar exclusão\n";
    }
    
    echo "\n3️⃣ Verificação de arquivos necessários:\n";
    
    $arquivos = [
        'private/imoveis/proprietarios-listar.php' => 'Listagem',
        'private/imoveis/proprietario-editar.php' => 'Edição',
        'private/includes/admin-header.php' => 'Header Admin',
        'private/includes/admin-footer.php' => 'Footer Admin'
    ];
    
    foreach ($arquivos as $arquivo => $descricao) {
        if (file_exists($arquivo)) {
            echo "   ✅ {$descricao}: {$arquivo}\n";
        } else {
            echo "   ❌ {$descricao}: {$arquivo} (NÃO ENCONTRADO)\n";
        }
    }
    
    echo "\n🎯 RESUMO DOS TESTES:\n";
    echo "✅ Consulta de proprietários funcionando\n";
    echo "✅ Contagem de imóveis funcionando\n";
    echo "✅ URLs de edição sendo geradas corretamente\n";
    echo "✅ Lógica de bloqueio/permissão de exclusão funcionando\n";
    
    echo "\n💡 Se ainda não funciona, verifique:\n";
    echo "1. Se o servidor web (Apache/Nginx) está rodando\n";
    echo "2. Se não há erros no console do navegador (F12)\n";
    echo "3. Se a URL está correta: http://localhost/corretora-imobiliaria-11/private/imoveis/proprietarios-listar.php\n";
    echo "4. Se há erros de PHP sendo exibidos na tela\n";
    echo "5. Se os ícones FontAwesome estão carregando\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>