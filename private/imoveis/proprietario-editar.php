<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

$errors = [];
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

function normalizar_cpf(string $valor): string {
    return preg_replace('/\D+/', '', $valor);
}

function cpf_valido(string $cpf): bool {
    $cpf = normalizar_cpf($cpf);
    if (strlen($cpf) !== 11) return false;
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    return true;
}

// Verificar se o ID foi fornecido
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: proprietarios-listar.php');
    exit;
}

// Buscar proprietário
$proprietario = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id]);
if (empty($proprietario)) {
    header('Location: proprietarios-listar.php');
    exit;
}
$proprietario = $proprietario[0];

// Buscar imóveis do proprietário
$imoveis = db_query("SELECT id_imovel, titulo, tipo, cidade, preco FROM imoveis WHERE id_proprietario = ? ORDER BY titulo", [$id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $errors[] = 'Sessão expirada ou token inválido. Atualize a página e tente novamente.';
    }

    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if ($nome === '') {
        $errors[] = 'O nome é obrigatório.';
    }

    // Validar CPF se informado
    if ($cpf !== '' && !cpf_valido($cpf)) {
        $errors[] = 'Informe um CPF com 11 dígitos.';
    }

    // Validar email se informado
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }

    // Verificações de duplicidade (exceto o próprio registro)
    if (empty($errors)) {
        // Verificar CPF duplicado
        if ($cpf !== '') {
            $cpf_norm = normalizar_cpf($cpf);
            $cpf_dup = db_query(
                "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? AND id_proprietario != ? LIMIT 1",
                [$cpf_norm, $id]
            );
            if (!empty($cpf_dup)) {
                $errors[] = 'Já existe outro proprietário cadastrado com este CPF.';
            }
        }

        // Verificar email duplicado
        if ($email !== '') {
            $email_dup = db_query(
                "SELECT id_proprietario FROM proprietarios WHERE LOWER(email) = LOWER(?) AND id_proprietario != ? LIMIT 1",
                [$email, $id]
            );
            if (!empty($email_dup)) {
                $errors[] = 'Este e-mail já está cadastrado para outro proprietário.';
            }
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE proprietarios SET nome = ?, cpf = ?, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
        $result = db_query($sql, [$nome, $cpf, $telefone, $email, $endereco, $id]);

        if ($result !== false) {
            $success = 'Proprietário atualizado com sucesso!';
            // Recarregar dados atualizados
            $proprietario = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id])[0];
        } else {
            $errors[] = 'Erro ao atualizar proprietário.';
        }
    }
}

$page_title = 'Editar Proprietário | Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.proprietario-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.proprietario-info {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.info-item h4 {
    margin: 0 0 8px 0;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
}

