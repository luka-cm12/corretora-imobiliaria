<?php
// Teste de debug para verificar atualizações
include_once 'private/includes/db.php';

echo "🔧 DEBUG - TESTE DE ATUALIZAÇÕES\n";
echo "=================================\n\n";

// 1. Verificar se a coluna posicao_solar existe
$sql_check = "SHOW COLUMNS FROM imoveis LIKE 'posicao_solar'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "✅ Coluna 'posicao_solar' existe\n";
} else {
    echo "❌ Coluna 'posicao_solar' NÃO existe\n";
    echo "Execute: adicionar-posicao-solar.sql\n\n";
    exit;
}

// 2. Verificar dados atuais na tabela
$sql = "SELECT id, titulo, preco, posicao_solar, updated_at, created_at FROM imoveis ORDER BY id LIMIT 5";
$result = mysqli_query($conn, $sql);

echo "\n📋 ÚLTIMOS 5 IMÓVEIS NA TABELA:\n";
echo "ID | Título | Preço | Posição Solar | Atualizado\n";
echo "---|--------|-------|---------------|----------\n";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $preco = number_format($row['preco'], 2, ',', '.');
        $posicao = $row['posicao_solar'] ?: 'Não definida';
        $atualizado = $row['updated_at'] ?: $row['created_at'];
        $data_formatada = $atualizado ? date('d/m/Y H:i', strtotime($atualizado)) : 'N/A';
        
        echo sprintf("%2d | %-20s | R$ %12s | %-12s | %s\n", 
            $row['id'], 
            substr($row['titulo'], 0, 20), 
            $preco, 
            $posicao, 
            $data_formatada
        );
    }
} else {
    echo "❌ Nenhum imóvel encontrado\n";
}

// 3. Testar função de formatação de preço
echo "\n💰 TESTE DE FORMATAÇÃO DE PREÇO:\n";
$precos_teste = ['250000', '1500.50', '2,500.00', 'R$ 350.000,00'];

foreach ($precos_teste as $preco_input) {
    $preco_limpo = (float) str_replace(['R$', '.', ',', ' '], ['', '', '.', ''], $preco_input);
    echo "Input: '$preco_input' -> Processado: $preco_limpo\n";
}

// 4. Verificar se há registros com preço 0
$sql_zero = "SELECT COUNT(*) as total FROM imoveis WHERE preco = 0 OR preco IS NULL";
$result_zero = mysqli_query($conn, $sql_zero);
$total_zero = mysqli_fetch_assoc($result_zero)['total'];

echo "\n⚠️  IMÓVEIS COM PREÇO ZERO: $total_zero\n";

// 5. Verificar cache/sessão
echo "\n🔄 VERIFICAÇÕES DE CACHE:\n";
echo "Session ID: " . (session_id() ?: 'Nenhuma sessão ativa') . "\n";
echo "Timestamp atual: " . date('Y-m-d H:i:s') . "\n";

mysqli_close($conn);
?>