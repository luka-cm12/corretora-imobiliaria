<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

// Verificar se existem as colunas internas
$condominioCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'valor_condominio'");
$iptuCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'valor_iptu'");
$matriculaCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'matricula'");
$exclusividadeCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'exclusividade'");
$chavesCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'chaves_quantidade'");

$hasFinancialCols = (is_array($condominioCol) && count($condominioCol) > 0) || (is_array($iptuCol) && count($iptuCol) > 0);
$hasInternalCols = (is_array($matriculaCol) && count($matriculaCol) > 0) || (is_array($exclusividadeCol) && count($exclusividadeCol) > 0) || (is_array($chavesCol) && count($chavesCol) > 0);
$hasAnyCols = $hasFinancialCols || $hasInternalCols;

// Paginação
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $por_pagina;

// Filtros
$filtro = '';
$params = [];
$types = '';

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = '%' . $_GET['busca'] . '%';
    $filtro = " WHERE (titulo LIKE ? OR cidade LIKE ? OR bairro LIKE ?)";
    $params = array_fill(0, 3, $busca);
    $types = 'sss';
}

// Total de imóveis
$total_query = "SELECT COUNT(*) as total FROM imoveis i 
                LEFT JOIN proprietarios p ON i.id_proprietario = p.id_proprietario" . $filtro;
$stmt = db_query($total_query, $params);
$total_imoveis = $stmt[0]['total'] ?? 0; // pega o primeiro elemento do array
$total_paginas = ceil($total_imoveis / $por_pagina);

// Obter imóveis com proprietários
$query = "SELECT i.*, p.nome as proprietario_nome FROM imoveis i 
          LEFT JOIN proprietarios p ON i.id_proprietario = p.id_proprietario" 
          . $filtro . " ORDER BY i.created_at DESC LIMIT " . (int)$por_pagina . " OFFSET " . (int)$offset;

$imoveis = db_query($query, $params); // já é array

$page_title = 'Listar Imóveis | Corretora Base';

// Função para formatar tipo do imóvel
function formatar_tipo_imovel($tipo) {
    $tipos = [
        'casa' => '🏠 Casa',
        'casa_condominio' => '🏘️ Casa em Condomínio',
        'apartamento' => '🏢 Apartamento',
        'apartamento_mobiliado' => '🏢🛋️ Apto Mobiliado',
        'sobrado' => '🏘️ Sobrado',
        'chacara' => '🌾 Chácara',
        'semi_mobiliado' => '🛋️ Semi Mobiliado',
        'terreno' => '🌿 Terreno',
        'loft' => '🏙️ Loft',
        'comercial' => '🏪 Comercial',
        'pavilhao' => '🏭 Pavilhão',
        'fazenda' => '🚜 Fazenda',
        'laja_terrea' => '🏘️ Laja Térrea',
        'sala_area' => '📦 Sala Aérea',
        'area_terras' => '🌍 Área de Terras',
        'loteamento' => '🗺️ Loteamento',
        'condominio_fechado' => '🏛️ Condomínio Fechado'
    ];
    
    return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
}

