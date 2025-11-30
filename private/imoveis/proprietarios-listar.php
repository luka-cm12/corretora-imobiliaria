<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

$error = '';
$success = '';

// CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// DEBUG: Log de todas as tentativas de exclusão
$debug_info = '';

// Processar exclusão
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $debug_info .= "🔍 INICIANDO PROCESSO DE EXCLUSÃO...\n";
    $debug_info .= "• Action: " . ($_POST['action'] ?? 'N/A') . "\n";
    $debug_info .= "• ID recebido: " . ($_POST['id'] ?? 'N/A') . "\n";
    
    // Verificar CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    $debug_info .= "• CSRF POST: " . (!empty($csrf) ? 'Presente' : 'Ausente') . "\n";
    $debug_info .= "• CSRF Session: " . (!empty($_SESSION['csrf_token']) ? 'Presente' : 'Ausente') . "\n";
    
    if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Token de segurança inválido. Atualize a página e tente novamente.';
        $debug_info .= "❌ CSRF FALHOU\n";
    } else {
        $debug_info .= "✅ CSRF OK\n";
        
        $id = (int)$_POST['id'];
        $debug_info .= "• ID processado: {$id}\n";
        
        if ($id <= 0) {
            $error = 'ID do proprietário inválido.';
            $debug_info .= "❌ ID INVÁLIDO\n";
        } else {
            try {
                $debug_info .= "🔍 Verificando imóveis associados...\n";
                
                // Verificar se o proprietário tem imóveis associados
                $imoveis_result = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id]);
                $imoveis_count = $imoveis_result[0]['count'];
                $debug_info .= "• Imóveis encontrados: {$imoveis_count}\n";
                
                if ($imoveis_count > 0) {
                    $error = "Não é possível excluir este proprietário pois ele possui {$imoveis_count} imóvel(is) cadastrado(s).";
                    $debug_info .= "❌ NÃO PODE EXCLUIR - TEM IMÓVEIS\n";
                } else {
                    $debug_info .= "✅ PODE EXCLUIR - SEM IMÓVEIS\n";
                    $debug_info .= "🗑️ Executando DELETE...\n";
                    
                    // Antes da exclusão, verificar se o proprietário existe
                    $prop_check = db_query("SELECT id_proprietario, nome FROM proprietarios WHERE id_proprietario = ?", [$id]);
                    if (empty($prop_check)) {
                        $debug_info .= "❌ PROPRIETÁRIO NÃO ENCONTRADO ANTES DA EXCLUSÃO\n";
                        $error = 'Proprietário não encontrado.';
                    } else {
                        $debug_info .= "✅ Proprietário encontrado: " . $prop_check[0]['nome'] . "\n";
                        
                        // Executar a exclusão
                        $debug_info .= "📋 SQL: DELETE FROM proprietarios WHERE id_proprietario = {$id}\n";
                        $linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id]);
                        $debug_info .= "📊 Linhas afetadas: {$linhas_afetadas}\n";
                        
                        if ($linhas_afetadas > 0) {
                            $success = 'Proprietário excluído com sucesso!';
                            $debug_info .= "✅ SUCESSO! EXCLUSÃO REALIZADA\n";
                            
                            // Verificar se realmente foi excluído
                            $verificar = db_query("SELECT COUNT(*) as count FROM proprietarios WHERE id_proprietario = ?", [$id])[0]['count'];
                            if ($verificar == 0) {
                                $debug_info .= "✅ CONFIRMADO - PROPRIETÁRIO NÃO EXISTE MAIS\n";
                            } else {
                                $debug_info .= "❌ ERRO - PROPRIETÁRIO AINDA EXISTE!\n";
                            }
                            
                            // Redirecionar para evitar reenvio do formulário
                            header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1&debug=" . urlencode($debug_info));
                            exit;
                        } else {
                            $error = 'Proprietário não encontrado ou não foi possível excluir.';
                            $debug_info .= "❌ FALHA - NENHUMA LINHA AFETADA\n";
                        }
                    }
                }
            } catch (Exception $e) {
                $error = 'Erro ao excluir proprietário: ' . $e->getMessage();
                $debug_info .= "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
                $debug_info .= "Stack trace: " . $e->getTraceAsString() . "\n";
            }
        }
    }
}

// Verificar se houve exclusão bem-sucedida (via redirecionamento)
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $success = 'Proprietário excluído com sucesso!';
    if (isset($_GET['debug'])) {
        $debug_info = urldecode($_GET['debug']);
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
    $where_sql = "WHERE nome LIKE ? OR cpf LIKE ? OR cnpj LIKE ? OR email LIKE ? OR telefone LIKE ?";
    $search_term = '%' . $search . '%';
    $params = [$search_term, $search_term, $search_term, $search_term, $search_term];
}

// Contar total de registros
$count_sql = "SELECT COUNT(*) as total FROM proprietarios " . $where_sql;
$total_records = db_query($count_sql, $params)[0]['total'];
$total_pages = ceil($total_records / $per_page);

