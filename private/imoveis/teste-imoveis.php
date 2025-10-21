<?php
echo '<h1>✅ Acesso à gestão de imóveis funcionando!</h1>';
echo '<p><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</p>';
echo '<p><strong>Pasta:</strong> /private/imoveis/</p>';

echo '<h2>🏠 Arquivos de Gestão de Imóveis</h2>';
$files = [
    'listar.php' => 'Listar Imóveis',
    'adicionar.php' => 'Adicionar Imóvel',
    'editar.php' => 'Editar Imóvel',
    'excluir.php' => 'Excluir Imóvel',
    'proprietario-cadastrar.php' => 'Cadastrar Proprietário'
];

echo '<ul>';
foreach ($files as $file => $desc) {
    $exists = file_exists($file);
    $icon = $exists ? '✅' : '❌';
    if ($exists) {
        echo '<li>' . $icon . ' <a href="' . $file . '">' . $desc . '</a></li>';
    } else {
        echo '<li>' . $icon . ' ' . $desc . ' (arquivo não encontrado)</li>';
    }
}
echo '</ul>';

echo '<h2>🔗 Links relacionados:</h2>';
echo '<ul>';
echo '<li><a href="../admin/login.php">🔐 Login Admin</a></li>';
echo '<li><a href="../admin/dashboard.php">📊 Dashboard Admin</a></li>';
echo '<li><a href="index.php">🏠 Índice Imóveis (redireciona)</a></li>';
echo '</ul>';

echo '<div style="background: #d5f4e6; padding: 10px; border: 1px solid #27ae60; margin: 10px 0;">';
echo '✅ <strong>Se você está vendo esta página, o acesso à gestão de imóveis está funcionando!</strong>';
echo '</div>';

echo '<div style="background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; margin: 10px 0;">';
echo '⚠️ <strong>NOTA:</strong> Os arquivos de gestão de imóveis requerem login admin para funcionar.<br>';
echo 'Faça login primeiro em <a href="../admin/login.php">../admin/login.php</a>';
echo '</div>';

echo '<div style="background: #f2dede; padding: 10px; border: 1px solid #d6b2b2; margin: 10px 0;">';
echo '🗑️ <strong>REMOVA este arquivo (teste-imoveis.php) após verificar!</strong>';
echo '</div>';
?>