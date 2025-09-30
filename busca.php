<?php
require_once 'private/includes/db.php';

// Inicializar variáveis de filtro
$filtros = [
    'tipo' => isset($_GET['tipo']) ? $_GET['tipo'] : '',
    'cidade' => isset($_GET['cidade']) ? $_GET['cidade'] : '',
    'bairro' => isset($_GET['bairro']) ? $_GET['bairro'] : '',
    'preco' => isset($_GET['preco']) ? $_GET['preco'] : '',
    'quartos' => isset($_GET['quartos']) ? $_GET['quartos'] : '',
    'banheiros' => isset($_GET['banheiros']) ? $_GET['banheiros'] : ''
];

// Construir a consulta SQL
$sql = "SELECT * FROM imoveis WHERE 1=1";
$params = [];
$types = '';

// Aplicar filtros
if (!empty($filtros['tipo'])) {
    $sql .= " AND tipo = ?";
    $params[] = $filtros['tipo'];
    $types .= 's';
}

if (!empty($filtros['cidade'])) {
    $sql .= " AND cidade = ?";
    $params[] = $filtros['cidade'];
    $types .= 's';
}

if (!empty($filtros['bairro'])) {
    $sql .= " AND bairro = ?";
    $params[] = $filtros['bairro'];
    $types .= 's';
}

if (!empty($filtros['preco'])) {
    switch ($filtros['preco']) {
        case '1':
            $sql .= " AND preco <= 200000";
            break;
        case '2':
            $sql .= " AND preco BETWEEN 200000 AND 500000";
            break;
        case '3':
            $sql .= " AND preco > 500000";
            break;
    }
}

if (!empty($filtros['quartos'])) {
    $sql .= " AND quartos >= ?";
    $params[] = $filtros['quartos'];
    $types .= 'i';
}

if (!empty($filtros['banheiros'])) {
    $sql .= " AND banheiros >= ?";
    $params[] = $filtros['banheiros'];
    $types .= 'i';
}

$sql .= " ORDER BY created_at DESC";

// Executar a consulta
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    // Bind de parâmetros no PDO
    foreach ($params as $key => $value) {
        // ':param1', ':param2', ... ou '?', dependendo do seu SQL
        $stmt->bindValue($key + 1, $value); // se usar ? no SQL
    }
}

$stmt->execute();

// Pega todos os resultados como array associativo
$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Obter opções para filtros
$tipos = $conn->query("SELECT DISTINCT tipo FROM imoveis ORDER BY tipo")
              ->fetchAll(PDO::FETCH_ASSOC);

$cidades = $conn->query("SELECT DISTINCT cidade FROM imoveis ORDER BY cidade")
                ->fetchAll(PDO::FETCH_ASSOC);

$bairros = [];
if (!empty($filtros['cidade'])) {
    $stmt = $conn->prepare("SELECT DISTINCT bairro FROM imoveis WHERE cidade = :cidade ORDER BY bairro");
    $stmt->execute([':cidade' => $filtros['cidade']]);
    $bairros = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



include 'private/includes/header.php';
?>


    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Resultados da Busca</h1>
            <p>Encontramos <?= count($imoveis) ?> imóveis com os filtros selecionados</p>
        </div>
    </section>

    <!-- Search Filters -->
    <section class="search-filters">
        <div class="container">
            <form action="busca.php" method="get" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo">
                            <option value="">Todos</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= $tipo['tipo'] ?>" <?= $filtros['tipo'] == $tipo['tipo'] ? 'selected' : '' ?>>
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
                        <label for="preco">Faixa de Preço</label>
                        <select id="preco" name="preco">
                            <option value="">Todas</option>
                            <option value="1" <?= $filtros['preco'] == '1' ? 'selected' : '' ?>>Até R$ 200.000</option>
                            <option value="2" <?= $filtros['preco'] == '2' ? 'selected' : '' ?>>R$ 200.000 - R$ 500.000</option>
                            <option value="3" <?= $filtros['preco'] == '3' ? 'selected' : '' ?>>Acima de R$ 500.000</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="quartos">Mín. Quartos</label>
                        <select id="quartos" name="quartos">
                            <option value="">Qualquer</option>
                            <option value="1" <?= $filtros['quartos'] == '1' ? 'selected' : '' ?>>1+</option>
                            <option value="2" <?= $filtros['quartos'] == '2' ? 'selected' : '' ?>>2+</option>
                            <option value="3" <?= $filtros['quartos'] == '3' ? 'selected' : '' ?>>3+</option>
                            <option value="4" <?= $filtros['quartos'] == '4' ? 'selected' : '' ?>>4+</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="banheiros">Mín. Banheiros</label>
                        <select id="banheiros" name="banheiros">
                            <option value="">Qualquer</option>
                            <option value="1" <?= $filtros['banheiros'] == '1' ? 'selected' : '' ?>>1+</option>
                            <option value="2" <?= $filtros['banheiros'] == '2' ? 'selected' : '' ?>>2+</option>
                            <option value="3" <?= $filtros['banheiros'] == '3' ? 'selected' : '' ?>>3+</option>
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
                        $preco_formatado = 'R$ ' . number_format($imovel['preco'], 2, ',', '.');
                    ?>
                        <div class="property-card">
                            <?php if ($imovel['destaque']): ?>
                                <div class="property-badge">Destaque</div>
                            <?php endif; ?>
                            
                            <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                            
                            <div class="property-info">
                                <h3><?= htmlspecialchars($imovel['titulo']) ?></h3>
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