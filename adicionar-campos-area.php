<?php
/**
 * Script para adicionar campos de área (privativa e comum) automaticamente
 * Execute este arquivo via navegador: http://localhost/corretora-imobiliaria-11/adicionar-campos-area.php
 */

require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🏗️ Adicionando Campos de Área Detalhada</h1>";
echo "<div style='font-family: Arial; max-width: 800px; margin: 20px;'>";

try {
    // Verificar se a conexão está funcionando
    echo "<p>✅ <strong>Conectado ao banco de dados!</strong></p>";
    
    // Verificar se as colunas já existem
    echo "<h3>🔍 Verificando colunas existentes...</h3>";
    $existingCols = db_query("SHOW COLUMNS FROM imoveis WHERE Field LIKE '%area%'");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>Campo</th><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Default</th></tr>";
    foreach ($existingCols as $col) {
        $highlight = in_array($col['Field'], ['area_privativa', 'area_comum']) ? 'background: #ffffcc;' : '';
        echo "<tr style='{$highlight}'>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar se as colunas já existem
    $hasAreaPrivativa = false;
    $hasAreaComum = false;
    
    foreach ($existingCols as $col) {
        if ($col['Field'] === 'area_privativa') $hasAreaPrivativa = true;
        if ($col['Field'] === 'area_comum') $hasAreaComum = true;
    }
    
    if ($hasAreaPrivativa && $hasAreaComum) {
        echo "<p>ℹ️ <strong>As colunas já existem!</strong> Não é necessário executar novamente.</p>";
    } else {
        // Executar a alteração
        echo "<h3>🔧 Adicionando novas colunas...</h3>";
        
        $sql = "ALTER TABLE imoveis 
                ADD COLUMN area_privativa DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área privativa do imóvel em metros quadrados',
                ADD COLUMN area_comum DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área comum do condomínio em metros quadrados'";
        
        $result = db_query($sql);
        
        if ($result !== false) {
            echo "<p>✅ <strong>Colunas adicionadas com sucesso!</strong></p>";
        } else {
            throw new Exception("❌ Erro ao adicionar as colunas");
        }
    }
    
    // Verificar o resultado final
    echo "<h3>📊 Estrutura final das áreas:</h3>";
    $finalCols = db_query("SHOW COLUMNS FROM imoveis WHERE Field LIKE '%area%'");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>Campo</th><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Null</th><th style='padding: 8px;'>Default</th><th style='padding: 8px;'>Comentário</th></tr>";
    foreach ($finalCols as $col) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($col['Comment'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Consultar dados de teste
    echo "<h3>📋 Dados atuais (5 primeiros imóveis):</h3>";
    $testData = db_query("SELECT id, titulo, area, area_privativa, area_comum FROM imoveis LIMIT 5");
    
    if (!empty($testData)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Título</th><th style='padding: 8px;'>Área Total</th><th style='padding: 8px;'>Área Privativa</th><th style='padding: 8px;'>Área Comum</th></tr>";
        foreach ($testData as $row) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $row['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars(substr($row['titulo'], 0, 30)) . "...</td>";
            echo "<td style='padding: 8px;'>" . ($row['area'] ?? '0') . " m²</td>";
            echo "<td style='padding: 8px;'>" . ($row['area_privativa'] ?? '0') . " m²</td>";
            echo "<td style='padding: 8px;'>" . ($row['area_comum'] ?? '0') . " m²</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ Nenhum imóvel cadastrado ainda.</p>";
    }
    
    echo "<h3>✅ Próximos Passos:</h3>";
    echo "<ul>";
    echo "<li>✅ Banco de dados atualizado com campos de área</li>";
    echo "<li>✅ Formulários de cadastro e edição já atualizados</li>";
    echo "<li>🎯 Agora você pode cadastrar área privativa e área comum!</li>";
    echo "<li>📱 Os campos aparecerão para o público nas listagens</li>";
    echo "</ul>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>💡 Como Funciona:</h4>";
    echo "<ul>";
    echo "<li><strong>Área Total:</strong> Campo principal que já existia</li>";
    echo "<li><strong>Área Privativa:</strong> Área de uso exclusivo (quartos, sala, etc.)</li>";
    echo "<li><strong>Área Comum:</strong> Área compartilhada do condomínio (piscina, salão, etc.)</li>";
    echo "<li><strong>Público:</strong> Estes campos aparecerão nas listagens para os visitantes</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>🔧 Solução Manual:</h3>";
    echo "<p>Execute este comando SQL no phpMyAdmin:</p>";
    echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px;'>";
    echo "ALTER TABLE imoveis \n";
    echo "ADD COLUMN area_privativa DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área privativa do imóvel',\n";
    echo "ADD COLUMN area_comum DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Área comum do condomínio';";
    echo "</pre>";
}

echo "</div>";
echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Script executado em: " . date('Y-m-d H:i:s') . "</p>";
?>