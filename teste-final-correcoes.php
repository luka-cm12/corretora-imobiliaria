<?php
// Teste final integrado para todas as correções
include_once 'private/includes/db.php';
include_once 'private/includes/functions.php';

echo "🔧 TESTE FINAL - TODAS AS CORREÇÕES\n";
echo "===================================\n\n";

// 1. Testar formatação de preço
echo "💰 TESTE DE FORMATAÇÃO DE PREÇO:\n";
$precos_teste = [
    'R$ 250.000,00',  // Formato com R$ e pontos
    '350000',         // Só números
    '1.500.000,50',   // Com pontos e vírgula
    'R$ 0,00'         // Zero
];

foreach ($precos_teste as $preco_input) {
    $preco_limpo = (float) str_replace(['R$', '.', ',', ' '], ['', '', '.', ''], $preco_input);
    $resultado = $preco_limpo > 0 ? "✅ Válido" : "❌ Inválido (zero)";
    echo "Input: '$preco_input' -> $preco_limpo -> $resultado\n";
}

// 2. Verificar se posição solar existe
echo "\n🌞 VERIFICAÇÃO DE POSIÇÃO SOLAR:\n";
$sql_check = "SHOW COLUMNS FROM imoveis LIKE 'posicao_solar'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "✅ Coluna 'posicao_solar' existe\n";
} else {
    echo "❌ Coluna 'posicao_solar' NÃO existe - Execute adicionar-posicao-solar.sql\n";
}

// 3. Buscar imóveis com posição solar
$sql = "SELECT id, titulo, preco, posicao_solar FROM imoveis WHERE posicao_solar IS NOT NULL AND posicao_solar != '' LIMIT 3";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "\n📋 IMÓVEIS COM POSIÇÃO SOLAR:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        $preco_formatado = formatar_preco($row['preco']);
        echo "ID {$row['id']}: {$row['titulo']} - $preco_formatado - {$row['posicao_solar']}\n";
    }
} else {
    echo "\nℹ️  Nenhum imóvel com posição solar definida ainda\n";
}

// 4. Verificar arquivos de listagem
echo "\n📁 VERIFICAÇÃO DE ARQUIVOS:\n";
$arquivos_importantes = [
    'private/imoveis/listar.php' => 'Listagem administrativa',
    'imoveis.php' => 'Listagem pública',
    'imovel-detalhes.php' => 'Página de detalhes',
    'busca.php' => 'Página de busca',
    'private/imoveis/adicionar.php' => 'Cadastro de imóveis',
    'private/imoveis/editar.php' => 'Edição de imóveis'
];

foreach ($arquivos_importantes as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        $tem_posicao_solar = strpos($conteudo, 'posicao_solar') !== false;
        $status = $tem_posicao_solar ? "✅ Com posição solar" : "⚠️  Sem posição solar";
        echo "$descricao: $status\n";
    } else {
        echo "$descricao: ❌ Arquivo não encontrado\n";
    }
}

// 5. Simulação de dados para teste
echo "\n🧪 DADOS DE TESTE:\n";
echo "Para testar completamente:\n";
echo "1. Cadastre um imóvel com preço 'R$ 250.000,00'\n";
echo "2. Defina uma posição solar (Norte, Sul, Leste ou Oeste)\n";
echo "3. Verifique se aparece na listagem administrativa\n";
echo "4. Verifique se aparece na busca pública\n";
echo "5. Verifique se aparece nos detalhes do imóvel\n";

// 6. URLs de teste
echo "\n🔗 URLS PARA TESTE:\n";
echo "Listagem admin: private/imoveis/listar.php\n";
echo "Busca pública: busca.php\n";
echo "Adicionar imóvel: private/imoveis/adicionar.php\n";

mysqli_close($conn);
?>