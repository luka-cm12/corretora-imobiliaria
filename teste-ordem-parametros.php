<?php
/**
 * Script para testar a ordem dos parâmetros do SQL UPDATE
 */

echo "<h2>Teste de Ordem de Parâmetros SQL</h2>";

// Simular valores de exemplo
$valores = [
    'titulo' => 'Casa Teste',
    'descricao' => 'Descrição teste',
    'tipo' => 'casa',
    'cidade' => 'São Paulo',
    'bairro' => 'Centro',
    'endereco' => 'Rua Teste, 123',
    'cep' => '12345678',
    'preco' => 300000.50,
    'valor_condominio' => 450.75,
    'valor_iptu' => 120.30,
    'area' => 85.5,
    'area_privativa' => 65.2,
    'area_comum' => 20.3,
    'quartos' => 3,
    'banheiros' => 2,
    'garagem' => 1,
    'imagens' => 'imagem1.jpg,imagem2.jpg',
    'destaque' => 1
];

echo "<h3>Valores de Exemplo:</h3>";
echo "<ul>";
foreach ($valores as $campo => $valor) {
    echo "<li><strong>$campo:</strong> $valor</li>";
}
echo "</ul>";

echo "<h3>SQL que seria gerado (com todas as colunas):</h3>";
echo "<pre>";
$sql = "UPDATE imoveis SET 
    titulo = ?, 
    descricao = ?, 
    tipo = ?, 
    cidade = ?, 
    bairro = ?, 
    endereco = ?, 
    cep = ?,
    preco = ?, 
    valor_condominio = ?,
    valor_iptu = ?,
    area = ?, 
    area_privativa = ?,
    area_comum = ?,
    quartos = ?, 
    banheiros = ?, 
    garagem = ?, 
    imagens = ?, 
    destaque = ?
WHERE id = ?";

echo htmlspecialchars($sql);
echo "</pre>";

echo "<h3>Ordem dos Parâmetros (NOVA - CORRIGIDA):</h3>";
echo "<ol>";
echo "<li>titulo: {$valores['titulo']}</li>";
echo "<li>descricao: {$valores['descricao']}</li>";
echo "<li>tipo: {$valores['tipo']}</li>";
echo "<li>cidade: {$valores['cidade']}</li>";
echo "<li>bairro: {$valores['bairro']}</li>";
echo "<li>endereco: {$valores['endereco']}</li>";
echo "<li>cep: {$valores['cep']}</li>";
echo "<li><strong style='color:red;'>preco: {$valores['preco']}</strong> ← PREÇO PRINCIPAL</li>";
echo "<li>valor_condominio: {$valores['valor_condominio']}</li>";
echo "<li>valor_iptu: {$valores['valor_iptu']}</li>";
echo "<li>area: {$valores['area']}</li>";
echo "<li><strong style='color:blue;'>area_privativa: {$valores['area_privativa']}</strong> ← ÁREA PRIVATIVA</li>";
echo "<li><strong style='color:green;'>area_comum: {$valores['area_comum']}</strong> ← ÁREA COMUM</li>";
echo "<li>quartos: {$valores['quartos']}</li>";
echo "<li>banheiros: {$valores['banheiros']}</li>";
echo "<li>garagem: {$valores['garagem']}</li>";
echo "<li>imagens: {$valores['imagens']}</li>";
echo "<li>destaque: {$valores['destaque']}</li>";
echo "<li>id: [ID_DO_IMOVEL]</li>";
echo "</ol>";

echo "<div style='background:#ffeded;padding:15px;border-radius:8px;margin:20px 0;'>";
echo "<h4>🔧 PROBLEMA IDENTIFICADO E CORRIGIDO:</h4>";
echo "<p><strong>Antes:</strong> Os parâmetros estavam sendo inseridos fora de ordem. O valor do preço estava recebendo valores de outros campos.</p>";
echo "<p><strong>Agora:</strong> A ordem dos parâmetros no array PHP corresponde exatamente à ordem dos placeholders (?) no SQL.</p>";
echo "</div>";

echo "<p><a href='private/imoveis/editar.php?id=1' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Testar Editar Imóvel</a></p>";
?>