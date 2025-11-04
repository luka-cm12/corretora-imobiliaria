<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

$error = '';
$success = '';

// Processar exclusão
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        // Verificar se o proprietário tem imóveis associados
        $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id])[0]['count'];
        
        if ($imoveis_count > 0) {
            $error = "Não é possível excluir este proprietário pois ele possui {$imoveis_count} imóvel(is) cadastrado(s).";
        } else {
            $result = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id]);
            if ($result) {
                $success = 'Proprietário excluído com sucesso!';
            } else {
                $error = 'Erro ao excluir proprietário.';
            }
        }
    } catch (Exception $e) {
        $error = 'Erro ao excluir proprietário: ' . $e->getMessage();
    }
}

// Parâmetros de busca e paginação
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Construir query de busca
$where_sql = '';
$params = [];

if (!empty($search)) {
    $where_sql = "WHERE nome LIKE ? OR cpf LIKE ? OR email LIKE ? OR telefone LIKE ?";
    $search_term = '%' . $search . '%';
    $params = [$search_term, $search_term, $search_term, $search_term];
}

// Contar total de registros
$count_sql = "SELECT COUNT(*) as total FROM proprietarios " . $where_sql;
$total_records = db_query($count_sql, $params)[0]['total'];
$total_pages = ceil($total_records / $per_page);

// Buscar proprietários
$sql = "SELECT p.*, 
               (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
        FROM proprietarios p 
        {$where_sql}
        ORDER BY p.nome ASC 
        LIMIT {$per_page} OFFSET {$offset}";

$proprietarios = db_query($sql, $params);

$page_title = 'Gerenciar Proprietários | Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.proprietarios-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex: 1;
    max-width: 500px;
}

.search-form input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.search-form button {
    padding: 10px 15px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    white-space: nowrap;
}

.search-form button:hover {
    background: #0056b3;
}

.btn-add {
    background: #28a745;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
    white-space: nowrap;
}

.btn-add:hover {
    background: #218838;
    text-decoration: none;
    color: white;
}

.proprietarios-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.proprietarios-table th {
    background: #f8f9fa;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
}

.proprietarios-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

.proprietarios-table tr:hover {
    background-color: #f8f9fa;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-edit {
    background: #17a2b8;
    color: white;
}

.btn-edit:hover {
    background: #138496;
    color: white;
    text-decoration: none;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
}

.pagination {
    display: flex;
    justify-content: center;
    margin: 30px 0;
    gap: 5px;
}

.pagination a, .pagination span {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    color: #007bff;
    text-decoration: none;
    border-radius: 4px;
}

.pagination a:hover {
    background: #e9ecef;
    text-decoration: none;
}

.pagination .current {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 32px;
    color: #007bff;
}

.stat-card p {
    margin: 0;
    color: #6c757d;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.empty-state h3 {
    color: #6c757d;
    margin-bottom: 15px;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 25px;
}

@media (max-width: 768px) {
    .proprietarios-table {
        font-size: 14px;
    }
    
    .proprietarios-table th,
    .proprietarios-table td {
        padding: 8px;
    }
    
    .proprietarios-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-form {
        max-width: none;
    }
}
</style>

<div class="breadcrumb">
    <a href="../admin/dashboard.php">Dashboard</a> / <span>Proprietários</span>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Estatísticas -->
<?php
$stats = db_query("
    SELECT 
        COUNT(*) as total_proprietarios,
        COUNT(CASE WHEN cpf IS NOT NULL AND cpf != '' THEN 1 END) as com_cpf,
        COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as com_email,
        (SELECT COUNT(DISTINCT id_proprietario) FROM imoveis) as com_imoveis
")[0];
?>

<div class="stats-cards">
    <div class="stat-card">
        <h3><?= $stats['total_proprietarios'] ?></h3>
        <p>Total de Proprietários</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['com_cpf'] ?></h3>
        <p>Com CPF Cadastrado</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['com_email'] ?></h3>
        <p>Com Email Cadastrado</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['com_imoveis'] ?></h3>
        <p>Com Imóveis Ativos</p>
    </div>
</div>

<!-- Cabeçalho com busca e botão adicionar -->
<div class="proprietarios-header">
    <form method="GET" class="search-form">
        <input type="text" 
               name="search" 
               placeholder="Buscar por nome, CPF, email ou telefone..." 
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit">
            <i class="fas fa-search"></i> Buscar
        </button>
        <?php if (!empty($search)): ?>
            <a href="?" class="btn-sm btn-edit">
                <i class="fas fa-times"></i> Limpar
            </a>
        <?php endif; ?>
    </form>
    
    <a href="proprietario-cadastrar.php" class="btn-add">
        <i class="fas fa-plus"></i> Novo Proprietário
    </a>
</div>

<?php if (empty($proprietarios)): ?>
    <div class="empty-state">
        <?php if (!empty($search)): ?>
            <i class="fas fa-search" style="font-size: 48px; color: #dee2e6; margin-bottom: 20px;"></i>
            <h3>Nenhum proprietário encontrado</h3>
            <p>Não encontramos proprietários com o termo "<?= htmlspecialchars($search) ?>".</p>
            <a href="?" class="btn-add">Ver Todos os Proprietários</a>
        <?php else: ?>
            <i class="fas fa-users" style="font-size: 48px; color: #dee2e6; margin-bottom: 20px;"></i>
            <h3>Nenhum proprietário cadastrado</h3>
            <p>Comece cadastrando o primeiro proprietário do sistema.</p>
            <a href="proprietario-cadastrar.php" class="btn-add">Cadastrar Primeiro Proprietário</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <table class="proprietarios-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Contato</th>
                <th>Imóveis</th>
                <th>Cadastrado em</th>
                <th width="120">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proprietarios as $proprietario): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($proprietario['nome']) ?></strong>
                    </td>
                    <td>
                        <?php if (!empty($proprietario['cpf'])): ?>
                            <?= htmlspecialchars($proprietario['cpf']) ?>
                        <?php else: ?>
                            <span class="badge badge-warning">Não informado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($proprietario['telefone'])): ?>
                            <div><i class="fas fa-phone"></i> <?= htmlspecialchars($proprietario['telefone']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($proprietario['email'])): ?>
                            <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($proprietario['email']) ?></div>
                        <?php endif; ?>
                        <?php if (empty($proprietario['telefone']) && empty($proprietario['email'])): ?>
                            <span class="badge badge-warning">Não informado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($proprietario['total_imoveis'] > 0): ?>
                            <span class="badge badge-success">
                                <?= $proprietario['total_imoveis'] ?> imóvel(is)
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning">Nenhum imóvel</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($proprietario['created_at'])): ?>
                            <?= date('d/m/Y', strtotime($proprietario['created_at'])) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="proprietario-editar.php?id=<?= $proprietario['id_proprietario'] ?>" 
                               class="btn-sm btn-edit" 
                               title="Editar proprietário">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <form method="POST" 
                                  style="display: inline;" 
                                  onsubmit="return confirm('Tem certeza que deseja excluir este proprietário?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $proprietario['id_proprietario'] ?>">
                                <button type="submit" 
                                        class="btn-sm btn-delete" 
                                        title="Excluir proprietário"
                                        <?= $proprietario['total_imoveis'] > 0 ? 'disabled title="Não é possível excluir: possui imóveis cadastrados"' : '' ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                    Próxima <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <p style="text-align: center; color: #6c757d; margin-top: 15px;">
            Mostrando <?= ($offset + 1) ?> a <?= min($offset + $per_page, $total_records) ?> 
            de <?= $total_records ?> proprietário(s)
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>