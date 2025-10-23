<?php
// Teste específico para verificar se atualizações funcionam
include_once 'private/includes/db.php';

echo "🔧 TESTE DE ATUALIZAÇÃO ESPECÍFICA\n";
echo "==================================\n\n";

// 1. Buscar primeiro imóvel
$sql = "SELECT id, titulo, preco, posicao_solar FROM imoveis ORDER BY id LIMIT 1";
$imoveis = db_query($sql);

if (empty($imoveis)) {
    echo "❌ Nenhum imóvel encontrado para teste\n";
    exit;
}

$imovel = $imoveis[0];
echo "📋 IMÓVEL PARA TESTE:\n";
echo "ID: " . $imovel['id'] . "\n";
echo "Título: " . $imovel['titulo'] . "\n";
echo "Preço atual: R$ " . number_format($imovel['preco'], 2, ',', '.') . "\n";
echo "Posição solar atual: " . ($imovel['posicao_solar'] ?: 'Não definida') . "\n\n";

// 2. Fazer uma atualização de teste
$novo_preco = 123456.78;
$nova_posicao = 'norte';

echo "🔄 FAZENDO ATUALIZAÇÃO DE TESTE...\n";
echo "Novo preço: R$ " . number_format($novo_preco, 2, ',', '.') . "\n";
echo "Nova posição: $nova_posicao\n\n";

$sql_update = "UPDATE imoveis SET preco = ?, posicao_solar = ? WHERE id = ?";
$result = db_query($sql_update, [$novo_preco, $nova_posicao, $imovel['id']]);

if ($result) {
    echo "✅ Atualização executada. Linhas afetadas: $result\n\n";
    
    // 3. Verificar se a atualização foi persistida
    echo "🔍 VERIFICANDO SE A ATUALIZAÇÃO FOI PERSISTIDA...\n";
    $sql_check = "SELECT preco, posicao_solar FROM imoveis WHERE id = ?";
    $check_result = db_query($sql_check, [$imovel['id']]);
    
    if (!empty($check_result)) {
        $updated_data = $check_result[0];
        echo "Preço após atualização: R$ " . number_format($updated_data['preco'], 2, ',', '.') . "\n";
        echo "Posição após atualização: " . ($updated_data['posicao_solar'] ?: 'Não definida') . "\n\n";
        
        if ($updated_data['preco'] == $novo_preco && $updated_data['posicao_solar'] == $nova_posicao) {
            echo "✅ SUCESSO! A atualização foi persistida corretamente.\n";
        } else {
            echo "❌ ERRO! A atualização NÃO foi persistida.\n";
            echo "Esperado: preço=$novo_preco, posição=$nova_posicao\n";
            echo "Atual: preço={$updated_data['preco']}, posição={$updated_data['posicao_solar']}\n";
        }
    } else {
        echo "❌ ERRO! Não foi possível verificar os dados após atualização.\n";
    }
    
    // 4. Restaurar valores originais
    echo "\n🔄 RESTAURANDO VALORES ORIGINAIS...\n";
    $sql_restore = "UPDATE imoveis SET preco = ?, posicao_solar = ? WHERE id = ?";
    $restore_result = db_query($sql_restore, [$imovel['preco'], $imovel['posicao_solar'], $imovel['id']]);
    
    if ($restore_result) {
        echo "✅ Valores originais restaurados.\n";
    } else {
        echo "⚠️  Atenção: Não foi possível restaurar os valores originais.\n";
    }
    
} else {
    echo "❌ ERRO na atualização!\n";
}

echo "\n🎯 CONCLUSÃO:\n";
echo "Se você viu 'SUCESSO' acima, o problema não está na atualização do banco.\n";
echo "O problema pode estar:\n";
echo "1. Na interface de listagem (cache do browser)\n";
echo "2. Na consulta de busca\n"; 
echo "3. Na formatação dos dados para exibição\n";
echo "\nTente: Ctrl+F5 para limpar cache do navegador\n";
?>