include __DIR__ . '/../includes/admin-header.php';
?>
            <div class="toolbar">
                <form action="" method="get" class="search-form">
                    <input type="text" name="busca" placeholder="Buscar imóveis..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <a href="adicionar.php" class="btn"><i class="fas fa-plus"></i> Adicionar Imóvel</a>
            </div>
            
            <?php if (empty($imoveis)): ?>
                <div class="no-results">
                    <i class="fas fa-home"></i>
                    <p>Nenhum imóvel encontrado</p>
                    <a href="listar.php" class="btn">Limpar Busca</a>
                </div>
            <?php else: ?>
                <div class="responsive-table">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 120px;">Imagem</th>
                                <th style="width: 25%;">Detalhes do Imóvel</th>
                                <th style="width: 15%;">Proprietário</th>
                                <th style="width: 12%;">Preço</th>
                                <?php if ($hasAnyCols): ?>
                                <th style="width: 20%;">🔐 Informações Internas</th>
                                <?php endif; ?>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($imoveis as $imovel): 
                            $imagens = array_values(array_filter(explode(',', $imovel['imagens'])));
                            $firstImage = (!empty($imagens)) 
                                ? '../../public/uploads/' . $imagens[0] 
                                : '../../public/assets/images/default-property.jpg';
                            $preco_formatado = formatar_preco($imovel['preco']);
                        ?>
                            <tr style="<?= !empty($imovel['destaque']) ? 'background-color: #fff8e1; border-left: 4px solid #ff9800;' : '' ?>">
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="font-weight: bold; color: #007bff;">#<?= $imovel['id'] ?></div>
                                    <?php if (!empty($imovel['destaque'])): ?>
                                        <div style="color: #ff9800; font-size: 10px;"><i class="fas fa-star"></i> DESTAQUE</div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="position: relative; display: inline-block;">
                                        <img src="<?= $firstImage ?>"
                                             alt="<?= htmlspecialchars($imovel['titulo']) ?>"
                                             class="thumbnail"
                                             style="width: 100px; height: 75px; object-fit: cover; border-radius: 8px; border: 2px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <?php if (isset($imovel['exclusividade']) && $imovel['exclusividade']): ?>
                                            <div style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">★</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="vertical-align: top; padding: 12px 8px;">
                                    <div style="margin-bottom: 8px;">
                                        <div style="font-weight: bold; font-size: 16px; color: #333; margin-bottom: 4px;">
                                            <?= htmlspecialchars($imovel['titulo'] ?? '') ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                            <span class="badge tipo-imovel" style="background: #007bff; color: white; padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 500;">
                                                <?= formatar_tipo_imovel($imovel['tipo'] ?? '') ?>
                                            </span>
                                            <?php if (isset($imovel['exclusividade']) && $imovel['exclusividade']): ?>
                                                <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold;">
                                                    ⭐ EXCLUSIVO
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="color: #666; font-size: 13px; margin-bottom: 4px;">
                                            <i class="fas fa-map-marker-alt" style="color: #28a745; margin-right: 4px;"></i>
                                            <strong><?= htmlspecialchars($imovel['bairro'] ?? '') ?></strong>, <?= htmlspecialchars($imovel['cidade'] ?? '') ?>
                                        </div>
                                        <div style="color: #666; font-size: 12px; display: flex; gap: 12px;">
                                            <?php if ($imovel['quartos'] > 0): ?>
                                                <span><i class="fas fa-bed" style="color: #007bff;"></i> <?= $imovel['quartos'] ?></span>
                                            <?php endif; ?>
                                            <?php if ($imovel['banheiros'] > 0): ?>
                                                <span><i class="fas fa-bath" style="color: #17a2b8;"></i> <?= $imovel['banheiros'] ?></span>
                                            <?php endif; ?>
                                            <?php if ($imovel['garagem'] > 0): ?>
                                                <span><i class="fas fa-car" style="color: #6c757d;"></i> <?= $imovel['garagem'] ?></span>
                                            <?php endif; ?>
                                            <?php if ($imovel['area'] > 0): ?>
                                                <span><i class="fas fa-expand-arrows-alt" style="color: #28a745;"></i> <?= number_format($imovel['area'], 0) ?>m²</span>
                                            <?php endif; ?>
                                            <?php if (!empty($imovel['posicao_solar'])): ?>
                                                <?php 
                                                $icones_posicao = [
                                                    'norte' => '🧭',
                                                    'sul' => '🧭', 
                                                    'leste' => '🌅',
                                                    'oeste' => '🌇'
                                                ];
                                                $posicao_nome = [
                                                    'norte' => 'Norte',
                                                    'sul' => 'Sul',
                                                    'leste' => 'Leste', 
                                                    'oeste' => 'Oeste'
                                                ];
                                                ?>
                                                <span title="Posição Solar: <?= ucfirst($imovel['posicao_solar']) ?>">
                                                    <?= $icones_posicao[$imovel['posicao_solar']] ?? '☀️' ?> 
                                                    <?= $posicao_nome[$imovel['posicao_solar']] ?? ucfirst($imovel['posicao_solar']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="vertical-align: top; padding: 12px 8px;">
                                    <?php if (!empty($imovel['proprietario_nome'])): ?>
                                        <div style="font-weight: 500; color: #333; margin-bottom: 4px;">
                                            <i class="fas fa-user" style="color: #007bff; margin-right: 4px;"></i>
                                            <?= htmlspecialchars($imovel['proprietario_nome']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">
                                            <i class="fas fa-user-times" style="color: #6c757d; margin-right: 4px;"></i>
                                            Não informado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="vertical-align: top; padding: 12px 8px; text-align: right;">
                                    <div style="font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 4px;">
                                        <?= $preco_formatado ?>
                                    </div>
                                    <div style="font-size: 11px; color: #6c757d;">
                                        ID: <?= $imovel['created_at'] ? date('d/m/Y', strtotime($imovel['created_at'])) : 'N/A' ?>
                                    </div>
                                </td>
                                <?php if ($hasAnyCols): ?>
                                <td style="vertical-align: top; padding: 12px 8px;">
                                    <div style="font-size: 11px; line-height: 1.5; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #e9ecef;">
                                        <?php 
                                        $hasInfo = false;
                                        ?>
                                        
                                        <!-- Valores Financeiros -->
                                        <?php if ((isset($imovel['valor_condominio']) && $imovel['valor_condominio'] > 0) || (isset($imovel['valor_iptu']) && $imovel['valor_iptu'] > 0) || (isset($imovel['taxa_intermediacao']) && $imovel['taxa_intermediacao'] > 0)): ?>
                                            <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #dee2e6;">
                                                <div style="font-weight: bold; color: #495057; margin-bottom: 4px; font-size: 10px;">💰 FINANCEIRO</div>
                                                <?php if (isset($imovel['valor_condominio']) && $imovel['valor_condominio'] > 0): 
                                                    $hasInfo = true; ?>
                                                    <div style="color: #007bff; margin-bottom: 2px;"><strong>🏢</strong> R$ <?= number_format($imovel['valor_condominio'], 2, ',', '.') ?>/mês</div>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($imovel['valor_iptu']) && $imovel['valor_iptu'] > 0): 
                                                    $hasInfo = true; ?>
                                                    <div style="color: #28a745; margin-bottom: 2px;"><strong>🏛️</strong> R$ <?= number_format($imovel['valor_iptu'], 2, ',', '.') ?>/ano</div>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($imovel['taxa_intermediacao']) && $imovel['taxa_intermediacao'] > 0): 
                                                    $hasInfo = true; ?>
                                                    <div style="color: #ffc107; margin-bottom: 2px;"><strong>�</strong> <?= number_format($imovel['taxa_intermediacao'], 2, ',', '.') ?>%</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Documentação -->
                                        <?php if (!empty($imovel['matricula'])): ?>
                                            <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #dee2e6;">
                                                <div style="font-weight: bold; color: #495057; margin-bottom: 4px; font-size: 10px;">📋 DOCUMENTOS</div>
                                                <div style="color: #6c757d; margin-bottom: 2px;">
                                                    <strong>📄</strong> <?= htmlspecialchars(substr($imovel['matricula'], 0, 15)) ?><?= strlen($imovel['matricula']) > 15 ? '...' : '' ?>
                                                </div>
                                            </div>
                                            <?php $hasInfo = true; ?>
                                        <?php endif; ?>
                                        
                                        <!-- Controle de Chaves -->
                                        <?php if (isset($imovel['chaves_quantidade']) && $imovel['chaves_quantidade'] > 0): ?>
                                            <div style="margin-bottom: 6px;">
                                                <div style="font-weight: bold; color: #495057; margin-bottom: 4px; font-size: 10px;">🔑 CHAVES</div>
                                                <div style="color: #17a2b8; margin-bottom: 2px;">
                                                    <strong><?= $imovel['chaves_quantidade'] ?></strong> <?= $imovel['chaves_quantidade'] == 1 ? 'chave' : 'chaves' ?>
                                                </div>
                                                <?php if (!empty($imovel['chaves_localizacao'])): ?>
                                                    <div style="color: #495057; margin-bottom: 2px; background: #e9ecef; padding: 4px 6px; border-radius: 4px;">
                                                        <strong><i class="fas fa-user" style="color: #007bff;"></i></strong> 
                                                        <?= htmlspecialchars(substr($imovel['chaves_localizacao'], 0, 25)) ?><?= strlen($imovel['chaves_localizacao']) > 25 ? '...' : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php $hasInfo = true; ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!$hasInfo): ?>
                                            <div style="text-align: center; color: #6c757d; font-style: italic; padding: 10px;">
                                                <i class="fas fa-info-circle"></i><br>
                                                Sem informações<br>internas
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                                <td class="actions" style="vertical-align: middle; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                        <a href="editar.php?id=<?= $imovel['id'] ?>" 
                                           class="btn-edit" 
                                           title="Editar Imóvel"
                                           style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #007bff; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; transition: all 0.3s;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="ficha-tecnica-admin.php?id=<?= $imovel['id'] ?>" 
                                           target="_blank"
                                           title="Ficha Técnica Administrativa"
                                           style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #6f42c1; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; transition: all 0.3s;">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="../../imovel-detalhes.php?id=<?= $imovel['id'] ?>" 
                                           target="_blank"
                                           title="Visualizar no Site"
                                           style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #28a745; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; transition: all 0.3s;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="excluir.php?id=<?= $imovel['id'] ?>" 
                                           class="btn-delete" 
                                           title="Excluir Imóvel"
                                           onclick="return confirm('Tem certeza que deseja excluir este imóvel?\n\nImóvel: <?= htmlspecialchars($imovel['titulo']) ?>\nEsta ação não pode ser desfeita.')"
                                           style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #dc3545; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; transition: all 0.3s;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <?php if ($total_paginas > 1): ?>
                    <div class="pagination">
                        <?php if ($pagina > 1): ?>
                            <a href="?pagina=<?= $pagina - 1 ?><?= isset($_GET['busca']) ? '&busca=' . urlencode($_GET['busca']) : '' ?>" class="page-item">&laquo; Anterior</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="?pagina=<?= $i ?><?= isset($_GET['busca']) ? '&busca=' . urlencode($_GET['busca']) : '' ?>" class="page-item <?= $i == $pagina ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($pagina < $total_paginas): ?>
                            <a href="?pagina=<?= $pagina + 1 ?><?= isset($_GET['busca']) ? '&busca=' . urlencode($_GET['busca']) : '' ?>" class="page-item">Próxima &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
  </div>

