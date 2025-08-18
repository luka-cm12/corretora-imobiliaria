<?php
require_once 'private/includes/db.php';
require_once 'private/includes/functions.php';

// Definir variáveis para o header
$page_title = 'Imóveis Disponíveis | Corretora Base';
$meta_description = 'Confira nossa seleção de imóveis disponíveis para compra e aluguel na Corretora Base. Encontre o lar dos seus sonhos!';
$load_lightbox = true;

// Processar filtros
$filtros = [];
$where = [];
$params = [];
$types = '';

// Filtro por tipo
if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $filtros['tipo'] = $_GET['tipo'];
    $where[] = "tipo = ?";
    $params[] = $filtros['tipo'];
    $types .= 's';
}

// Filtro por cidade
if (isset($_GET['cidade']) && !empty($_GET['cidade'])) {
    $filtros['cidade'] = $_GET['cidade'];
    $where[] = "cidade = ?";
    $params[] = $filtros['cidade'];
    $types .= 's';
}

// Filtro por bairro
if (isset($_GET['bairro']) && !empty($_GET['bairro'])) {
    $filtros['bairro'] = $_GET['bairro'];
    $where[] = "bairro = ?";
    $params[] = $filtros['bairro'];
    $types .= 's';
}

// Filtro por preço
if (isset($_GET['preco']) && !empty($_GET['preco'])) {
    $filtros['preco'] = $_GET['preco'];
    switch ($_GET['preco']) {
        case '1':
            $where[] = "preco <= 200000";
            break;
        case '2':
            $where[] = "preco BETWEEN 200000 AND 500000";
            break;
        case '3':
            $where[] = "preco > 500000";
            break;
    }
}

// Filtro por quartos
if (isset($_GET['quartos']) && !empty($_GET['quartos'])) {
    $filtros['quartos'] = $_GET['quartos'];
    $where[] = "quartos >= ?";
    $params[] = $filtros['quartos'];
    $types .= 'i';
}

// Filtro por banheiros
if (isset($_GET['banheiros']) && !empty($_GET['banheiros'])) {
    $filtros['banheiros'] = $_GET['banheiros'];
    $where[] = "banheiros >= ?";
    $params[] = $filtros['banheiros'];
    $types .= 'i';
}

// Construir a consulta SQL
$sql = "SELECT * FROM imoveis";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC";

// Executar a consulta
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$imoveis = $result->fetch_all(MYSQLI_ASSOC);

// Obter opções para filtros
$tipos = db_query("SELECT DISTINCT tipo FROM imoveis ORDER BY tipo")->fetch_all(MYSQLI_ASSOC);
$cidades = db_query("SELECT DISTINCT cidade FROM imoveis ORDER BY cidade")->fetch_all(MYSQLI_ASSOC);
$bairros = !empty($filtros['cidade']) ? 
    db_query("SELECT DISTINCT bairro FROM imoveis WHERE cidade = ? ORDER BY bairro", [$filtros['cidade']])->fetch_all(MYSQLI_ASSOC) : 
    [];

// Incluir o header
include 'private/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Nossos Imóveis</h1>
        <p>Encontre o imóvel perfeito para você</p>
    </div>
</section>

<!-- Property Filters -->
<section class="property-filters">
    <div class="container">
        <form action="imoveis.php" method="get" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['tipo'] ?>" <?= isset($filtros['tipo']) && $filtros['tipo'] == $tipo['tipo'] ? 'selected' : '' ?>>
                                <?= ucfirst($tipo['tipo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="cidade">Cidade</label>
                    <select id="cidade" name="cidade">
                        <option value="">Todas</option>
                        <?php foreach ($cidades as $cidade): ?>
                            <option value="<?= $cidade['cidade'] ?>" <?= isset($filtros['cidade']) && $filtros['cidade'] == $cidade['cidade'] ? 'selected' : '' ?>>
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
                            <option value="<?= $bairro['bairro'] ?>" <?= isset($filtros['bairro']) && $filtros['bairro'] == $bairro['bairro'] ? 'selected' : '' ?>>
                                <?= $bairro['bairro'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="preco">Faixa de Preço</label>
                    <select id="preco" name="preco">
                        <option value="">Todas</option>
                        <option value="1" <?= isset($filtros['preco']) && $filtros['preco'] == '1' ? 'selected' : '' ?>>Até R$ 200.000</option>
                        <option value="2" <?= isset($filtros['preco']) && $filtros['preco'] == '2' ? 'selected' : '' ?>>R$ 200.000 - R$ 500.000</option>
                        <option value="3" <?= isset($filtros['preco']) && $filtros['preco'] == '3' ? 'selected' : '' ?>>Acima de R$ 500.000</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="quartos">Mín. Quartos</label>
                    <select id="quartos" name="quartos">
                        <option value="">Qualquer</option>
                        <option value="1" <?= isset($filtros['quartos']) && $filtros['quartos'] == '1' ? 'selected' : '' ?>>1+</option>
                        <option value="2" <?= isset($filtros['quartos']) && $filtros['quartos'] == '2' ? 'selected' : '' ?>>2+</option>
                        <option value="3" <?= isset($filtros['quartos']) && $filtros['quartos'] == '3' ? 'selected' : '' ?>>3+</option>
                        <option value="4" <?= isset($filtros['quartos']) && $filtros['quartos'] == '4' ? 'selected' : '' ?>>4+</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="banheiros">Mín. Banheiros</label>
                    <select id="banheiros" name="banheiros">
                        <option value="">Qualquer</option>
                        <option value="1" <?= isset($filtros['banheiros']) && $filtros['banheiros'] == '1' ? 'selected' : '' ?>>1+</option>
                        <option value="2" <?= isset($filtros['banheiros']) && $filtros['banheiros'] == '2' ? 'selected' : '' ?>>2+</option>
                        <option value="3" <?= isset($filtros['banheiros']) && $filtros['banheiros'] == '3' ? 'selected' : '' ?>>3+</option>
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

<!-- Property List -->
<section class="property-list">
    <div class="container">
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
                    $imagens = explode(',', $imovel['imagens']);
                    $firstImage = !empty($imagens) ? 'uploads/' . $imagens[0] : 'assets/images/default-property.jpg';
                    $preco_formatado = formatar_preco($imovel['preco']);
                ?>
                    <div class="property-card">
                        <?php if ($imovel['destaque']): ?>
                            <div class="property-badge">Destaque</div>
                        <?php endif; ?>
                        
                        <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>">
                            <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                        </a>
                        
                        <div class="property-info">
                            <h3><a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>"><?= htmlspecialchars($imovel['titulo']) ?></a></h3>
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
        <?php endif; ?>
    </div>
</section>

<?php
// Incluir o footer
include 'private/includes/footer.php';
?>