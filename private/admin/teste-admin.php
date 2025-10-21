<?php
echo '<h1>✅ Acesso à área administrativa funcionando!</h1>';
echo '<p><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</p>';
echo '<p><strong>Pasta:</strong> /private/admin/</p>';

echo '<h2>🔗 Links de teste:</h2>';
echo '<ul>';
echo '<li><a href="login.php">➡️ Página de Login</a></li>';
echo '<li><a href="index.php">➡️ Índice Admin (redireciona)</a></li>';
echo '<li><a href="dashboard.php">➡️ Dashboard (requer login)</a></li>';
echo '</ul>';

echo '<div style="background: #d5f4e6; padding: 10px; border: 1px solid #27ae60; margin: 10px 0;">';
echo '✅ <strong>Se você está vendo esta página, o acesso ao admin está funcionando!</strong>';
echo '</div>';

echo '<div style="background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; margin: 10px 0;">';
echo '⚠️ <strong>REMOVA este arquivo (teste-admin.php) após verificar!</strong>';
echo '</div>';
?>