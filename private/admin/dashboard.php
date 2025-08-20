<?php
require_once 'private/includes/auth.php';
require_once 'private/includes/db.php';
require_login();

// Função para formatar datas no formato brasileiro
function formatar_data($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Estatísticas para o dashboard
$total_imoveis = db_query("SELECT COUNT(*) as total FROM imoveis")->fetch_assoc()['total'];
$imoveis_destaque = db_query("SELECT COUNT(*) as total FROM imoveis WHERE destaque = 1")->fetch_assoc()['total'];
$imoveis_venda = db_query("SELECT COUNT(*) as total FROM imoveis WHERE tipo IN ('casa', 'apartamento', 'terreno')")->fetch_assoc()['total'];
$imoveis_locacao = db_query("SELECT COUNT(*) as total FROM imoveis WHERE tipo = 'comercial'")->fetch_assoc()['total'];

// Últimos imóveis cadastrados
$ultimos_imoveis = db_query("SELECT id, titulo, cidade, created_at FROM imoveis ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Últimos contatos recebidos
$ultimos_contatos = db_query("SELECT nome, email, assunto, created_at FROM contatos ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Incluir header administrativo
include 'private/includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-info">
                <h3>Total de Imóveis</h3>
                <p><?= $total_imoveis ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3>Em Destaque</h3>
                <p><?= $imoveis_destaque ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3>Para Venda</h3>
                <p><?= $imoveis_venda ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="stat-info">
                <h3>Para Locação</h3>
                <p><?= $imoveis_locacao ?></p>
            </div>
        </div>
    </div>
    
    <div class="recent-activity">
        <div class="activity-section">
            <h2>Últimos Imóveis Cadastrados</h2>
            <div class="activity-list">
                <?php foreach ($ultimos_imoveis as $imovel): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="activity-details">
                            <p><a href="../imoveis/editar.php?id=<?= $imovel['id'] ?>"><?= htmlspecialchars($imovel['titulo']) ?></a> - <?= htmlspecialchars($imovel['cidade']) ?></p>
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
                        <div class="activity-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
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

<?php
// Incluir footer administrativo
include 'private/includes/admin-footer.php';
?>