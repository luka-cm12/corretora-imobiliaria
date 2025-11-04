<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../includes/db.php');

echo "<h1>Teste da Ficha Técnica Administrativa</h1>";

// Buscar um imóvel de exemplo
$imoveis = db_query("SELECT id, titulo FROM imoveis LIMIT 1");

if ($imoveis && count($imoveis) > 0) {
    $imovel = $imoveis[0];
    echo "<div style='padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>Imóvel encontrado:</strong> " . htmlspecialchars($imovel['titulo']) . "</p>";
    echo "<p><strong>ID:</strong> " . $imovel['id'] . "</p>";
    echo "<br>";
    echo "<p><strong>Links de teste:</strong></p>";
    echo "<ul style='margin: 10px 0; padding-left: 20px;'>";
    echo "<li><a href='ficha-tecnica-admin.php?id=" . $imovel['id'] . "' target='_blank'>🖨️ Ver Ficha Técnica Administrativa</a></li>";
    echo "<li><a href='listar.php'>📋 Voltar para Lista de Imóveis</a></li>";
    echo "<li><a href='editar.php?id=" . $imovel['id'] . "'>✏️ Editar este Imóvel</a></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✅ Ficha Reformulada no Formato Profissional!</h3>";
    echo "<p><strong>Agora a ficha está no formato de captação profissional:</strong></p>";
    echo "<ul style='margin: 10px 0; padding-left: 20px;'>";
    echo "<li>📋 Cabeçalho formal com dados da corretora</li>";
    echo "<li>👤 Seção completa de dados do proprietário</li>";
    echo "<li>🏠 Identificação detalhada do imóvel</li>";
    echo "<li>📊 Características técnicas organizadas</li>";
    echo "<li>💰 Valores e documentação estruturados</li>";
    echo "<li>✅ Comodidades em formato checkbox</li>";
    echo "<li>📝 Campo de observações</li>";
    echo "<li>🔐 Controle interno da imobiliária</li>";
    echo "<li>✍️ Área para assinaturas</li>";
    echo "<li>🖨️ Layout A4 otimizado para impressão</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📝 Como usar:</h3>";
    echo "<ol style='margin: 10px 0; padding-left: 20px;'>";
    echo "<li>Vá para a <a href='listar.php'>lista de imóveis</a></li>";
    echo "<li>Clique no botão roxo de impressão (📄) em qualquer imóvel</li>";
    echo "<li>A ficha técnica abrirá em uma nova aba</li>";
    echo "<li>Use o botão 'Imprimir' para imprimir ou salvar como PDF</li>";
    echo "</ol>";
    echo "</div>";
    
} else {
    echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin: 20px 0;'>";
    echo "<p>❌ Nenhum imóvel encontrado no banco de dados.</p>";
    echo "<p>Cadastre alguns imóveis primeiro para testar a funcionalidade.</p>";
    echo "</div>";
}

echo "<div style='margin-top: 30px; padding: 15px; background: #e2e3e5; border-radius: 8px;'>";
echo "<p><strong>Arquivos criados/modificados:</strong></p>";
echo "<ul style='margin: 10px 0; padding-left: 20px; font-family: monospace; font-size: 14px;'>";
echo "<li>✅ private/imoveis/ficha-tecnica-admin.php (NOVO)</li>";
echo "<li>✅ private/imoveis/listar.php (botão adicionado)</li>";
echo "<li>✅ public/assets/css/admin.css (estilos melhorados)</li>";
echo "</ul>";
echo "</div>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f7fa;
}

h1 {
    color: #2c3e50;
    margin-bottom: 20px;
}

a {
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>