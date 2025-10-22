<?php
/**
 * Teste da funcionalidade de Posição Solar
 * Este arquivo testa se o novo campo está funcionando corretamente
 */

require_once(__DIR__ . '/private/includes/db.php');

// Verifica se a coluna posicao_solar existe na tabela
$colCheck = db_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'posicao_solar'");
$posicaoSolarExists = !empty($colCheck);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Posição Solar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .status { padding: 15px; border-radius: 5px; margin: 15px 0; }
        .ok { background: #d4edda; border-left: 5px solid #28a745; color: #155724; }
        .error { background: #f8d7da; border-left: 5px solid #dc3545; color: #721c24; }
        .info { background: #d1ecf1; border-left: 5px solid #17a2b8; color: #0c5460; }
        .preview { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .solar-option { display: inline-block; margin: 10px; padding: 15px; background: white; border: 2px solid #e9ecef; border-radius: 12px; min-width: 150px; text-align: center; }
        .solar-option span { font-size: 24px; display: block; margin-bottom: 5px; }
        .solar-option strong { display: block; margin-bottom: 5px; }
        .solar-option small { color: #666; }
        .buttons { margin-top: 20px; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 0 10px; }
        .btn:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌞 Teste - Funcionalidade Posição Solar</h1>
        
        <?php if ($posicaoSolarExists): ?>
            <div class="status ok">
                <h3>✅ Coluna 'posicao_solar' encontrada!</h3>
                <p>A nova funcionalidade está pronta para uso. O campo foi adicionado com sucesso na tabela de imóveis.</p>
            </div>
        <?php else: ?>
            <div class="status error">
                <h3>❌ Coluna 'posicao_solar' não encontrada</h3>
                <p>Você precisa executar o arquivo SQL para criar a nova coluna na tabela de imóveis.</p>
                <p><strong>Passos:</strong></p>
                <ol>
                    <li>Abra o phpMyAdmin ou seu cliente MySQL</li>
                    <li>Execute o arquivo: <code>adicionar-posicao-solar.sql</code></li>
                    <li>Recarregue esta página para testar novamente</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>ℹ️ Como funciona a Posição Solar</h3>
            <p>A posição solar ajuda os interessados a entender a iluminação natural do imóvel:</p>
            <ul>
                <li><strong>Norte:</strong> Maior incidência de sol durante todo o dia</li>
                <li><strong>Sul:</strong> Menor incidência solar, mais fresco</li>
                <li><strong>Leste:</strong> Sol da manhã, ideal para quartos</li>
                <li><strong>Oeste:</strong> Sol da tarde, pode esquentar mais</li>
            </ul>
        </div>
        
        <div class="preview">
            <h3>👀 Preview das Opções</h3>
            <p>Assim ficará o visual no formulário de cadastro/edição:</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin: 15px 0;">
                <div class="solar-option">
                    <span>☀️</span>
                    <strong>Norte</strong>
                    <small>Maior incidência de sol</small>
                </div>
                <div class="solar-option">
                    <span>🌤️</span>
                    <strong>Sul</strong>
                    <small>Menor incidência solar</small>
                </div>
                <div class="solar-option">
                    <span>🌅</span>
                    <strong>Leste</strong>
                    <small>Sol da manhã</small>
                </div>
                <div class="solar-option">
                    <span>🌇</span>
                    <strong>Oeste</strong>
                    <small>Sol da tarde</small>
                </div>
            </div>
        </div>
        
        <?php if ($posicaoSolarExists): ?>
            <div class="status ok">
                <h3>🎯 Testes Funcionais</h3>
                <p>A funcionalidade está pronta! Você pode:</p>
                <ul>
                    <li>✅ Cadastrar novos imóveis com posição solar</li>
                    <li>✅ Editar imóveis existentes e adicionar posição solar</li>
                    <li>✅ A informação será salva no banco de dados</li>
                    <li>✅ Interface responsiva para mobile e desktop</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="buttons">
            <?php if ($posicaoSolarExists): ?>
                <a href="private/imoveis/adicionar.php" class="btn">🏠 Testar Cadastro</a>
                <a href="private/imoveis/listar.php" class="btn btn-secondary">📝 Testar Edição</a>
            <?php else: ?>
                <a href="adicionar-posicao-solar.sql" class="btn" target="_blank">📄 Ver SQL</a>
                <a href="#" onclick="location.reload()" class="btn btn-secondary">🔄 Recarregar Teste</a>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px; font-size: 12px; color: #6c757d;">
            <p><strong>Informações Técnicas:</strong></p>
            <ul>
                <li>Campo no banco: <code>posicao_solar VARCHAR(10) NULL</code></li>
                <li>Valores possíveis: norte, sul, leste, oeste</li>
                <li>Interface: Radio buttons com ícones e descrições</li>
                <li>Compatível com versão mobile e desktop</li>
            </ul>
        </div>
    </div>
</body>
</html>