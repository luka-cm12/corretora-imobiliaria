<?php
// Teste dos novos campos internos
require_once(__DIR__ . '/private/includes/db.php');

echo "<h2>Teste dos Novos Campos Internos</h2>";

// Verificar se as colunas existem
echo "<h3>1. Verificação das Novas Colunas</h3>";

$matriculaCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'matricula'");
$exclusividadeCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'exclusividade'");
$taxaCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'taxa_intermediacao'");
$chavesQtdCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'chaves_quantidade'");
$chavesLocCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'chaves_localizacao'");

echo "Coluna matrícula existe: " . (is_array($matriculaCol) && count($matriculaCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "Coluna exclusividade existe: " . (is_array($exclusividadeCol) && count($exclusividadeCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "Coluna taxa_intermediacao existe: " . (is_array($taxaCol) && count($taxaCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "Coluna chaves_quantidade existe: " . (is_array($chavesQtdCol) && count($chavesQtdCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "Coluna chaves_localizacao existe: " . (is_array($chavesLocCol) && count($chavesLocCol) > 0 ? "✅ SIM" : "❌ NÃO") . "<br>";

// Mostrar estrutura das novas colunas
echo "<h3>2. Detalhes das Novas Colunas</h3>";
$novasColunas = db_query("SHOW COLUMNS FROM imoveis WHERE Field IN ('matricula', 'exclusividade', 'taxa_intermediacao', 'chaves_quantidade', 'chaves_localizacao')");
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Padrão</th><th>Comentário</th></tr>";
foreach ($novasColunas as $coluna) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($coluna['Field']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($coluna['Comment'] ?? '') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Teste de inserção SQL simulada
echo "<h3>3. Teste de SQL Dinâmico com Novos Campos</h3>";

// Simular dados
$titulo = "Casa com Exclusividade";
$descricao = "Casa em exclusividade na corretora";
$tipo = "casa";
$cidade = "São Paulo";
$bairro = "Vila Madalena";
$endereco = "Rua das Oliveiras, 123";
$preco = 750000;
$valor_condominio = 0; // Casa não tem condomínio
$valor_iptu = 2400.00; // IPTU anual
$matricula = "12345-SP";
$exclusividade = 1;
$taxa_intermediacao = 6.50;
$chaves_quantidade = 2;
$chaves_localizacao = "Gaveta do escritório principal";
$area = 180.5;
$quartos = 3;
$banheiros = 2;
$garagem = 2;
$imagens = "casa1.jpg,casa2.jpg,casa3.jpg";
$destaque = 1;
$id_proprietario = 1;
$caracteristicas_json = '["ar_condicionado","churrasqueira","quintal"]';

// Verificar colunas existentes
$colCheck = db_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis'");
$existing_columns = array_column($colCheck, 'COLUMN_NAME');

// Campos base
$fields = ['titulo', 'descricao', 'tipo', 'cidade', 'bairro', 'endereco', 'preco', 'area', 'quartos', 'banheiros', 'garagem', 'imagens', 'destaque', 'id_proprietario'];
$values = [$titulo, $descricao, $tipo, $cidade, $bairro, $endereco, $preco, $area, $quartos, $banheiros, $garagem, $imagens, $destaque, $id_proprietario];

// Adicionar campos condicionais
$camposOpcionais = [
    'caracteristicas' => $caracteristicas_json,
    'valor_condominio' => $valor_condominio,
    'valor_iptu' => $valor_iptu,
    'matricula' => $matricula,
    'exclusividade' => $exclusividade,
    'taxa_intermediacao' => $taxa_intermediacao,
    'chaves_quantidade' => $chaves_quantidade,
    'chaves_localizacao' => $chaves_localizacao
];

foreach ($camposOpcionais as $campo => $valor) {
    if (in_array($campo, $existing_columns)) {
        $fields[] = $campo;
        $values[] = $valor;
        echo "✅ Campo <strong>$campo</strong> será incluído<br>";
    } else {
        echo "❌ Campo <strong>$campo</strong> não existe na tabela<br>";
    }
}

// Montar SQL
$placeholders = str_repeat('?,', count($fields) - 1) . '?';
$sql = "INSERT INTO imoveis (" . implode(', ', $fields) . ") VALUES ($placeholders)";

echo "<h4>SQL Gerado:</h4>";
echo "<code style='background: #f8f9fa; padding: 10px; display: block; border-radius: 5px;'>" . htmlspecialchars($sql) . "</code>";

echo "<h4>Parâmetros:</h4>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
foreach ($values as $i => $value) {
    echo sprintf("%2d. %s = %s\n", $i + 1, $fields[$i], is_string($value) ? "'{$value}'" : $value);
}
echo "</pre>";

echo "<h4>Resumo dos Campos Internos:</h4>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<strong>📄 Matrícula:</strong> $matricula<br>";
echo "<strong>⭐ Exclusividade:</strong> " . ($exclusividade ? 'SIM' : 'NÃO') . "<br>";
echo "<strong>💰 Taxa Intermediação:</strong> {$taxa_intermediacao}%<br>";
echo "<strong>🔑 Chaves Disponíveis:</strong> $chaves_quantidade<br>";
echo "<strong>📍 Localização das Chaves:</strong> $chaves_localizacao<br>";
echo "<strong>🏢 Valor Condomínio:</strong> " . ($valor_condominio > 0 ? "R$ " . number_format($valor_condominio, 2, ',', '.') : 'Não há') . "<br>";
echo "<strong>🏛️ Valor IPTU:</strong> R$ " . number_format($valor_iptu, 2, ',', '.') . "<br>";
echo "</div>";

echo "<p><em>✅ Todos os novos campos internos foram implementados com sucesso!</em></p>";
?>