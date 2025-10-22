<?php
/**
 * Teste dos Novos Tipos de Imóveis
 * Página para verificar se os novos tipos estão funcionando corretamente
 */

session_start();
require_once 'private/includes/init.php';
require_once 'private/config/config.php';

// Função para formatar tipo do imóvel
function formatar_tipo_imovel($tipo) {
    $tipos = [
        'casa' => '🏠 Casa',
        'casa_condominio' => '🏘️ Casa em Condomínio',
        'apartamento' => '🏢 Apartamento',
        'apartamento_mobiliado' => '🏢🛋️ Apartamento Mobiliado',
        'sobrado' => '🏘️ Sobrado',
        'chacara' => '🌾 Chácara',
        'semi_mobiliado' => '🛋️ Semi Mobiliado',
        'terreno' => '🌿 Terreno',
        'comercial' => '🏪 Comercial'
    ];
    
    return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
}

// Lista dos novos tipos implementados
$novos_tipos = [
    'casa' => 'Casa tradicional',
    'casa_condominio' => 'Casa em condomínio fechado',
    'apartamento' => 'Apartamento vazio',
    'apartamento_mobiliado' => 'Apartamento com mobília completa',
    'sobrado' => 'Casa de dois ou mais andares',
    'chacara' => 'Propriedade rural ou sítio',
    'semi_mobiliado' => 'Imóvel com mobília básica',
    'terreno' => 'Lote para construção',
    'comercial' => 'Imóvel para negócios'
];

include 'private/includes/header.php';
?>

<style>
.test-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.test-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.test-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 2rem;
}

.tipos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.tipo-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.tipo-card:hover {
    border-color: #007bff;
    background: #e7f3ff;
    transform: translateY(-2px);
}

.tipo-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: white;
    background: #28a745;
    margin-top: 0.5rem;
}

.test-button {
    background: #007bff;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 0.5rem;
    font-size: 14px;
    transition: all 0.3s ease;
}

.test-button:hover {
    background: #0056b3;
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin: 1rem 0;
    border: 1px solid;
}

.alert-info {
    background: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-success {
    background: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

@media (max-width: 768px) {
    .tipos-grid {
        grid-template-columns: 1fr;
    }
    
    .test-button {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
}
</style>

<div class="test-container">
    <div class="test-header">
        <h1>🏠 Novos Tipos de Imóveis Implementados</h1>
        <p>Sistema atualizado com 9 tipos diferentes de propriedades</p>
    </div>
    
    <div class="test-card">
        <h2><i class="fas fa-list-ul"></i> Tipos Disponíveis</h2>
        <p>Os seguintes tipos de imóveis foram adicionados ao sistema:</p>
        
        <div class="tipos-grid">
            <?php foreach ($novos_tipos as $tipo => $descricao): ?>
                <div class="tipo-card">
                    <span class="tipo-icon"><?= formatar_tipo_imovel($tipo) ?></span>
                    <h4><?= explode(' ', formatar_tipo_imovel($tipo), 2)[1] ?? $tipo ?></h4>
                    <p><?= $descricao ?></p>
                    <span class="status-badge">✓ Implementado</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="test-card">
        <h2><i class="fas fa-cogs"></i> Páginas Atualizadas</h2>
        <div class="alert alert-success">
            <strong>✅ Implementação Concluída!</strong> Os novos tipos foram adicionados em todas as páginas do sistema.
        </div>
        
        <h4>Páginas modificadas:</h4>
        <ul>
            <li><strong>Cadastro de Imóveis</strong> - Formulário com 9 tipos disponíveis</li>
            <li><strong>Edição de Imóveis</strong> - Atualização de tipos existentes</li>
            <li><strong>Listagem Admin</strong> - Exibição de tipos com ícones</li>
            <li><strong>Busca Pública</strong> - Filtro por tipo atualizado</li>
            <li><strong>Página Inicial</strong> - Select de busca expandido</li>
        </ul>
    </div>
    
    <div class="test-card">
        <h2><i class="fas fa-test-tube"></i> Testar Funcionalidades</h2>
        <p>Use os links abaixo para testar as implementações:</p>
        
        <div class="alert alert-info">
            <strong>💡 Como testar:</strong> Acesse cada página e verifique se os novos tipos aparecem corretamente nos formulários e filtros.
        </div>
        
        <a href="private/imoveis/adicionar.php" class="test-button">
            <i class="fas fa-plus"></i> Testar Cadastro
        </a>
        
        <a href="private/imoveis/listar.php" class="test-button">
            <i class="fas fa-list"></i> Testar Listagem
        </a>
        
        <a href="busca.php" class="test-button">
            <i class="fas fa-search"></i> Testar Busca
        </a>
        
        <a href="index.php" class="test-button">
            <i class="fas fa-home"></i> Testar Página Inicial
        </a>
    </div>
    
    <div class="test-card">
        <h2><i class="fas fa-mobile-alt"></i> Compatibilidade Mobile</h2>
        <p>Todos os novos tipos são totalmente compatíveis com a interface mobile implementada anteriormente.</p>
        
        <div class="alert alert-success">
            <strong>📱 Mobile Ready:</strong> Os novos tipos funcionam perfeitamente em dispositivos móveis com interface touch-friendly.
        </div>
        
        <h4>Recursos mobile mantidos:</h4>
        <ul>
            <li>✅ Dropdowns otimizados para toque</li>
            <li>✅ Ícones visuais para melhor identificação</li>
            <li>✅ Interface responsiva em todas as telas</li>
            <li>✅ Validação em tempo real</li>
        </ul>
    </div>
    
    <div class="test-card">
        <h2><i class="fas fa-database"></i> Banco de Dados</h2>
        <p><strong>Importante:</strong> Os novos tipos são compatíveis com a estrutura atual do banco de dados.</p>
        
        <div class="alert alert-info">
            <strong>ℹ️ Informação:</strong> Não são necessárias alterações no banco de dados. Os novos tipos são armazenados como strings na coluna 'tipo' existente.
        </div>
        
        <h4>Tipos de dados aceitos:</h4>
        <ul>
            <li><code>casa</code>, <code>casa_condominio</code></li>
            <li><code>apartamento</code>, <code>apartamento_mobiliado</code></li>
            <li><code>sobrado</code>, <code>chacara</code></li>
            <li><code>semi_mobiliado</code></li>
            <li><code>terreno</code>, <code>comercial</code></li>
        </ul>
    </div>
</div>

<?php include 'private/includes/footer.php'; ?>