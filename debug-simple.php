<?php
require_once(__DIR__ . '/private/includes/db.php');

// Verificar colunas essenciais
$colunas_verificar = ['preco', 'valor_condominio', 'valor_iptu'];

echo "=== VERIFICAÇÃO DE COLUNAS ===\n";

foreach ($colunas_verificar as $coluna) {
    $result = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = ?", [$coluna]);
    
    if (is_array($result) && count($result) > 0) {
        echo "✅ Coluna '$coluna' existe\n";
    } else {
        echo "❌ Coluna '$coluna' NÃO existe\n";
    }
}

// Buscar um imóvel para testar valores
$imovel = db_query("SELECT id, preco, valor_condominio, valor_iptu FROM imoveis ORDER BY id DESC LIMIT 1");

if (is_array($imovel) && count($imovel) > 0) {
    $dados = $imovel[0];
    echo "\n=== DADOS DO ÚLTIMO IMÓVEL (ID: {$dados['id']}) ===\n";
    echo "Preço: " . var_export($dados['preco'] ?? 'NULO', true) . "\n";
    echo "Condomínio: " . var_export($dados['valor_condominio'] ?? 'NULO', true) . "\n";
    echo "IPTU: " . var_export($dados['valor_iptu'] ?? 'NULO', true) . "\n";
} else {
    echo "\n❌ Nenhum imóvel encontrado\n";
}
?>