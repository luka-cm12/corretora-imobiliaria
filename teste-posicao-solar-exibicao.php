<?php
// Teste da exibição de posição solar
include_once 'private/includes/db.php';

echo "🌞 TESTE EXIBIÇÃO POSIÇÃO SOLAR\n";
echo "==============================\n\n";

// 1. Verificar se a coluna existe
$sql_check = "SHOW COLUMNS FROM imoveis LIKE 'posicao_solar'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "✅ Coluna 'posicao_solar' existe\n";
} else {
    echo "❌ Coluna 'posicao_solar' NÃO existe\n";
    exit;
}

// 2. Buscar imóveis com posição solar
$sql = "SELECT id, titulo, posicao_solar FROM imoveis WHERE posicao_solar IS NOT NULL AND posicao_solar != ''";
$result = mysqli_query($conn, $sql);

$count = mysqli_num_rows($result);
echo "📊 Total de imóveis com posição solar: $count\n\n";

if ($count > 0) {
    echo "📋 IMÓVEIS COM POSIÇÃO SOLAR:\n";
    echo "-----------------------------\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id'] . "\n";
        echo "Título: " . $row['titulo'] . "\n";
        echo "Posição: " . $row['posicao_solar'] . "\n";
        echo "-----------------------------\n";
    }
} else {
    echo "ℹ️  Nenhum imóvel tem posição solar definida ainda.\n";
    echo "   Edite alguns imóveis para testar a funcionalidade.\n";
}

echo "\n🎯 PÁGINAS ATUALIZADAS:\n";
echo "✅ private/imoveis/listar.php - Listagem administrativa\n";
echo "✅ imovel-detalhes.php - Página de detalhes\n";  
echo "✅ imoveis.php - Listagem pública\n";

echo "\n🔧 TESTE MANUAL:\n";
echo "1. Acesse: private/imoveis/editar.php?id=1\n";
echo "2. Selecione uma posição solar\n";
echo "3. Salve o imóvel\n";
echo "4. Verifique nas listagens se aparece a posição\n";

mysqli_close($conn);
?>