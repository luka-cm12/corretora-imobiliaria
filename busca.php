<?php
require_once 'private/includes/db.php';
require_once 'private/includes/functions.php';

// Inicializar variáveis de filtro (inclui novos filtros)
$filtros = [
    'q' => isset($_GET['q']) ? trim($_GET['q']) : '',
    'tipo' => isset($_GET['tipo']) ? $_GET['tipo'] : '',
    'cidade' => isset($_GET['cidade']) ? $_GET['cidade'] : '',
    'bairro' => isset($_GET['bairro']) ? $_GET['bairro'] : '',
    'preco' => isset($_GET['preco']) ? $_GET['preco'] : '', // faixa predefinida (compatibilidade)
    'preco_min' => isset($_GET['preco_min']) ? $_GET['preco_min'] : '',
    'preco_max' => isset($_GET['preco_max']) ? $_GET['preco_max'] : '',
    'quartos' => isset($_GET['quartos']) ? $_GET['quartos'] : '',
    'banheiros' => isset($_GET['banheiros']) ? $_GET['banheiros'] : '',
    'ordenar' => isset($_GET['ordenar']) ? $_GET['ordenar'] : 'recentes',
    'page' => isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1,
];

// Construir WHERE a partir dos filtros
$whereParts = [];
$params = [];

if (!empty($filtros['q'])) {
    $whereParts[] = "(titulo LIKE ? OR descricao LIKE ? OR endereco LIKE ? OR bairro LIKE ? OR cidade LIKE ?)";
    $qLike = '%' . $filtros['q'] . '%';
    // 5 vezes o mesmo parâmetro para os 5 campos
    array_push($params, $qLike, $qLike, $qLike, $qLike, $qLike);
}

if (!empty($filtros['tipo'])) {
    $whereParts[] = "tipo = ?";
    $params[] = $filtros['tipo'];
}

if (!empty($filtros['cidade'])) {
    $whereParts[] = "cidade = ?";
    $params[] = $filtros['cidade'];
}

if (!empty($filtros['bairro'])) {
    $whereParts[] = "bairro = ?";
    $params[] = $filtros['bairro'];
}

// Faixa de preço: prioriza min/max; se ausentes, usa a faixa predefinida
$precoMin = $filtros['preco_min'] !== '' ? (float)$filtros['preco_min'] : null;
$precoMax = $filtros['preco_max'] !== '' ? (float)$filtros['preco_max'] : null;

if ($precoMin !== null) {
    $whereParts[] = "preco >= ?";
    $params[] = $precoMin;
}
if ($precoMax !== null) {
    $whereParts[] = "preco <= ?";
    $params[] = $precoMax;
}

if ($precoMin === null && $precoMax === null && !empty($filtros['preco'])) {
    switch ($filtros['preco']) {
        case '1':
            $whereParts[] = "preco <= 200000";
            break;
        case '2':
            $whereParts[] = "preco BETWEEN 200000 AND 500000";
            break;
        case '3':
            $whereParts[] = "preco > 500000";
            break;
    }
}

if (!empty($filtros['quartos'])) {
    $whereParts[] = "quartos >= ?";
    $params[] = (int)$filtros['quartos'];
}

if (!empty($filtros['banheiros'])) {
    $whereParts[] = "banheiros >= ?";
    $params[] = (int)$filtros['banheiros'];
}

// Ordenação
$orderBy = 'created_at DESC';
switch ($filtros['ordenar']) {
    case 'preco_asc':
        $orderBy = 'preco ASC';
        break;
    case 'preco_desc':
        $orderBy = 'preco DESC';
        break;
    case 'area_desc':
        $orderBy = 'area DESC';
        break;
    default:
        $orderBy = 'created_at DESC';
}

// Paginação
$perPage = 12;
$page = $filtros['page'];
$offset = ($page - 1) * $perPage;

// Consultas SQL
$whereSql = !empty($whereParts) ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

// Total de registros
$sqlCount = "SELECT COUNT(*) AS total FROM imoveis" . $whereSql;
$stmt = $conn->prepare($sqlCount);
foreach ($params as $i => $value) {
    $stmt->bindValue($i + 1, $value);
}
$stmt->execute();
$total = (int)$stmt->fetchColumn();

// Consulta de resultados com ordenação e paginação
$sql = "SELECT * FROM imoveis" . $whereSql . " ORDER BY $orderBy LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
$stmt = $conn->prepare($sql);
foreach ($params as $i => $value) {
    $stmt->bindValue($i + 1, $value);
}
$stmt->execute();
$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter opções para filtros (combos)
$tipos = $conn->query("SELECT DISTINCT tipo FROM imoveis ORDER BY tipo")
              ->fetchAll(PDO::FETCH_ASSOC);

$cidades = $conn->query("SELECT DISTINCT cidade FROM imoveis ORDER BY cidade")
                ->fetchAll(PDO::FETCH_ASSOC);

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
        'loft' => '🏙️ Loft',
        'comercial' => '🏪 Comercial'
    ];
    
    return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
}

