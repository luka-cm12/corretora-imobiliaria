<?php
echo '<h1>🔍 Diagnóstico Completo - Corretora Claudia</h1>';

// Informações do servidor
echo '<h2>📊 Informações do Servidor</h2>';
echo '<ul>';
echo '<li><strong>Servidor:</strong> ' . ($_SERVER['HTTP_HOST'] ?? 'N/A') . '</li>';
echo '<li><strong>Documento Root:</strong> ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '</li>';
echo '<li><strong>Script atual:</strong> ' . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . '</li>';
echo '<li><strong>PHP Version:</strong> ' . PHP_VERSION . '</li>';
echo '</ul>';

// Verificar estrutura de pastas
echo '<h2>📁 Estrutura de Pastas</h2>';
$paths = [
    'private/' => 'Pasta Private',
    'private/admin/' => 'Pasta Admin',
    'private/imoveis/' => 'Pasta Imóveis',
    'private/config/' => 'Pasta Config',
    'private/includes/' => 'Pasta Includes',
    'public/' => 'Pasta Public'
];

echo '<ul>';
foreach ($paths as $path => $desc) {
    $exists = is_dir($path);
    $icon = $exists ? '✅' : '❌';
    $readable = $exists && is_readable($path) ? '(legível)' : '(não legível)';
    echo "<li>{$icon} {$desc}: {$path} {$readable}</li>";
}
echo '</ul>';

// Verificar arquivos críticos do admin
echo '<h2>📄 Arquivos da Área Admin</h2>';
$adminFiles = [
    'private/admin/index.php' => 'Índice Admin',
    'private/admin/login.php' => 'Login Admin',
    'private/admin/dashboard.php' => 'Dashboard Admin',
    'private/admin/.htaccess' => 'Configuração Apache Admin'
];

echo '<ul>';
foreach ($adminFiles as $file => $desc) {
    $exists = file_exists($file);
    $icon = $exists ? '✅' : '❌';
    $readable = $exists && is_readable($file) ? '(legível)' : '(não legível)';
    echo "<li>{$icon} {$desc}: {$file} {$readable}</li>";
}
echo '</ul>';

// Verificar arquivos da gestão de imóveis
echo '<h2>🏠 Arquivos da Gestão de Imóveis</h2>';
$imoveisFiles = [
    'private/imoveis/index.php' => 'Índice Imóveis',
    'private/imoveis/listar.php' => 'Listar Imóveis',
    'private/imoveis/adicionar.php' => 'Adicionar Imóvel',
    'private/imoveis/editar.php' => 'Editar Imóvel',
    'private/imoveis/excluir.php' => 'Excluir Imóvel',
    'private/imoveis/.htaccess' => 'Configuração Apache Imóveis'
];

echo '<ul>';
foreach ($imoveisFiles as $file => $desc) {
    $exists = file_exists($file);
    $icon = $exists ? '✅' : '❌';
    $readable = $exists && is_readable($file) ? '(legível)' : '(não legível)';
    echo "<li>{$icon} {$desc}: {$file} {$readable}</li>";
}
echo '</ul>';

echo '<ul>';
foreach ($adminFiles as $file => $desc) {
    $exists = file_exists($file);
    $icon = $exists ? '✅' : '❌';
    $readable = $exists && is_readable($file) ? '(legível)' : '(não legível)';
    echo "<li>{$icon} {$desc}: {$file} {$readable}</li>";
}
echo '</ul>';

// Teste de conexão com banco
echo '<h2>🔌 Teste de Conexão com Banco</h2>';
try {
    require_once 'private/config/config.php';
    echo '<div style="color: green;">✅ Conexão com banco estabelecida!</div>';
    echo '<ul>';
    echo '<li><strong>Host:</strong> ' . DB_HOST . '</li>';
    echo '<li><strong>Banco:</strong> ' . DB_NAME . '</li>';
    echo '<li><strong>Usuário:</strong> ' . DB_USER . '</li>';
    echo '</ul>';
    
    // Verificar tabela usuarios
    $tables = $conn->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    if (!empty($tables)) {
        $users = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch();
        echo '<div style="color: green;">✅ Tabela usuarios existe com ' . $users['total'] . ' usuário(s)</div>';
    } else {
        echo '<div style="color: red;">❌ Tabela usuarios não existe</div>';
    }
    
} catch (Exception $e) {
    echo '<div style="color: red;">❌ Erro na conexão: ' . $e->getMessage() . '</div>';
}

// Links de teste
echo '<h2>🔗 Links de Teste</h2>';
echo '<div style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
echo '<h3>🔧 Área Administrativa:</h3>';
echo '<p><a href="private/admin/teste-admin.php" target="_blank">🧪 Teste de Acesso Admin</a></p>';
echo '<p><a href="private/admin/" target="_blank">📂 Diretório Admin</a></p>';
echo '<p><a href="private/admin/login.php" target="_blank">🔐 Login Admin</a></p>';
echo '<h3>🏠 Gestão de Imóveis:</h3>';
echo '<p><a href="private/imoveis/teste-imoveis.php" target="_blank">🧪 Teste de Acesso Imóveis</a></p>';
echo '<p><a href="private/imoveis/" target="_blank">📂 Diretório Imóveis</a></p>';
echo '<p><a href="private/imoveis/listar.php" target="_blank">📋 Listar Imóveis</a></p>';
echo '<h3>🔌 Outros:</h3>';
echo '<p><a href="teste-conexao.php" target="_blank">🔌 Teste de Conexão</a></p>';
echo '</div>';

echo '<h2>⚠️ URLs para testar no navegador:</h2>';
$baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7;">';
echo '<p><strong>Base URL:</strong> <a href="' . $baseUrl . '" target="_blank">' . $baseUrl . '</a></p>';
echo '<p><strong>Admin URL:</strong> <a href="' . $baseUrl . 'private/admin/" target="_blank">' . $baseUrl . 'private/admin/</a></p>';
echo '<p><strong>Login URL:</strong> <a href="' . $baseUrl . 'private/admin/login.php" target="_blank">' . $baseUrl . 'private/admin/login.php</a></p>';
echo '</div>';

echo '<div style="background: #f2dede; padding: 15px; border: 1px solid #d6b2b2; margin: 15px 0;">';
echo '<strong>🗑️ LIMPEZA:</strong> Após verificar que tudo funciona, remova os arquivos de diagnóstico:<br>';
echo '• diagnostico.php<br>';
echo '• teste-conexao.php<br>';
echo '• verificar-admin.php<br>';
echo '• atualizar-usuarios.php<br>';
echo '• private/admin/teste-admin.php';
echo '</div>';
?>