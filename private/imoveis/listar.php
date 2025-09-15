<?php
require_once(__DIR__ . '/../includes/auth.php');
//require_login();

require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

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
$total_query = "SELECT COUNT(*) as total FROM imoveis" . $filtro;
$stmt = db_query($total_query, $params, $types);
$total_imoveis = $stmt[0]['total'] ?? 0; // pega o primeiro elemento do array
$total_paginas = ceil($total_imoveis / $por_pagina);

// Obter imóveis
$query = "SELECT * FROM imoveis" . $filtro . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $por_pagina;
$params[] = $offset;
$types .= 'ii';

$imoveis = db_query($query, $params, $types); // já é array

$page_title = 'Listar Imóveis | Corretora Base';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>
    
    <div class="main-content">
        <header class="admin-header">
            <div class="header-left">
                <h2>Lista de Imóveis</h2>
            </div>
            <div class="header-right">
                <span class="welcome">Bem-vindo, <?= $_SESSION['admin_username'] ?></span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>
        
        <div class="content">
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
                                <th>ID</th>
                                <th>Imagem</th>
                                <th>Título</th>
                                <th>Localização</th>
                                <th>Preço</th>
                                <th>Destaque</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($imoveis as $imovel): 
                                $imagens = explode(',', $imovel['imagens']);
                                $firstImage = !empty($imagens) ? '../../public/uploads/' . $imagens[0] : '../../assets/images/default-property.jpg';
                                $preco_formatado = formatar_preco($imovel['preco']);
                            ?>
                                <tr>
                                    <td><?= $imovel['id'] ?></td>
                                    <td>
                                        <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>" class="thumbnail">
                                    </td>
                                    <td><?= htmlspecialchars($imovel['titulo']) ?></td>
                                    <td><?= htmlspecialchars($imovel['bairro']) ?>, <?= htmlspecialchars($imovel['cidade']) ?></td>
                                    <td><?= $preco_formatado ?></td>
                                    <td>
                                        <?php if ($imovel['destaque']): ?>
                                            <span class="badge success">Sim</span>
                                        <?php else: ?>
                                            <span class="badge">Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="editar.php?id=<?= $imovel['id'] ?>" class="btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="excluir.php?id=<?= $imovel['id'] ?>" class="btn-delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este imóvel?')"><i class="fas fa-trash"></i></a>
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

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>