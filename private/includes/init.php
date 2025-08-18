// Verifica a pasta uploads durante a inicialização
<?php
$upload_dir = __DIR__ . 'public/uploads';

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        die("ERRO CRÍTICO: Não foi possível criar a pasta uploads.");
    }
}

if (!is_writable($upload_dir)) {
    if (!chmod($upload_dir, 0755)) {
        die("ERRO CRÍTICO: A pasta uploads não tem permissão de escrita.");
    }
}