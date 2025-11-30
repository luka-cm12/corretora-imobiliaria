<?php
require_once(__DIR__ . '/private/includes/auth.php');
require_login();
require_once(__DIR__ . '/private/includes/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';
$success = '';

// PROCESSO IDÊNTICO AO DA PÁGINA ORIGINAL
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
    echo "<strong>POST RECEBIDO:</strong><br>";
    echo "Action: " . ($_POST['action'] ?? 'N/A') . "<br>";
    echo "ID: " . ($_POST['id'] ?? 'N/A') . "<br>";
    echo "CSRF Token: " . (!empty($_POST['csrf_token']) ? 'Presente (' . substr($_POST['csrf_token'], 0, 10) . '...)' : 'Ausente') . "<br>";
    echo "Sessão CSRF: " . (!empty($_SESSION['csrf_token']) ? 'Presente (' . substr($_SESSION['csrf_token'], 0, 10) . '...)' : 'Ausente') . "<br>";
    echo "</div>";
    
    // Verificar CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Token de segurança inválido. Atualize a página e tente novamente.';
        echo "<p style='color: red;'>❌ CSRF FALHOU</p>";
    } else {
        echo "<p style='color: green;'>✅ CSRF OK</p>";
        
        $id = (int)$_POST['id'];
        
        if ($id <= 0) {
            $error = 'ID do proprietário inválido.';
            echo "<p style='color: red;'>❌ ID INVÁLIDO: {$id}</p>";
        } else {
            echo "<p style='color: blue;'>🔍 Processando ID: {$id}</p>";
            
            try {
                // Verificar se o proprietário tem imóveis associados
                echo "<p>📋 Verificando imóveis...</p>";
                $imoveis_result = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id]);
                $imoveis_count = $imoveis_result[0]['count'];
                echo "<p>📊 Imóveis encontrados: {$imoveis_count}</p>";
                
                if ($imoveis_count > 0) {
                    $error = "Não é possível excluir este proprietário pois ele possui {$imoveis_count} imóvel(is) cadastrado(s).";
                    echo "<p style='color: orange;'>⚠️ Não pode excluir - tem imóveis</p>";
                } else {
                    echo "<p style='color: green;'>✅ Pode excluir - sem imóveis</p>";
                    
                    // Verificar se o proprietário existe
                    echo "<p>🔍 Verificando se proprietário existe...</p>";
                    $prop_exists = db_query("SELECT id_proprietario, nome FROM proprietarios WHERE id_proprietario = ?", [$id]);
                    
                    if (empty($prop_exists)) {
                        echo "<p style='color: red;'>❌ Proprietário não encontrado!</p>";
                        $error = 'Proprietário não encontrado.';
                    } else {
                        echo "<p style='color: green;'>✅ Proprietário encontrado: " . $prop_exists[0]['nome'] . "</p>";
                        
                        // Executar a exclusão
                        echo "<p>🗑️ Executando DELETE...</p>";
                        
                        // Log da query que será executada
                        echo "<p><strong>SQL:</strong> DELETE FROM proprietarios WHERE id_proprietario = {$id}</p>";
                        
                        $linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id]);
                        
                        echo "<p><strong>Linhas afetadas:</strong> {$linhas_afetadas}</p>";
                        
                        if ($linhas_afetadas > 0) {
                            $success = 'Proprietário excluído com sucesso!';
                            echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! Exclusão realizada.</p>";
                            
                            // Verificar se realmente foi excluído
                            $verificar = db_query("SELECT COUNT(*) as count FROM proprietarios WHERE id_proprietario = ?", [$id])[0]['count'];
                            if ($verificar == 0) {
                                echo "<p style='color: green;'>✅ CONFIRMADO: Proprietário não existe mais no banco</p>";
                            } else {
                                echo "<p style='color: red;'>❌ ERRO: Proprietário ainda existe no banco!</p>";
                            }
                        } else {
                            $error = 'Proprietário não encontrado ou não foi possível excluir.';
                            echo "<p style='color: red; font-weight: bold;'>❌ FALHA! Nenhuma linha afetada.</p>";
                        }
                    }
                }
            } catch (Exception $e) {
                $error = 'Erro ao excluir proprietário: ' . $e->getMessage();
                echo "<p style='color: red; font-weight: bold;'>❌ EXCEÇÃO: " . $e->getMessage() . "</p>";
                echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px; font-size: 12px;'>";
                echo $e->getTraceAsString();
                echo "</pre>";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Direto de Exclusão</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .proprietario { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #dee2e6; }
        .btn-delete { background: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-delete:hover { background: #c82333; }
        .btn-delete:disabled { background: #6c757d; cursor: not-allowed; opacity: 0.6; }
    </style>
</head>
<body>
    <h1>🧪 Teste Direto de Exclusão</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">❌ <strong>Erro:</strong> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">✅ <strong>Sucesso:</strong> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <h2>Proprietários Disponíveis para Teste</h2>
    
    <?php
    try {
        $proprietarios = db_query("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
            FROM proprietarios p 
            ORDER BY p.id_proprietario DESC 
            LIMIT 10
        ");
        
        foreach ($proprietarios as $prop) {
            echo "<div class='proprietario'>";
            echo "<h3>ID: {$prop['id_proprietario']} - " . htmlspecialchars($prop['nome']) . "</h3>";
            echo "<p><strong>CPF:</strong> " . htmlspecialchars($prop['cpf'] ?? 'N/A') . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($prop['email'] ?? 'N/A') . "</p>";
            echo "<p><strong>Imóveis:</strong> {$prop['total_imoveis']}</p>";
            
            // Formulário IDÊNTICO ao da página original
            echo "<form method='POST' style='display: inline;' onsubmit='return confirm(\"Tem certeza que deseja excluir este proprietário?\");'>";
            echo "<input type='hidden' name='action' value='delete'>";
            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
            echo "<input type='hidden' name='id' value='{$prop['id_proprietario']}'>";
            
            if ($prop['total_imoveis'] > 0) {
                echo "<button type='button' class='btn-delete' disabled>";
                echo "🗑️ Não Pode Excluir ({$prop['total_imoveis']} imóveis)";
                echo "</button>";
            } else {
                echo "<button type='submit' class='btn-delete'>";
                echo "🗑️ Excluir Proprietário";
                echo "</button>";
            }
            
            echo "</form>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Erro ao carregar proprietários: " . $e->getMessage() . "</div>";
    }
    ?>
    
    <hr>
    
    <h3>Informações de Debug</h3>
    <ul>
        <li><strong>Sessão ID:</strong> <?= session_id() ?></li>
        <li><strong>CSRF Token:</strong> <?= substr($_SESSION['csrf_token'] ?? 'N/A', 0, 20) ?>...</li>
        <li><strong>Método:</strong> <?= $_SERVER['REQUEST_METHOD'] ?></li>
        <li><strong>User Agent:</strong> <?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') ?></li>
    </ul>
    
    <p><a href="private/imoveis/proprietarios-listar.php">← Voltar para página original</a></p>
    
</body>
</html>