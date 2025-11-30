<?php
require_once(__DIR__ . '/private/includes/auth.php');
require_login();
require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>DIAGNÓSTICO COMPLETO - Problema na Exclusão</h1>";

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gerar CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<h2>1. Verificação da Estrutura da Tabela</h2>";
try {
    $estrutura = db_query("DESCRIBE proprietarios");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($estrutura as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($campo['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao verificar estrutura: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Verificação de Constraints e Índices</h2>";
try {
    $indices = db_query("SHOW INDEX FROM proprietarios");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tabela</th><th>Não Único</th><th>Nome da Chave</th><th>Coluna</th><th>Tipo</th></tr>";
    foreach ($indices as $indice) {
        echo "<tr>";
        echo "<td>{$indice['Table']}</td>";
        echo "<td>{$indice['Non_unique']}</td>";
        echo "<td>{$indice['Key_name']}</td>";
        echo "<td>{$indice['Column_name']}</td>";
        echo "<td>" . ($indice['Index_type'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao verificar índices: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Listar Proprietários com Problemas Potenciais</h2>";
try {
    $proprietarios = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario");
    
    echo "<p><strong>Total de proprietários:</strong> " . count($proprietarios) . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Email</th><th>Problemas</th><th>Teste</th></tr>";
    
    foreach ($proprietarios as $prop) {
        $problemas = [];
        
        // Verificar problemas
        if (empty($prop['cpf']) || $prop['cpf'] === '') {
            $problemas[] = "CPF vazio";
        }
        
        if (strlen($prop['cpf']) > 0 && strlen($prop['cpf']) != 11 && strlen($prop['cpf']) != 14) {
            $problemas[] = "CPF formato inválido";
        }
        
        // Contar imóveis
        $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$prop['id_proprietario']])[0]['count'];
        
        if ($imoveis_count > 0) {
            $problemas[] = "{$imoveis_count} imóvel(is)";
        }
        
        $cor_linha = empty($problemas) ? '#e8f5e8' : '#ffe8e8';
        
        echo "<tr style='background-color: {$cor_linha};'>";
        echo "<td>{$prop['id_proprietario']}</td>";
        echo "<td>" . htmlspecialchars($prop['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($prop['cpf'] ?? 'VAZIO') . "</td>";
        echo "<td>" . htmlspecialchars($prop['email'] ?? 'VAZIO') . "</td>";
        echo "<td>" . (empty($problemas) ? 'Nenhum' : implode(', ', $problemas)) . "</td>";
        
        if (empty($problemas)) {
            echo "<td><button onclick='testarExclusao({$prop['id_proprietario']})' style='background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px;'>Testar Exclusão</button></td>";
        } else {
            echo "<td>Não pode excluir</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao listar proprietários: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Teste Manual de Exclusão</h2>";

// Processar teste de exclusão
if (isset($_POST['test_id'])) {
    $test_id = (int)$_POST['test_id'];
    
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>Testando exclusão do proprietário ID: {$test_id}</h3>";
    
    try {
        // Verificar se existe
        $existe = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$test_id]);
        
        if (empty($existe)) {
            echo "<p style='color: red;'>❌ Proprietário não encontrado!</p>";
        } else {
            $prop = $existe[0];
            echo "<p>✅ Proprietário encontrado: " . htmlspecialchars($prop['nome']) . "</p>";
            
            // Verificar imóveis
            $imoveis = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$test_id])[0]['count'];
            echo "<p>📊 Imóveis associados: {$imoveis}</p>";
            
            if ($imoveis > 0) {
                echo "<p style='color: orange;'>⚠️ Não pode excluir - possui imóveis</p>";
            } else {
                echo "<p>🗑️ Tentando excluir...</p>";
                
                // Tentar exclusão
                $linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$test_id]);
                
                echo "<p>📋 Comando SQL executado: DELETE FROM proprietarios WHERE id_proprietario = {$test_id}</p>";
                echo "<p>📊 Linhas afetadas: {$linhas_afetadas}</p>";
                
                if ($linhas_afetadas > 0) {
                    echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! Proprietário excluído.</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ FALHA! Nenhuma linha foi afetada.</p>";
                    
                    // Verificar novamente se ainda existe
                    $ainda_existe = db_query("SELECT COUNT(*) as count FROM proprietarios WHERE id_proprietario = ?", [$test_id])[0]['count'];
                    echo "<p>🔍 Proprietário ainda existe? " . ($ainda_existe > 0 ? 'SIM' : 'NÃO') . "</p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
        
        // Log detalhado do erro
        echo "<details><summary>Detalhes do Erro</summary>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        echo "</details>";
    }
    
    echo "</div>";
}

?>

<script>
function testarExclusao(id) {
    if (confirm('Testar exclusão do proprietário ID: ' + id + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="test_id" value="${id}">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 20px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background: #f2f2f2; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
</style>

<hr>
<p><a href="private/imoveis/proprietarios-listar.php">← Voltar para listagem</a></p>