// Buscar proprietários
try {
    $sql = "SELECT p.*, 
                   (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
            FROM proprietarios p 
            {$where_sql}
            ORDER BY p.nome ASC 
            LIMIT {$per_page} OFFSET {$offset}";

    $proprietarios = db_query($sql, $params);
} catch (Exception $e) {
    // Se der erro, pode ser que as colunas não existam ainda - usar só as básicas
    $sql_basico = "SELECT p.*, 
                          (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
                   FROM proprietarios p 
                   {$where_sql}
                   ORDER BY p.nome ASC 
                   LIMIT {$per_page} OFFSET {$offset}";
    
    $proprietarios = db_query($sql_basico, $params);
    
    // Adicionar campos vazios para compatibilidade
    foreach ($proprietarios as &$prop) {
        if (!isset($prop['tipo_documento'])) $prop['tipo_documento'] = 'cpf';
        if (!isset($prop['cnpj'])) $prop['cnpj'] = '';
    }
}

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

.btn-delete:disabled {
    background: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-delete:disabled:hover {
    background: #6c757d;
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

/* Alertas de sucesso e erro */
.alert {
    padding: 15px;
    margin: 20px 0;
    border: 1px solid transparent;
    border-radius: 5px;
    position: relative;
}

.alert-success {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
    color: #155724 !important;
}

.alert-danger {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #721c24 !important;
}

.alert i {
    margin-right: 8px;
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
    
    .alert {
        margin: 10px 0;
        padding: 12px;
    }
}
</style>

<div class="breadcrumb">
    <a href="../admin/dashboard.php">Dashboard</a> / <span>Proprietários</span>
</div>

<!-- DEBUG INFO -->
<?php if (!empty($debug_info)): ?>
<div style="background: #e7f3ff; padding: 15px; margin: 15px 0; border-left: 4px solid #2196F3; border-radius: 5px; font-family: monospace; white-space: pre-line; font-size: 13px;">
<strong>🔍 DEBUG DA EXCLUSÃO:</strong>
<?= htmlspecialchars($debug_info) ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 5px;">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Erro:</strong> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 5px;">
        <i class="fas fa-check-circle"></i>
        <strong>Sucesso:</strong> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Estatísticas -->
<?php
try {
    $stats = db_query("
        SELECT 
            COUNT(*) as total_proprietarios,
            COUNT(CASE WHEN cpf IS NOT NULL AND cpf != '' THEN 1 END) as com_cpf,
            COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as com_email,
            (SELECT COUNT(DISTINCT id_proprietario) FROM imoveis) as com_imoveis
    ")[0];
} catch (Exception $e) {
    // Fallback se der erro
    $stats = [
        'total_proprietarios' => 0,
        'com_cpf' => 0, 
        'com_email' => 0,
        'com_imoveis' => 0
    ];
}
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
                <th>Documento</th>
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
                            <div><small>CPF:</small><br><?= htmlspecialchars($proprietario['cpf']) ?></div>
                        <?php elseif (!empty($proprietario['cnpj'])): ?>
                            <div><small>CNPJ:</small><br><?= htmlspecialchars($proprietario['cnpj']) ?></div>
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
                        <?php if (isset($proprietario['data_cadastro'])): ?>
                            <?= date('d/m/Y', strtotime($proprietario['data_cadastro'])) ?>
                        <?php elseif (isset($proprietario['created_at'])): ?>
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
                                  onsubmit="return confirmarExclusao(this, '<?= htmlspecialchars($proprietario['nome']) ?>')"
                                  class="form-exclusao">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= $proprietario['id_proprietario'] ?>">
                                <?php if ($proprietario['total_imoveis'] > 0): ?>
                                    <button type="button" 
                                            class="btn-sm btn-delete" 
                                            disabled
                                            title="Não é possível excluir: proprietário possui <?= $proprietario['total_imoveis'] ?> imóvel(is) cadastrado(s)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" 
                                            class="btn-sm btn-delete" 
                                            title="Excluir proprietário">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
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

<script>
function confirmarExclusao(form, nome) {
    // Verificar se os campos necessários estão preenchidos
    const action = form.querySelector('input[name="action"]');
    const id = form.querySelector('input[name="id"]');
    const csrf = form.querySelector('input[name="csrf_token"]');
    
    if (!action || !id || !csrf) {
        alert('Erro: Dados do formulário incompletos.');
        return false;
    }
    
    if (!action.value || !id.value || !csrf.value) {
        alert('Erro: Dados do formulário vazios.');
        return false;
    }
    
    // Confirmar exclusão
    const confirmar = confirm(`Tem certeza que deseja excluir o proprietário "${nome}"?\n\nEsta ação não pode ser desfeita.`);
    
    if (confirmar) {
        // Mostrar loading
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
        }
    }
    
    return confirmar;
}

// Remover parâmetro deleted da URL após mostrar mensagem
if (window.location.search.includes('deleted=1')) {
    const url = new URL(window.location);
    url.searchParams.delete('deleted');
    window.history.replaceState({}, document.title, url.pathname + url.search);
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>