<?php
// Teste dos valores internos (valor_condominio e valor_iptu)
require_once(__DIR__ . '/private/includes/db.php');

echo "<h2>Teste dos Valores Internos</h2>";

// Verificar se as colunas existem
echo "<h3>1. Verificação das Colunas</h3>";

$condominioCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'valor_condominio'");
$iptuCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'valor_iptu'");

echo "Coluna valor_condominio existe: " . (is_array($condominioCol) && count($condominioCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "Coluna valor_iptu existe: " . (is_array($iptuCol) && count($iptuCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";

// Mostrar estrutura da tabela
echo "<h3>2. Estrutura da Tabela</h3>";
$colunas = db_query("SHOW COLUMNS FROM imoveis");
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
foreach ($colunas as $coluna) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Teste de inserção SQL
echo "<h3>3. Teste de SQL Dinâmico</h3>";

// Simular inserção
$titulo = "Teste";
$descricao = "Descrição teste";
$tipo = "casa";
$cidade = "São Paulo";
$bairro = "Centro";
$endereco = "Rua Teste, 123";
$preco = 500000;
$valor_condominio = 350.00;
$valor_iptu = 1200.00;
$area = 120.5;
$quartos = 3;
$banheiros = 2;
$garagem = 2;
$imagens = "test1.jpg,test2.jpg";
$destaque = 0;

// SQL com placeholders condicionais
$sqlInsert = "INSERT INTO imoveis (titulo, descricao, tipo, cidade, bairro, endereco, preco, %CONDOMINIO% %IPTU% area, quartos, banheiros, garagem, imagens, destaque) VALUES (?, ?, ?, ?, ?, ?, ?, %CONDOMINIO_PARAM% %IPTU_PARAM% ?, ?, ?, ?, ?, ?)";

$params = [$titulo, $descricao, $tipo, $cidade, $bairro, $endereco, $preco];

// Processamento condicional
if (is_array($condominioCol) && count($condominioCol) > 0) {
    $sqlInsert = str_replace('%CONDOMINIO%', 'valor_condominio,', $sqlInsert);
    $sqlInsert = str_replace('%CONDOMINIO_PARAM%', '?,', $sqlInsert);
    $params[] = $valor_condominio;
} else {
    $sqlInsert = str_replace('%CONDOMINIO%', '', $sqlInsert);
    $sqlInsert = str_replace('%CONDOMINIO_PARAM%', '', $sqlInsert);
}

if (is_array($iptuCol) && count($iptuCol) > 0) {
    $sqlInsert = str_replace('%IPTU%', 'valor_iptu,', $sqlInsert);
    $sqlInsert = str_replace('%IPTU_PARAM%', '?,', $sqlInsert);
    $params[] = $valor_iptu;
} else {
    $sqlInsert = str_replace('%IPTU%', '', $sqlInsert);
    $sqlInsert = str_replace('%IPTU_PARAM%', '', $sqlInsert);
}

$params = array_merge($params, [$area, $quartos, $banheiros, $garagem, $imagens, $destaque]);

echo "<strong>SQL Final:</strong><br>";
echo "<code>" . htmlspecialchars($sqlInsert) . "</code><br><br>";

echo "<strong>Parâmetros:</strong><br>";
echo "<pre>" . print_r($params, true) . "</pre>";

echo "<p><em>Teste concluído! Verifique se o SQL está correto para seu banco de dados.</em></p>";
?>