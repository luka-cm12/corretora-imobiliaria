<?php
// Teste para verificar o problema de valores no formulário de edição
require_once(__DIR__ . '/private/includes/db.php');

echo "🔧 TESTE DE VALORES NO FORMULÁRIO DE EDIÇÃO\n";
echo "===========================================\n\n";

// Buscar um imóvel para teste
$imoveis = db_query("SELECT id, titulo, preco, valor_condominio, valor_iptu FROM imoveis LIMIT 3");

if (empty($imoveis)) {
    echo "❌ Nenhum imóvel encontrado na base de dados.\n";
    exit;
}

echo "📋 IMÓVEIS DISPONÍVEIS PARA TESTE:\n";
echo "----------------------------------\n";

foreach ($imoveis as $imovel) {
    echo sprintf("ID: %d - %s\n", $imovel['id'], $imovel['titulo']);
    echo sprintf("   💰 Preço: R$ %s\n", number_format((float)$imovel['preco'], 2, ',', '.'));
    
    if (isset($imovel['valor_condominio']) && $imovel['valor_condominio'] > 0) {
        echo sprintf("   🏢 Condomínio: R$ %s\n", number_format((float)$imovel['valor_condominio'], 2, ',', '.'));
    } else {
        echo "   🏢 Condomínio: Não informado\n";
    }
    
    if (isset($imovel['valor_iptu']) && $imovel['valor_iptu'] > 0) {
        echo sprintf("   🏛️ IPTU: R$ %s\n", number_format((float)$imovel['valor_iptu'], 2, ',', '.'));
    } else {
        echo "   🏛️ IPTU: Não informado\n";
    }
    echo "\n";
}

echo "🎯 TESTE DE FORMATAÇÃO:\n";
echo "-----------------------\n";

$teste_imovel = $imoveis[0];

echo "Valores RAW do banco:\n";
echo "- Preço: " . var_export($teste_imovel['preco'], true) . "\n";
echo "- Condomínio: " . var_export($teste_imovel['valor_condominio'] ?? 'NULL', true) . "\n";
echo "- IPTU: " . var_export($teste_imovel['valor_iptu'] ?? 'NULL', true) . "\n\n";

echo "Formatação PHP para formulário:\n";
echo "- Preço formatado: " . (isset($teste_imovel['preco']) && $teste_imovel['preco'] > 0 ? number_format((float)$teste_imovel['preco'], 2, ',', '.') : 'VAZIO') . "\n";
echo "- Condomínio formatado: " . (isset($teste_imovel['valor_condominio']) && $teste_imovel['valor_condominio'] > 0 ? number_format((float)$teste_imovel['valor_condominio'], 2, ',', '.') : 'VAZIO') . "\n";
echo "- IPTU formatado: " . (isset($teste_imovel['valor_iptu']) && $teste_imovel['valor_iptu'] > 0 ? number_format((float)$teste_imovel['valor_iptu'], 2, ',', '.') : 'VAZIO') . "\n\n";

echo "🔍 SIMULAÇÃO DE PROCESSAMENTO POST:\n";
echo "-----------------------------------\n";

// Simular valores que vêm do formulário
$simulacao_post = [
    'preco' => number_format((float)$teste_imovel['preco'], 2, ',', '.'),
    'valor_condominio' => isset($teste_imovel['valor_condominio']) && $teste_imovel['valor_condominio'] > 0 
        ? number_format((float)$teste_imovel['valor_condominio'], 2, ',', '.') 
        : '',
    'valor_iptu' => isset($teste_imovel['valor_iptu']) && $teste_imovel['valor_iptu'] > 0 
        ? number_format((float)$teste_imovel['valor_iptu'], 2, ',', '.') 
        : ''
];

echo "Valores simulados vindos do POST:\n";
foreach ($simulacao_post as $campo => $valor) {
    echo "- \$_POST['$campo'] = '" . $valor . "'\n";
}
echo "\n";

// Processar como no código atual
echo "Processamento (método atual):\n";

// Preço
$preco_raw = trim($simulacao_post['preco'] ?? '0');
$preco = 0;
if (!empty($preco_raw)) {
    $preco_limpo = str_replace(['R$', '.', ' '], ['', '', ''], $preco_raw);
    $preco_limpo = str_replace(',', '.', $preco_limpo);
    $preco = (float) $preco_limpo;
}

// Condomínio
$valor_condominio = 0;
$condominio_raw = trim($simulacao_post['valor_condominio'] ?? '');
if (!empty($condominio_raw)) {
    $condominio_limpo = str_replace(['R$', '.', ' '], ['', '', ''], $condominio_raw);
    $condominio_limpo = str_replace(',', '.', $condominio_limpo);
    $valor_condominio = (float) $condominio_limpo;
}

echo "- Preço processado: R$ " . number_format($preco, 2, ',', '.') . " (float: $preco)\n";
echo "- Condomínio processado: R$ " . number_format($valor_condominio, 2, ',', '.') . " (float: $valor_condominio)\n";

if ($preco != (float)$teste_imovel['preco']) {
    echo "⚠️ PROBLEMA: Preço processado não confere!\n";
    echo "   Original: " . (float)$teste_imovel['preco'] . "\n";
    echo "   Processado: " . $preco . "\n";
} else {
    echo "✅ Preço processado corretamente!\n";
}

echo "\n🎯 ACESSE PARA TESTAR:\n";
echo "----------------------\n";
echo "http://localhost/corretora-imobiliaria-11/private/imoveis/editar.php?id=" . $teste_imovel['id'] . "\n";
echo "\nVerifique se o campo 'Preço (R$)' mostra o valor correto do imóvel.\n";
?>