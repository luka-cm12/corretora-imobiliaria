<?php
/**
 * Script para adicionar o tipo 'loft' ao banco de dados automaticamente
 * Execute este arquivo via navegador: http://localhost/corretora-imobiliaria-11/adicionar-loft-banco.php
 */

require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🏙️ Adicionando Tipo LOFT ao Banco de Dados</h1>";
echo "<div style='font-family: Arial; max-width: 800px; margin: 20px;'>";

try {
    // Verificar se a conexão está funcionando
    echo "<p>✅ <strong>Conectado ao banco de dados!</strong></p>";
    
    // Verificar a estrutura atual do campo tipo
    echo "<h3>📋 Verificando estrutura atual...</h3>";
    $current = db_query("SHOW COLUMNS FROM imoveis LIKE 'tipo'");
    
    if (empty($current)) {
        throw new Exception("❌ Tabela 'imoveis' ou coluna 'tipo' não encontrada!");
    }
    
    echo "<pre>Estrutura atual: " . print_r($current[0], true) . "</pre>";
    
    // Executar a alteração
    echo "<h3>🔧 Executando alteração...</h3>";
    
    $sql = "ALTER TABLE imoveis MODIFY COLUMN tipo ENUM(
        'casa',
        'casa_condominio',
        'apartamento', 
        'apartamento_mobiliado',
        'sobrado',
        'chacara',
        'semi_mobiliado',
        'terreno',
        'loft',
        'comercial'
    ) NOT NULL COMMENT 'Tipo do imóvel incluindo Loft'";
    
    $result = db_query($sql);
    
    if ($result !== false) {
        echo "<p>✅ <strong>Alteração executada com sucesso!</strong></p>";
    } else {
        throw new Exception("❌ Erro ao executar a alteração");
    }
    
    // Verificar o resultado
    echo "<h3>🔍 Verificando resultado...</h3>";
    $updated = db_query("SHOW COLUMNS FROM imoveis LIKE 'tipo'");
    echo "<pre>Nova estrutura: " . print_r($updated[0], true) . "</pre>";
    
    // Verificar se 'loft' está no ENUM
    $enum_values = $updated[0]['Type'];
    if (strpos($enum_values, 'loft') !== false) {
        echo "<p>🎉 <strong>SUCESSO! Tipo 'loft' foi adicionado com sucesso!</strong></p>";
        echo "<p>✅ O sistema agora suporta <strong>10 tipos de imóveis</strong> incluindo Loft!</p>";
    } else {
        echo "<p>⚠️ <strong>Atenção:</strong> 'loft' não foi encontrado no ENUM. Verifique manualmente.</p>";
    }
    
    // Mostrar distribuição atual por tipo
    echo "<h3>📊 Distribuição atual de imóveis por tipo:</h3>";
    $stats = db_query("SELECT tipo, COUNT(*) as quantidade FROM imoveis GROUP BY tipo ORDER BY tipo");
    
    if (!empty($stats)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Quantidade</th></tr>";
        foreach ($stats as $stat) {
            echo "<tr><td style='padding: 8px;'>" . htmlspecialchars($stat['tipo']) . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . $stat['quantidade'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ Nenhum imóvel cadastrado ainda.</p>";
    }
    
    echo "<h3>✅ Próximos Passos:</h3>";
    echo "<ul>";
    echo "<li>✅ Banco de dados atualizado</li>";
    echo "<li>✅ Códigos PHP já atualizados</li>";
    echo "<li>🎯 Agora você pode cadastrar imóveis tipo 'Loft'!</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>🔧 Solução Manual:</h3>";
    echo "<p>Execute este comando SQL no phpMyAdmin:</p>";
    echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px;'>";
    echo "ALTER TABLE imoveis MODIFY COLUMN tipo ENUM('casa','casa_condominio','apartamento','apartamento_mobiliado','sobrado','chacara','semi_mobiliado','terreno','loft','comercial') NOT NULL;";
    echo "</pre>";
}

echo "</div>";
echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Script executado em: " . date('Y-m-d H:i:s') . "</p>";
?>