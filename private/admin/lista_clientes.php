<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

// Filtros simples
$busca = trim($_GET['busca'] ?? '');
$params = [];
$where = '';
if ($busca !== '') {
    $where = " WHERE (nome LIKE ? OR email LIKE ? OR telefone LIKE ? OR assunto LIKE ? OR mensagem LIKE ?)";
    $like = "%$busca%";
    $params = [$like, $like, $like, $like, $like];
}

$sql = "SELECT id, imovel_id, imovel_titulo, nome, email, telefone, assunto, mensagem, created_at FROM contatos" . $where . " ORDER BY id DESC";
$contatos = db_query($sql, $params);

$page_title = 'Leads (Clientes)';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="toolbar">
    <form method="get" class="search-form">
        <input type="text" name="busca" placeholder="Buscar por nome, email, telefone..." value="<?= htmlspecialchars($busca) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
</div>

<div class="responsive-table">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Assunto</th>
                <th>Imóvel</th>
                <th>Mensagem</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($contatos)): ?>
                <?php foreach ($contatos as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id']) ?></td>
                        <td><?= htmlspecialchars($c['nome'] ?? '-') ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($c['email'] ?? '') ?>"><?= htmlspecialchars($c['email'] ?? '-') ?></a></td>
                        <td><?= htmlspecialchars($c['telefone'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['assunto'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($c['imovel_id'])): ?>
                                <a href="../imovel-detalhes.php?id=<?= urlencode($c['imovel_id']) ?>" target="_blank">
                                    <?= htmlspecialchars($c['imovel_titulo'] ?? 'Ver imóvel') ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= nl2br(htmlspecialchars($c['mensagem'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars($c['created_at'] ?? '-') ?></td>
                        <td class="actions">
                            <?php
                                $tel = preg_replace('/\D+/', '', $c['telefone'] ?? '');
                                $wa = $tel ? 'https://wa.me/' . $tel . '?text=' . urlencode('Olá ' . ($c['nome'] ?? '') . ', tudo bem?') : '';
                            ?>
                            <?php if ($wa): ?>
                                <a href="<?= $wa ?>" target="_blank" title="Chamar no WhatsApp" class="btn-edit"><i class="fab fa-whatsapp"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($c['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($c['email']) ?>" title="Enviar e-mail" class="btn-delete" style="background:#6c63ff"><i class="fas fa-envelope"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">Nenhum cliente encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
