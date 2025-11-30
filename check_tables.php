<?php
require_once 'private/includes/db.php';

try {
    echo "Verificando tabelas no banco...\n";
    $tables = db_query("SHOW TABLES");
    
    echo "Tabelas encontradas:\n";
    foreach ($tables as $table) {
        print_r($table);
    }
    
    echo "\nVerificando tabela proprietarios especificamente...\n";
    $exists = db_query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'proprietarios'");
    echo "Tabela proprietarios existe: " . ($exists[0]['count'] > 0 ? "SIM" : "NÃO") . "\n";
    
    if ($exists[0]['count'] > 0) {
        echo "\nEstrutura da tabela proprietarios:\n";
        $structure = db_query("DESCRIBE proprietarios");
        print_r($structure);
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
?>