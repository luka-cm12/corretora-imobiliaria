<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/auth.php');
require_login();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/*function require_login() {
    if (!isset($_SESSION['usuario_id'])) {
        // Redireciona para a página de login se não estiver logado
        header("Location: login.php");
        exit;
    }
}*/

/*function login($usuario_id) {
    $_SESSION['usuario_id'] = $usuario_id;
}

/*function logout() {
    session_destroy();
    header("Location: login.php");
    exit;
}*/
?>

<?php
// Função para formatar datas no formato brasileiro
function formatar_data($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Estatísticas para o dashboard
$total_imoveis   = db_query("SELECT COUNT(*) as total FROM imoveis")->fetch_assoc()['total'];
$imoveis_destaque = db_query("SELECT COUNT(*) as total FROM imoveis WHERE destaque = 1")->fetch_assoc()['total'];
$imoveis_venda    = db_query("SELECT COUNT(*) as total FROM imoveis WHERE tipo IN ('casa', 'apartamento', 'terreno')")->fetch_assoc()['total'];
$imoveis_locacao  = db_query("SELECT COUNT(*) as total FROM imoveis WHERE tipo = 'comercial'")->fetch_assoc()['total'];

// Últimos imóveis cadastrados
$ultimos_imoveis = db_query("SELECT id, titulo, cidade, created_at FROM imoveis ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Últimos contatos recebidos
$ultimos_contatos = db_query("SELECT nome, email, assunto, created_at FROM contatos ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Incluir header administrativo
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Dashboard</h1>
    
    <!-- Cards de estatísticas -->
    <div class="stats-grid">
        <div class="stat-card card-total">
            <div class="stat-icon"><i class="fas fa-home"></i></div>
            <div class="stat-info">
                <h3>Total de Imóveis</h3>
                <p><?= $total_imoveis ?></p>
            </div>
        </div>
        <div class="stat-card card-destaque">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <h3>Em Destaque</h3>
                <p><?= $imoveis_destaque ?></p>
            </div>
        </div>
        <div class="stat-card card-venda">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>Para Venda</h3>
                <p><?= $imoveis_venda ?></p>
            </div>
        </div>
        <div class="stat-card card-locacao">
            <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-info">
                <h3>Para Locação</h3>
                <p><?= $imoveis_locacao ?></p>
            </div>
        </div>
    </div>
    
    <!-- Atividades recentes -->
    <div class="recent-activity">
        <div class="activity-section">
            <h2>Últimos Imóveis Cadastrados</h2>
            <div class="activity-list">
                <?php foreach ($ultimos_imoveis as $imovel): ?>
                    <div class="activity-item">
                        <div class="activity-icon"><i class="fas fa-home"></i></div>
                        <div class="activity-details">
                            <p>
                                <a href="../imoveis/editar.php?id=<?= urlencode($imovel['id']) ?>">
                                    <?= htmlspecialchars($imovel['titulo']) ?>
                                </a> - <?= htmlspecialchars($imovel['cidade']) ?>
                            </p>
                            <span class="activity-time"><?= formatar_data($imovel['created_at']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="activity-section">
            <h2>Últimos Contatos Recebidos</h2>
            <div class="activity-list">
                <?php foreach ($ultimos_contatos as $contato): ?>
                    <div class="activity-item">
                        <div class="activity-icon"><i class="fas fa-envelope"></i></div>
                        <div class="activity-details">
                            <p><strong><?= htmlspecialchars($contato['nome']) ?></strong> - <?= htmlspecialchars($contato['assunto']) ?></p>
                            <span class="activity-time"><?= formatar_data($contato['created_at']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- CSS rápido para o dashboard -->
<style>
.admin-content { padding: 20px; font-family: Arial, sans-serif; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
.stat-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); display: flex; align-items: center; }
.stat-icon { font-size: 2rem; margin-right: 15px; color: #4CAF50; }
.stat-info h3 { margin: 0 0 5px; font-size: 1rem; }
.stat-info p { margin: 0; font-weight: bold; font-size: 1.2rem; }
.card-destaque .stat-icon { color: #FF9800; }
.card-venda .stat-icon { color: #2196F3; }
.card-locacao .stat-icon { color: #9C27B0; }

.recent-activity { display: flex; flex-wrap: wrap; gap: 30px; }
.activity-section { flex: 1; min-width: 300px; }
.activity-list { background: #fff; border-radius: 10px; padding: 15px; max-height: 400px; overflow-y: auto; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
.activity-item { display: flex; margin-bottom: 15px; }
.activity-icon { font-size: 1.5rem; margin-right: 10px; color: #4CAF50; }
.activity-time { font-size: 0.8rem; color: #777; }
.activity-item a { text-decoration: none; color: #333; }
.activity-item a:hover { text-decoration: underline; }
</style>

<?php
include __DIR__ . '/../includes/admin-footer.php';
?>