<style>
/* Melhorias na tabela de imóveis */
.responsive-table table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.responsive-table th {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    font-weight: 600;
    padding: 16px 12px;
    text-align: left;
    border: none;
}

.responsive-table td {
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

.responsive-table tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    transition: all 0.3s ease;
}

/* Hover effects nos botões de ação */
.actions a:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Badge de tipo melhorado */
.badge.tipo-imovel {
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Efeito para imóveis em destaque */
.responsive-table tr[style*="background-color: #fff8e1"] {
    position: relative;
    animation: pulse-highlight 2s infinite;
}

@keyframes pulse-highlight {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.4); 
    }
    50% { 
        box-shadow: 0 0 20px 5px rgba(255, 152, 0, 0.1); 
    }
}

/* Responsivo melhorado */
@media (max-width: 1200px) {
    .responsive-table {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .responsive-table table {
        min-width: 1000px;
    }
}

/* Melhorias no badge de exclusivo */
.responsive-table tr div[style*="background: #dc3545"] {
    animation: star-glow 1.5s ease-in-out infinite alternate;
}

@keyframes star-glow {
    from { box-shadow: 0 0 5px rgba(220, 53, 69, 0.5); }
    to { box-shadow: 0 0 15px rgba(220, 53, 69, 0.8); }
}

/* Estilo para seção de informações internas */
.responsive-table td div[style*="background: #f8f9fa"] {
    transition: all 0.3s ease;
}

.responsive-table tr:hover td div[style*="background: #f8f9fa"] {
    background: #e9ecef !important;
    border-color: #dee2e6 !important;
}

/* Tooltip para imagens */
.responsive-table .thumbnail {
    transition: all 0.3s ease;
    cursor: pointer;
}

.responsive-table .thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
</style>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>