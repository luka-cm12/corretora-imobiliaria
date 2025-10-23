<?php
// Teste final do formulário de edição
include_once 'private/includes/db.php';

echo "🔧 TESTE FINAL - FORMULÁRIO DE EDIÇÃO\n";
echo "=====================================\n\n";

// 1. Verificar se a coluna posicao_solar existe
$sql_check = "SHOW COLUMNS FROM imoveis LIKE 'posicao_solar'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "✅ Coluna 'posicao_solar' existe na tabela 'imoveis'\n";
} else {
    echo "❌ Coluna 'posicao_solar' NÃO existe na tabela 'imoveis'\n";
    echo "Execute: adicionar-posicao-solar.sql\n\n";
}

// 2. Verificar primeiro imóvel
$sql = "SELECT id, titulo, preco, posicao_solar FROM imoveis ORDER BY id LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo "\n📋 DADOS DO PRIMEIRO IMÓVEL:\n";
    echo "ID: " . $row['id'] . "\n";
    echo "Título: " . $row['titulo'] . "\n";
    echo "Preço: R$ " . number_format($row['preco'], 2, ',', '.') . "\n";
    echo "Posição Solar: " . ($row['posicao_solar'] ?: 'Não informada') . "\n";
} else {
    echo "❌ Nenhum imóvel encontrado\n";
}

// 3. Testar arquivo de edição
$edit_file = 'private/imoveis/editar.php';
if (file_exists($edit_file)) {
    echo "\n✅ Arquivo de edição encontrado: $edit_file\n";
    
    // Verificar se as principais funções estão presentes
    $content = file_get_contents($edit_file);
    
    $checks = [
        'posicao-solar-grid' => 'Grid de posição solar',
        'money-mask' => 'Classe de máscara monetária',
        'posicao_solar.*checked' => 'Verificação de posição solar marcada',
        'addEventListener.*click' => 'Eventos de clique JavaScript'
    ];
    
    foreach ($checks as $pattern => $description) {
        if (preg_match("/$pattern/", $content)) {
            echo "✅ $description encontrado\n";
        } else {
            echo "❌ $description NÃO encontrado\n";
        }
    }
} else {
    echo "❌ Arquivo de edição NÃO encontrado: $edit_file\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. Abra um imóvel para edição\n";
echo "2. Teste os botões de posição solar\n";
echo "3. Verifique se o preço aparece formatado\n";
echo "4. Teste o salvamento das alterações\n\n";

mysqli_close($conn);
?>