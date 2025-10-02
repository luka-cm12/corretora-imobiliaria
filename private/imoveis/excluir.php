<?php
// Excluir imóvel – confirmação e processamento com CSRF
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/functions.php');

// Exige login
require_login();
// if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') { header('Location: ../admin/login.php'); exit; }

// Garante token CSRF
if (empty($_SESSION['csrf_token'])) {
    try { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32)); }
}

// Processamento do POST (exclusão)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $_SESSION['mensagem'] = 'Token de segurança inválido.';
        $_SESSION['tipo_mensagem'] = 'error';
        header('Location: listar.php');
        exit;
    }

    $imovel_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$imovel_id) {
        $_SESSION['mensagem'] = 'ID do imóvel inválido.';
        $_SESSION['tipo_mensagem'] = 'error';
        header('Location: listar.php');
        exit;
    }

    // Busca imagens no próprio registro de imoveis
    $dados = db_query('SELECT titulo, imagens FROM imoveis WHERE id = ?', [$imovel_id]);
    if (!is_array($dados) || count($dados) === 0) {
        $_SESSION['mensagem'] = 'Imóvel não encontrado.';
        $_SESSION['tipo_mensagem'] = 'warning';
        header('Location: listar.php');
        exit;
    }

    $imgs = array_values(array_filter(explode(',', $dados[0]['imagens'] ?? '')));
    // Remove primeiro os arquivos físicos
    $baseUploadsAbs = __DIR__ . '/../../public/uploads/';
    foreach ($imgs as $img) {
        $path = $baseUploadsAbs . $img;
        if (is_file($path)) { @unlink($path); }
    }

    // Exclui o registro
    $del = db_query('DELETE FROM imoveis WHERE id = ?', [$imovel_id]);
    if ($del !== false) {
        $_SESSION['mensagem'] = 'Imóvel excluído com sucesso!';
        $_SESSION['tipo_mensagem'] = 'success';
    } else {
        $_SESSION['mensagem'] = 'Erro ao excluir imóvel.';
        $_SESSION['tipo_mensagem'] = 'error';
    }
    header('Location: listar.php');
    exit;
}

// GET – exibe confirmação bonita
$imovel_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$imovel_id) {
    header('Location: listar.php');
    exit;
}

$dados = db_query('SELECT id, titulo, cidade, bairro, preco, imagens, destaque FROM imoveis WHERE id = ?', [$imovel_id]);
if (!is_array($dados) || count($dados) === 0) {
    header('Location: listar.php');
    exit;
}

$imovel = $dados[0];
$imagens = array_values(array_filter(explode(',', $imovel['imagens'] ?? '')));
$thumb = !empty($imagens) ? '../../public/uploads/' . $imagens[0] : '../../public/assets/images/default-property.jpg';

$page_title = 'Excluir Imóvel';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Excluir Imóvel</h1>
    <p class="breadcrumb">
        <a href="../admin/dashboard.php">Dashboard</a> /
        <a href="listar.php">Imóveis</a> /
        <span>Excluir</span>
    </p>

    <div class="card danger">
        <div class="card-body" style="display:flex; gap:20px; align-items:flex-start;">
            <div style="flex:0 0 180px;">
                <img src="<?= htmlspecialchars($thumb) ?>" alt="Thumb do imóvel" style="width:180px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #eee;">
            </div>
            <div style="flex:1;">
                <h2 style="margin:0 0 6px;">Tem certeza que deseja excluir este imóvel?</h2>
                <p style="margin:0 0 12px;color:#555;">Esta ação é permanente e irá remover também os arquivos de imagem relacionados.</p>
                <ul style="list-style:none;padding:0;margin:0 0 16px;color:#333;">
                    <li><strong>Título:</strong> <?= htmlspecialchars($imovel['titulo'] ?? '') ?></li>
                    <li><strong>Localização:</strong> <?= htmlspecialchars(($imovel['bairro'] ?? '') . ', ' . ($imovel['cidade'] ?? '')) ?></li>
                    <li><strong>Preço:</strong> <?= formatar_preco($imovel['preco'] ?? 0) ?></li>
                    <li><strong>Destaque:</strong> <?= !empty($imovel['destaque']) ? 'Sim' : 'Não' ?></li>
                    <li><strong>Imagens:</strong> <?= count($imagens) ?> arquivo(s)</li>
                </ul>

                <form method="post" style="display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="id" value="<?= (int)$imovel_id ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Excluir definitivamente</button>
                    <a class="btn btn-secondary" href="listar.php"><i class="fas fa-arrow-left"></i> Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>