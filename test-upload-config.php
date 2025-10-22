<?php
/**
 * Teste de Configurações de Upload do PHP
 * Este arquivo verifica se as configurações do servidor suportam o upload de 25 imagens
 */

// Configurações relevantes para upload de múltiplas imagens
$config_checks = [
    'max_file_uploads' => [
        'current' => ini_get('max_file_uploads'),
        'recommended' => 25,
        'description' => 'Número máximo de arquivos que podem ser enviados simultaneamente'
    ],
    'post_max_size' => [
        'current' => ini_get('post_max_size'),
        'recommended' => '100M',
        'description' => 'Tamanho máximo dos dados POST (incluindo uploads)'
    ],
    'upload_max_filesize' => [
        'current' => ini_get('upload_max_filesize'),
        'recommended' => '10M',
        'description' => 'Tamanho máximo de cada arquivo individual'
    ],
    'memory_limit' => [
        'current' => ini_get('memory_limit'),
        'recommended' => '256M',
        'description' => 'Limite de memória para processamento das imagens'
    ],
    'max_execution_time' => [
        'current' => ini_get('max_execution_time'),
        'recommended' => 300,
        'description' => 'Tempo máximo de execução (importante para redimensionar múltiplas imagens)'
    ]
];

// Função para converter tamanhos em bytes
function convertToBytes($size) {
    if (is_numeric($size)) return (int) $size;
    
    $unit = strtoupper(substr($size, -1));
    $value = (int) substr($size, 0, -1);
    
    switch ($unit) {
        case 'G': return $value * 1024 * 1024 * 1024;
        case 'M': return $value * 1024 * 1024;
        case 'K': return $value * 1024;
        default: return $value;
    }
}

// Função para formatar bytes em formato legível
function formatBytes($bytes) {
    if ($bytes >= 1024*1024*1024) {
        return round($bytes / (1024*1024*1024), 1) . 'G';
    } elseif ($bytes >= 1024*1024) {
        return round($bytes / (1024*1024), 1) . 'M';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 1) . 'K';
    }
    return $bytes . 'B';
}

// Verificar se extensões necessárias estão disponíveis
$extensions = [
    'gd' => extension_loaded('gd'),
    'fileinfo' => extension_loaded('fileinfo'),
    'exif' => extension_loaded('exif')
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Configurações de Upload</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .config-item { margin: 15px 0; padding: 15px; border-radius: 5px; }
        .ok { background: #d4edda; border-left: 5px solid #28a745; }
        .warning { background: #fff3cd; border-left: 5px solid #ffc107; }
        .error { background: #f8d7da; border-left: 5px solid #dc3545; }
        .config-name { font-weight: bold; color: #333; }
        .config-value { font-family: monospace; color: #007bff; }
        .recommendation { font-size: 0.9em; color: #666; margin-top: 5px; }
        .status { font-weight: bold; }
        .status.ok { color: #28a745; }
        .status.warning { color: #ffc107; }
        .status.error { color: #dc3545; }
        .summary { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .extension-check { display: inline-block; margin: 5px 10px; padding: 5px 10px; border-radius: 3px; }
        .ext-ok { background: #d4edda; color: #155724; }
        .ext-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Teste de Configurações de Upload para 25 Imagens</h1>
        
        <h2>📊 Configurações do PHP</h2>
        
        <?php foreach ($config_checks as $config => $details): ?>
            <?php
            $current = $details['current'];
            $currentBytes = convertToBytes($current);
            $recommendedBytes = convertToBytes($details['recommended']);
            
            if ($config === 'max_file_uploads') {
                $status = (int)$current >= (int)$details['recommended'] ? 'ok' : 'error';
            } elseif ($config === 'max_execution_time') {
                $status = ((int)$current >= (int)$details['recommended'] || (int)$current === 0) ? 'ok' : 'warning';
            } else {
                $status = $currentBytes >= $recommendedBytes ? 'ok' : 'warning';
            }
            ?>
            
            <div class="config-item <?= $status ?>">
                <div class="config-name"><?= $config ?></div>
                <div>
                    Atual: <span class="config-value"><?= htmlspecialchars($current) ?></span>
                    | Recomendado: <span class="config-value"><?= htmlspecialchars($details['recommended']) ?></span>
                    | <span class="status <?= $status ?>">
                        <?= $status === 'ok' ? '✅ OK' : ($status === 'warning' ? '⚠️ ATENÇÃO' : '❌ PROBLEMA') ?>
                    </span>
                </div>
                <div class="recommendation"><?= htmlspecialchars($details['description']) ?></div>
            </div>
        <?php endforeach; ?>
        
        <h2>🔌 Extensões PHP</h2>
        <div>
            <?php foreach ($extensions as $ext => $loaded): ?>
                <span class="extension-check <?= $loaded ? 'ext-ok' : 'ext-error' ?>">
                    <?= $ext ?>: <?= $loaded ? '✅ Carregada' : '❌ Não encontrada' ?>
                </span>
            <?php endforeach; ?>
        </div>
        
        <div class="summary">
            <h3>📋 Resumo e Recomendações</h3>
            
            <?php
            $maxFiles = (int)ini_get('max_file_uploads');
            $postSize = convertToBytes(ini_get('post_max_size'));
            $fileSize = convertToBytes(ini_get('upload_max_filesize'));
            
            echo "<p><strong>Capacidade atual:</strong> Até {$maxFiles} arquivos por upload</p>";
            
            if ($maxFiles >= 25) {
                echo "<p class='status ok'>✅ Seu servidor suporta upload de 25 imagens simultaneamente!</p>";
            } else {
                echo "<p class='status error'>❌ Seu servidor só suporta {$maxFiles} arquivos por vez. Para 25 imagens, configure max_file_uploads = 25</p>";
            }
            
            $estimatedSize = 25 * convertToBytes('5M'); // Estimativa de 5MB por imagem
            if ($postSize >= $estimatedSize) {
                echo "<p class='status ok'>✅ Tamanho POST suficiente para 25 imagens (atual: " . formatBytes($postSize) . ")</p>";
            } else {
                echo "<p class='status warning'>⚠️ post_max_size pode ser insuficiente para 25 imagens grandes. Recomendado: 100M ou mais</p>";
            }
            ?>
            
            <h4>🛠️ Como ajustar (se necessário):</h4>
            <ol>
                <li><strong>No arquivo php.ini:</strong>
                    <pre style="background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace;">
max_file_uploads = 25
post_max_size = 100M
upload_max_filesize = 10M
memory_limit = 256M
max_execution_time = 300</pre>
                </li>
                <li><strong>Reinicie o servidor web</strong> (Apache/Nginx)</li>
                <li><strong>Teste novamente</strong> atualizando esta página</li>
            </ol>
            
            <p><strong>💡 Dica:</strong> Se você usar hospedagem compartilhada, pode precisar contactar o suporte para alterar essas configurações.</p>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="private/imoveis/adicionar.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                🏠 Testar Upload de Imóveis
            </a>
        </div>
    </div>
</body>
</html>