<?php
// Teste final - simula exatamente o comportamento da página de listagem
require_once(__DIR__ . '/private/includes/auth.php');
require_login();
require_once(__DIR__ . '/private/includes/db.php');
require_once(__DIR__ . '/private/includes/functions.php');

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

// Processar exclusão (código idêntico ao da página)
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    // Verificar CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Token de segurança inválido. Atualize a página e tente novamente.';
    } else {
        $id = (int)$_POST['id'];
        
        if ($id <= 0) {
            $error = 'ID do proprietário inválido.';
        } else {
            try {
                // Verificar se o proprietário tem imóveis associados
                $imoveis_result = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id]);
                $imoveis_count = $imoveis_result[0]['count'];
                
                if ($imoveis_count > 0) {
                    $error = "Não é possível excluir este proprietário pois ele possui {$imoveis_count} imóvel(is) cadastrado(s).";
                } else {
                    // Executar a exclusão
                    $linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id]);
                    
                    if ($linhas_afetadas > 0) {
                        $success = 'Proprietário excluído com sucesso!';
                    } else {
                        $error = 'Proprietário não encontrado ou não foi possível excluir.';
                    }
                }
            } catch (Exception $e) {
                $error = 'Erro ao excluir proprietário: ' . $e->getMessage();
            }
        }
    }
}

// Verificar se houve exclusão bem-sucedida (via redirecionamento)
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $success = 'Proprietário excluído com sucesso!';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Final - Exclusão de Proprietário</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn-delete { background: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-delete:hover { background: #c82333; }
        .btn-delete:disabled { background: #6c757d; opacity: 0.6; cursor: not-allowed; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .actions { display: flex; gap: 8px; }
    </style>
</head>
<body>
    <h1>Teste Final - Exclusão de Proprietário</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i>⚠️</i> <strong>Erro:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i>✅</i> <strong>Sucesso:</strong> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <h2>Proprietários no Sistema</h2>
    
    <?php
    try {
        // Buscar proprietários (código idêntico ao da página)
        $proprietarios = db_query("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
            FROM proprietarios p 
            ORDER BY p.nome ASC 
            LIMIT 10
        ");
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Erro ao buscar proprietários: " . $e->getMessage() . "</div>";
        $proprietarios = [];
    }
    ?>
    
    <?php if (empty($proprietarios)): ?>
        <p>Nenhum proprietário encontrado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Imóveis</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proprietarios as $proprietario): ?>
                    <tr>
                        <td><?= $proprietario['id_proprietario'] ?></td>
                        <td><?= htmlspecialchars($proprietario['nome']) ?></td>
                        <td><?= htmlspecialchars($proprietario['email'] ?? 'N/A') ?></td>
                        <td><?= $proprietario['total_imoveis'] ?> imóvel(is)</td>
                        <td>
                            <div class="actions">
                                <!-- Formulário idêntico ao da página original -->
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este proprietário?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= $proprietario['id_proprietario'] ?>">
                                    
                                    <?php if ($proprietario['total_imoveis'] > 0): ?>
                                        <button type="button" 
                                                class="btn-delete" 
                                                disabled
                                                title="Não é possível excluir: proprietário possui <?= $proprietario['total_imoveis'] ?> imóvel(is) cadastrado(s)">
                                            🗑️ Excluir
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" 
                                                class="btn-delete" 
                                                title="Excluir proprietário">
                                            🗑️ Excluir
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <hr>
    
    <h3>Criar Proprietário de Teste</h3>
    <form method="POST" action="?" style="margin: 20px 0;">
        <input type="hidden" name="criar_teste" value="1">
        <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
            ➕ Criar Proprietário de Teste
        </button>
    </form>
    
    <?php
    // Criar proprietário de teste
    if (isset($_POST['criar_teste'])) {
        try {
            $nome_teste = "TESTE EXCLUSÃO " . date('d/m/Y H:i:s');
            $email_teste = "teste" . time() . "@exemplo.com";
            
            $insert_id = db_query(
                "INSERT INTO proprietarios (nome, email, created_at) VALUES (?, ?, NOW())", 
                [$nome_teste, $email_teste]
            );
            
            echo "<div class='alert alert-success'>✅ Proprietário de teste criado com ID: {$insert_id}</div>";
            echo "<script>setTimeout(() => window.location.reload(), 1500);</script>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>❌ Erro ao criar proprietário de teste: " . $e->getMessage() . "</div>";
        }
    }
    ?>
    
    <hr>
    <p><a href="private/imoveis/proprietarios-listar.php">← Voltar para a página original</a></p>
</body>
</html>