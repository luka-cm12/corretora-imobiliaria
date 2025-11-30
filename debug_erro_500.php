<?php
// Script para testar o arquivo proprietario-editar.php e identificar erro 500
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "🔍 DIAGNÓSTICO DO ERRO 500 - EDITAR PROPRIETÁRIO\n\n";

try {
    echo "1️⃣ Testando includes básicos:\n";
    
    // Testar se os arquivos existem
    $arquivos = [
        'private/includes/auth.php',
        'private/includes/db.php', 
        'private/includes/functions.php'
    ];
    
    foreach ($arquivos as $arquivo) {
        if (file_exists($arquivo)) {
            echo "   ✅ {$arquivo} - existe\n";
        } else {
            echo "   ❌ {$arquivo} - NÃO ENCONTRADO\n";
            exit("ERRO CRÍTICO: Arquivo {$arquivo} não encontrado");
        }
    }
    
    echo "\n2️⃣ Testando conexão com banco:\n";
    require_once 'private/includes/db.php';
    echo "   ✅ Conexão com banco estabelecida\n";
    
    echo "\n3️⃣ Testando query de proprietários:\n";
    $teste_props = db_query("SELECT * FROM proprietarios LIMIT 1");
    if (count($teste_props) > 0) {
        echo "   ✅ Query funcionando - " . count($teste_props) . " registro(s) encontrado(s)\n";
        $primeiro = $teste_props[0];
        echo "   📋 Primeiro registro ID: {$primeiro['id_proprietario']} - Nome: {$primeiro['nome']}\n";
    } else {
        echo "   ⚠️ Nenhum proprietário encontrado na base\n";
    }
    
    echo "\n4️⃣ Testando estrutura da tabela:\n";
    $colunas = db_query("SHOW COLUMNS FROM proprietarios");
    foreach ($colunas as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n5️⃣ Simulando acesso à página de edição:\n";
    
    // Simular parâmetros GET
    if (count($teste_props) > 0) {
        $id_teste = $teste_props[0]['id_proprietario'];
        echo "   🧪 Testando com ID: {$id_teste}\n";
        
        // Testar query de busca específica
        $prop_especifico = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id_teste]);
        if (count($prop_especifico) > 0) {
            echo "   ✅ Proprietário encontrado: {$prop_especifico[0]['nome']}\n";
        } else {
            echo "   ❌ Proprietário não encontrado com ID {$id_teste}\n";
        }
        
        // Testar query de imóveis
        $imoveis_teste = db_query("SELECT COUNT(*) as total FROM imoveis WHERE id_proprietario = ?", [$id_teste]);
        $total_imoveis = $imoveis_teste[0]['total'];
        echo "   📊 Total de imóveis do proprietário: {$total_imoveis}\n";
        
        // URL de teste
        echo "   🔗 URL para testar: http://localhost/corretora-imobiliaria-11/private/imoveis/proprietario-editar.php?id={$id_teste}\n";
        
    } else {
        echo "   ⚠️ Impossível testar - nenhum proprietário na base\n";
    }
    
    echo "\n6️⃣ Verificando sessão:\n";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            echo "   ✅ CSRF token gerado\n";
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            echo "   ✅ CSRF token gerado (fallback)\n";
        }
    } else {
        echo "   ✅ CSRF token já existe\n";
    }
    
    echo "\n7️⃣ Testando funções de validação:\n";
    
    // Incluir e testar funções
    function normalizar_documento(string $valor): string {
        return preg_replace('/\D+/', '', $valor);
    }

    function cpf_valido(string $cpf): bool {
        if (empty(trim($cpf))) return false;
        $cpf = normalizar_documento($cpf);
        if (strlen($cpf) !== 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
        if (!ctype_digit($cpf)) return false;
        return true;
    }
    
    $teste_cpf = '12345678901';
    $resultado = cpf_valido($teste_cpf);
    echo "   🧪 Teste CPF '{$teste_cpf}': " . ($resultado ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "\n";
    
    echo "\n✅ DIAGNÓSTICO COMPLETO - Sistema parece estar funcionando!\n";
    echo "\n💡 Se ainda há erro 500, verifique:\n";
    echo "1. Log de erros do Apache (xampp/apache/logs/error.log)\n";
    echo "2. Se há caracteres especiais ou BOM no arquivo PHP\n";
    echo "3. Se todas as chaves do array estão sendo acessadas corretamente\n";
    echo "4. Se não há syntax errors no PHP\n";
    
} catch (Exception $e) {
    echo "❌ ERRO ENCONTRADO: " . $e->getMessage() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    echo "📁 Arquivo: " . $e->getFile() . "\n";
}
?>