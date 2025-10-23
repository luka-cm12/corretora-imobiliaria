<?php
require_once(__DIR__ . '/private/includes/db.php');

echo "<h3>Verificação de Colunas - Debug</h3>";

// Verificar se as colunas existem
$colunas_para_verificar = [
    'preco', 'valor_condominio', 'valor_iptu', 'matricula', 
    'parcelas_iptu', 'exclusividade', 'taxa_intermediacao',
    'area_privativa', 'area_comum', 'chaves_tipo', 'chaves_copias', 
    'observacoes_chaves', 'posicao_solar', 'caracteristicas', 'cep'
];

echo "<table border='1' style='border-collapse:collapse; margin:20px 0;'>";
echo "<tr><th>Coluna</th><th>Existe?</th><th>Tipo</th><th>Default</th></tr>";

foreach ($colunas_para_verificar as $coluna) {
    $result = db_query("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'imoveis' 
        AND COLUMN_NAME = ?
    ", [$coluna]);
    
    if (is_array($result) && count($result) > 0) {
        $info = $result[0];
        echo "<tr>";
        echo "<td style='padding:8px;'><strong>" . htmlspecialchars($coluna) . "</strong></td>";
        echo "<td style='padding:8px; color:green;'>✅ SIM</td>";
        echo "<td style='padding:8px;'>" . htmlspecialchars($info['DATA_TYPE']) . "</td>";
        echo "<td style='padding:8px;'>" . htmlspecialchars($info['COLUMN_DEFAULT'] ?? 'NULL') . "</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td style='padding:8px;'><strong>" . htmlspecialchars($coluna) . "</strong></td>";
        echo "<td style='padding:8px; color:red;'>❌ NÃO</td>";
        echo "<td style='padding:8px;'>-</td>";
        echo "<td style='padding:8px;'>-</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Verificar um imóvel específico para debug
$imovel_exemplo = db_query("SELECT * FROM imoveis ORDER BY id DESC LIMIT 1");
if (is_array($imovel_exemplo) && count($imovel_exemplo) > 0) {
    echo "<h4>Exemplo de Imóvel (ID: " . $imovel_exemplo[0]['id'] . ")</h4>";
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    
    foreach ($colunas_para_verificar as $coluna) {
        $valor = $imovel_exemplo[0][$coluna] ?? 'COLUNA NÃO EXISTE';
        echo "<tr>";
        echo "<td style='padding:8px;'>" . htmlspecialchars($coluna) . "</td>";
        echo "<td style='padding:8px;'>" . htmlspecialchars($valor) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhum imóvel encontrado na base de dados.</p>";
}

echo "<p><a href='private/imoveis/editar.php?id=1'>Testar Editar Imóvel #1</a></p>";
?>