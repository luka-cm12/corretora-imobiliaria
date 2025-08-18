<?php
$upload_dir = __DIR__ . 'public/uploads';
$test_file = $upload_dir . 'test_permission.txt';

// Verifica se a pasta existe
if (!is_dir($upload_dir)) {
    die("ERRO: A pasta uploads não existe em: " . $upload_dir);
}

// Tenta criar um arquivo de teste
if (file_put_contents($test_file, 'Teste de permissão') === false) {
    die("ERRO: Não foi possível escrever na pasta uploads. Verifique as permissões.");
}

// Tenta apagar o arquivo de teste
if (!unlink($test_file)) {
    die("ERRO: Não foi possível apagar o arquivo de teste. Permissões podem estar incorretas.");
}

echo "SUCESSO: A pasta uploads está configurada corretamente com permissões de escrita!";