$bairros = [];
if (!empty($filtros['cidade'])) {
    $stmt = $conn->prepare("SELECT DISTINCT bairro FROM imoveis WHERE cidade = :cidade ORDER BY bairro");
    $stmt->execute([':cidade' => $filtros['cidade']]);
    $bairros = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Para paginação: total de páginas e função utilitária para montar URL com filtros
$totalPages = (int)ceil($total / $perPage);
$query = $_GET;
unset($query['page']);
$baseQueryString = http_build_query($query);
function buildPageUrl($p, $baseQueryString) {
    return 'busca.php?' . ($baseQueryString ? ($baseQueryString . '&') : '') . 'page=' . (int)$p;
}

include 'private/includes/header.php';
?>


    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Resultados da Busca</h1>
            <p>
                <?php if ($total > 0): ?>
                    Encontramos <?= $total ?> imóveis com os filtros selecionados
                <?php else: ?>
                    Nenhum imóvel encontrado
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Search Filters -->
    <section class="search-filters">
        <div class="container">
            <form action="busca.php" method="get" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group" style="flex:2">
                        <label for="q">Palavra‑chave</label>
                        <input type="text" id="q" name="q" placeholder="Título, endereço, bairro, cidade..." value="<?= htmlspecialchars($filtros['q']) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo">
                            <option value="">Todos os tipos</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= $tipo['tipo'] ?>" <?= $filtros['tipo'] == $tipo['tipo'] ? 'selected' : '' ?>>
                                    <?= formatar_tipo_imovel($tipo['tipo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="cidade">Cidade</label>
                        <select id="cidade" name="cidade">
                            <option value="">Todas</option>
                            <?php foreach ($cidades as $cidade): ?>
                                <option value="<?= $cidade['cidade'] ?>" <?= $filtros['cidade'] == $cidade['cidade'] ? 'selected' : '' ?>>
                                    <?= $cidade['cidade'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="bairro">Bairro</label>
                        <select id="bairro" name="bairro" <?= empty($bairros) ? 'disabled' : '' ?>>
                            <option value="">Todos</option>
                            <?php foreach ($bairros as $bairro): ?>
                                <option value="<?= $bairro['bairro'] ?>" <?= $filtros['bairro'] == $bairro['bairro'] ? 'selected' : '' ?>>
                                    <?= $bairro['bairro'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="preco">Faixa de Preço (rápida)</label>
                        <select id="preco" name="preco">
                            <option value="">Todas</option>
                            <option value="1" <?= $filtros['preco'] == '1' ? 'selected' : '' ?>>Até R$ 200.000</option>
                            <option value="2" <?= $filtros['preco'] == '2' ? 'selected' : '' ?>>R$ 200.000 - R$ 500.000</option>
                            <option value="3" <?= $filtros['preco'] == '3' ? 'selected' : '' ?>>Acima de R$ 500.000</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="preco_min">Preço mínimo</label>
                        <input type="number" id="preco_min" name="preco_min" min="0" step="1000" value="<?= htmlspecialchars($filtros['preco_min']) ?>" placeholder="Ex: 150000">
                    </div>
                    <div class="filter-group">
                        <label for="preco_max">Preço máximo</label>
                        <input type="number" id="preco_max" name="preco_max" min="0" step="1000" value="<?= htmlspecialchars($filtros['preco_max']) ?>" placeholder="Ex: 600000">
                    </div>
                    
                    <div class="filter-group">
                        <label for="quartos">Quartos</label>
                        <select id="quartos" name="quartos">
                            <option value="">Qualquer</option>
                            <option value="1" <?= $filtros['quartos'] == '1' ? 'selected' : '' ?>>1+</option>
                            <option value="2" <?= $filtros['quartos'] == '2' ? 'selected' : '' ?>>2+</option>
                            <option value="3" <?= $filtros['quartos'] == '3' ? 'selected' : '' ?>>3+</option>
                            <option value="4" <?= $filtros['quartos'] == '4' ? 'selected' : '' ?>>4+</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="banheiros">Banheiros</label>
                        <select id="banheiros" name="banheiros">
                            <option value="">Qualquer</option>
                            <option value="1" <?= $filtros['banheiros'] == '1' ? 'selected' : '' ?>>1+</option>
                            <option value="2" <?= $filtros['banheiros'] == '2' ? 'selected' : '' ?>>2+</option>
                            <option value="3" <?= $filtros['banheiros'] == '3' ? 'selected' : '' ?>>3+</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="ordenar">Ordenar por</label>
                        <select id="ordenar" name="ordenar">
                            <option value="recentes" <?= $filtros['ordenar'] === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                            <option value="preco_asc" <?= $filtros['ordenar'] === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
                            <option value="preco_desc" <?= $filtros['ordenar'] === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
                            <option value="area_desc" <?= $filtros['ordenar'] === 'area_desc' ? 'selected' : '' ?>>Maior área</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Aplicar Filtros</button>
                        <a href="imoveis.php" class="btn-clear">Limpar Filtros</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Search Results -->
    <section class="search-results">
        <div class="container">
            <?php if ($total > 0): ?>
                <p style="margin-bottom: 1rem;">
                    Mostrando <?= count($imoveis) > 0 ? ($offset + 1) : 0 ?>–<?= min($offset + count($imoveis), $total) ?> de <?= $total ?> resultados
                </p>
            <?php endif; ?>
            <?php if (empty($imoveis)): ?>
                <div class="no-results">
                    <i class="fas fa-home"></i>
                    <h3>Nenhum imóvel encontrado</h3>
                    <p>Não encontramos imóveis com os filtros selecionados. Tente ajustar sua busca.</p>
                    <a href="imoveis.php" class="btn">Ver Todos os Imóveis</a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($imoveis as $imovel): 
                        $imagens = array_filter(explode(',', $imovel['imagens']));
                        $firstImage = !empty($imagens) ? 'public/uploads/' . $imagens[0] : 'public/assets/images/default-property.jpg';
                        $preco_formatado = formatar_preco($imovel['preco']);
                    ?>
                        <div class="property-card">
                            <?php if ($imovel['destaque']): ?>
                                <div class="property-badge">Destaque</div>
                            <?php endif; ?>
                            
                            <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                            
                            <div class="property-info">
                                <h3><?= htmlspecialchars($imovel['titulo']) ?></h3>
                                <div class="property-type" style="margin-bottom: 8px;">
                                    <span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                        <?= formatar_tipo_imovel($imovel['tipo']) ?>
                                    </span>
                                </div>
                                <p class="property-address"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($imovel['bairro']) ?>, <?= htmlspecialchars($imovel['cidade']) ?></p>
                                
                                <div class="property-details">
                                    <?php if ($imovel['quartos'] > 0): ?>
                                        <span><i class="fas fa-bed"></i> <?= $imovel['quartos'] ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($imovel['banheiros'] > 0): ?>
                                        <span><i class="fas fa-bath"></i> <?= $imovel['banheiros'] ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($imovel['garagem'] > 0): ?>
                                        <span><i class="fas fa-car"></i> <?= $imovel['garagem'] ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($imovel['area'] > 0): ?>
                                        <span><i class="fas fa-vector-square"></i> <?= $imovel['area'] ?>m²</span>
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
                                
                                <p class="property-price"><?= $preco_formatado ?></p>
                                
                                <div class="property-actions">
                                    <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>" class="btn">Ver Detalhes</a>
                                    <a href="contato.php?imovel=<?= urlencode($imovel['titulo']) ?>" class="btn-contact">Contatar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Paginação" style="margin-top: 1.5rem; display:flex; gap:.5rem; flex-wrap:wrap;">
                        <?php 
                            $window = 5; 
                            $start = max(1, $page - floor($window/2));
                            $end = min($totalPages, $start + $window - 1);
                            $start = max(1, $end - $window + 1);
                        ?>
                        <a class="page-link <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $page > 1 ? buildPageUrl($page-1, $baseQueryString) : '#' ?>">Anterior</a>
                        <?php for ($p = $start; $p <= $end; $p++): ?>
                            <a class="page-link <?= $p == $page ? 'active' : '' ?>" href="<?= buildPageUrl($p, $baseQueryString) ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <a class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= $page < $totalPages ? buildPageUrl($page+1, $baseQueryString) : '#' ?>">Próxima</a>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php
        // Incluir o footer
        include 'private/includes/footer.php';
    ?>
    <script>
        // Atualizar dinamicamente os bairros quando a cidade muda
        document.getElementById('cidade').addEventListener('change', function() {
            const cidade = this.value;
            const bairroSelect = document.getElementById('bairro');
            
            if (!cidade) {
                bairroSelect.innerHTML = '<option value="">Todos</option>';
                bairroSelect.disabled = true;
                return;
            }
            
            // Fazer requisição AJAX para obter bairros
            fetch(`private/includes/api/bairros.php?cidade=${encodeURIComponent(cidade)}`)
                .then(response => response.json())
                .then(bairros => {
                    let options = '<option value="">Todos</option>';
                    bairros.forEach(bairro => {
                        const selected = bairro === '<?= $filtros["bairro"] ?>' ? 'selected' : '';
                        options += `<option value="${bairro}" ${selected}>${bairro}</option>`;
                    });
                    bairroSelect.innerHTML = options;
                    bairroSelect.disabled = false;
                });
        });
        
        // Disparar o evento change se já houver uma cidade selecionada
        <?php if (!empty($filtros['cidade'])): ?>
            document.getElementById('cidade').dispatchEvent(new Event('change'));
        <?php endif; ?>
    </script>