.info-item p {
    margin: 0;
    color: #6c757d;
    font-size: 16px;
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

.imoveis-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.imoveis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.imovel-card {
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

.imovel-card h4 {
    margin: 0 0 10px 0;
    color: #495057;
}

.imovel-card p {
    margin: 5px 0;
    color: #6c757d;
    font-size: 14px;
}

.form-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-back {
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
}

.btn-back:hover {
    background: #5a6268;
    text-decoration: none;
    color: white;
}
</style>

<div class="breadcrumb">
    <a href="../admin/dashboard.php">Dashboard</a> / 
    <a href="proprietarios-listar.php">Proprietários</a> / 
    <span>Editar</span>
</div>

<div class="proprietario-header">
    <h1>Editar Proprietário</h1>
    <a href="proprietarios-listar.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Voltar para Lista
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul style="margin-left: 18px; margin-bottom: 0;">
            <?php foreach ($errors as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Informações atuais do proprietário -->
<div class="proprietario-info">
    <h3><i class="fas fa-user"></i> Informações Atuais</h3>
    
    <div class="info-grid">
        <div class="info-item">
            <h4>Nome Completo</h4>
            <p><?= htmlspecialchars($proprietario['nome']) ?></p>
        </div>
        
        <div class="info-item">
            <h4>CPF</h4>
            <p>
                <?php if (!empty($proprietario['cpf'])): ?>
                    <?= htmlspecialchars($proprietario['cpf']) ?>
                <?php else: ?>
                    <span class="badge badge-warning">Não informado</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="info-item">
            <h4>Telefone</h4>
            <p>
                <?php if (!empty($proprietario['telefone'])): ?>
                    <?= htmlspecialchars($proprietario['telefone']) ?>
                <?php else: ?>
                    <span class="badge badge-warning">Não informado</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="info-item">
            <h4>Email</h4>
            <p>
                <?php if (!empty($proprietario['email'])): ?>
                    <?= htmlspecialchars($proprietario['email']) ?>
                <?php else: ?>
                    <span class="badge badge-warning">Não informado</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <?php if (!empty($proprietario['endereco'])): ?>
        <div class="info-item" style="margin-top: 20px;">
            <h4>Endereço</h4>
            <p><?= htmlspecialchars($proprietario['endereco']) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- Imóveis do proprietário -->
<div class="imoveis-section">
    <h3><i class="fas fa-home"></i> Imóveis Cadastrados (<?= count($imoveis) ?>)</h3>
    
    <?php if (empty($imoveis)): ?>
        <p style="color: #6c757d; text-align: center; margin: 20px 0;">
            Este proprietário ainda não possui imóveis cadastrados.
        </p>
    <?php else: ?>
        <div class="imoveis-grid">
            <?php foreach ($imoveis as $imovel): ?>
                <div class="imovel-card">
                    <h4><?= htmlspecialchars($imovel['titulo']) ?></h4>
                    <p><strong>Tipo:</strong> <?= ucfirst($imovel['tipo']) ?></p>
                    <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['cidade']) ?></p>
                    <p><strong>Preço:</strong> R$ <?= number_format($imovel['preco'], 2, ',', '.') ?></p>
                    <a href="editar.php?id=<?= $imovel['id_imovel'] ?>" style="color: #007bff; text-decoration: none;">
                        <i class="fas fa-edit"></i> Editar imóvel
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Formulário de edição -->
<div class="form-section">
    <h3><i class="fas fa-edit"></i> Editar Dados</h3>
    
    <form method="POST" class="imovel-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" 
                       name="nome" 
                       value="<?= htmlspecialchars($proprietario['nome']) ?>" 
                       required 
                       maxlength="150" 
                       autocomplete="name">
            </div>
            <div class="form-group">
                <label>CPF</label>
                <input type="text" 
                       name="cpf" 
                       value="<?= htmlspecialchars($proprietario['cpf']) ?>" 
                       placeholder="000.000.000-00" 
                       maxlength="14" 
                       pattern="\d{3}\.\d{3}\.\d{3}-\d{2}|\d{11}" 
                       title="Informe 11 dígitos ou no formato 000.000.000-00">
                <div class="form-text">Somente números ou no formato 000.000.000-00 (opcional)</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" 
                       name="telefone" 
                       value="<?= htmlspecialchars($proprietario['telefone']) ?>" 
                       placeholder="(99) 99999-9999" 
                       maxlength="20" 
                       autocomplete="tel">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" 
                       name="email" 
                       value="<?= htmlspecialchars($proprietario['email']) ?>" 
                       placeholder="email@exemplo.com" 
                       maxlength="150" 
                       autocomplete="email">
            </div>
        </div>

        <div class="form-group">
            <label>Endereço</label>
            <textarea name="endereco" 
                      rows="3" 
                      maxlength="255"><?= htmlspecialchars($proprietario['endereco']) ?></textarea>
        </div>

        <div class="form-actions" style="display:flex; gap:15px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
            <a href="proprietarios-listar.php" class="btn-cancel">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
        
        <div class="form-text">Campos marcados com * são obrigatórios.</div>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>