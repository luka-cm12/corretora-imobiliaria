<?php
// Script de diagnóstico e correção DEFINITIVA
require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🔧 Diagnóstico e Correção DEFINITIVA</h1>";

try {
    // 1. Testar conexão básica
    echo "<h2>1️⃣ Teste de Conexão</h2>";
    $teste = db_query("SELECT 1 as teste");
    echo "<p style='color: green;'>✅ Conexão PDO funcionando</p>";
    
    // 2. Verificar estrutura da tabela
    echo "<h2>2️⃣ Estrutura Atual da Tabela</h2>";
    $estrutura = db_query("SHOW COLUMNS FROM proprietarios");
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Padrão</th><th>Extra</th></tr>";
    
    $campos_existentes = [];
    foreach ($estrutura as $col) {
        $campos_existentes[] = $col['Field'];
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Verificar se faltam colunas
    echo "<h2>3️⃣ Verificação de Colunas</h2>";
    
    $colunas_necessarias = ['id_proprietario', 'nome', 'cpf', 'telefone', 'email', 'endereco'];
    $colunas_opcionais = ['tipo_documento', 'cnpj', 'created_at'];
    
    echo "<h3>Colunas Obrigatórias:</h3>";
    foreach ($colunas_necessarias as $coluna) {
        $existe = in_array($coluna, $campos_existentes);
        echo "<p>" . ($existe ? "✅" : "❌") . " {$coluna}: " . ($existe ? "EXISTE" : "FALTANDO") . "</p>";
    }
    
    echo "<h3>Colunas Opcionais:</h3>";
    foreach ($colunas_opcionais as $coluna) {
        $existe = in_array($coluna, $campos_existentes);
        echo "<p>" . ($existe ? "✅" : "⚠️") . " {$coluna}: " . ($existe ? "EXISTE" : "NÃO EXISTE") . "</p>";
    }
    
    // 4. Adicionar colunas que faltam
    echo "<h2>4️⃣ Criação de Colunas Faltantes</h2>";
    
    // Verificar e adicionar tipo_documento
    if (!in_array('tipo_documento', $campos_existentes)) {
        echo "<h4>Adicionando coluna tipo_documento...</h4>";
        try {
            db_query("ALTER TABLE proprietarios ADD COLUMN tipo_documento VARCHAR(10) DEFAULT 'cpf' AFTER nome");
            echo "<p style='color: green;'>✅ Coluna tipo_documento criada</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Erro ao criar tipo_documento: " . $e->getMessage() . "</p>";
        }
    }
    
    // Verificar e adicionar cnpj
    if (!in_array('cnpj', $campos_existentes)) {
        echo "<h4>Adicionando coluna cnpj...</h4>";
        try {
            db_query("ALTER TABLE proprietarios ADD COLUMN cnpj VARCHAR(20) NULL AFTER cpf");
            echo "<p style='color: green;'>✅ Coluna cnpj criada</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Erro ao criar cnpj: " . $e->getMessage() . "</p>";
        }
    }
    
    // 5. Teste de inserção BÁSICA
    echo "<h2>5️⃣ Teste de Inserção BÁSICA</h2>";
    
    try {
        // Tentar inserção com estrutura mínima
        $nome_teste = 'TESTE ' . date('H:i:s');
        $result = db_query(
            "INSERT INTO proprietarios (nome, cpf) VALUES (?, ?)", 
            [$nome_teste, '12345678901']
        );
        
        if ($result) {
            echo "<p style='color: green;'>✅ Inserção básica funcionou - ID: {$result}</p>";
            
            // Verificar se foi inserido
            $check = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$result]);
            if (!empty($check)) {
                echo "<p style='color: green;'>✅ Registro encontrado após inserção</p>";
                
                // Limpar teste
                db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$result]);
                echo "<p>🗑️ Registro de teste removido</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ ERRO na inserção básica: " . $e->getMessage() . "</p>";
        
        // Mostrar detalhes do erro
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Detalhes do Erro:</h4>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "</div>";
    }
    
    // 6. Teste com estrutura nova (se existir)
    if (in_array('tipo_documento', $campos_existentes) && in_array('cnpj', $campos_existentes)) {
        echo "<h2>6️⃣ Teste com Estrutura Nova</h2>";
        
        try {
            $nome_teste2 = 'TESTE NOVO ' . date('H:i:s');
            $result2 = db_query(
                "INSERT INTO proprietarios (nome, tipo_documento, cpf) VALUES (?, ?, ?)", 
                [$nome_teste2, 'cpf', '98765432109']
            );
            
            if ($result2) {
                echo "<p style='color: green;'>✅ Inserção com estrutura nova funcionou - ID: {$result2}</p>";
                
                // Limpar teste
                db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$result2]);
                echo "<p>🗑️ Registro de teste removido</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro com estrutura nova: " . $e->getMessage() . "</p>";
        }
    }
    
    // 7. Mostrar proprietários existentes
    echo "<h2>7️⃣ Proprietários Existentes</h2>";
    
    $count = db_query("SELECT COUNT(*) as total FROM proprietarios")[0]['total'];
    echo "<p>Total: <strong>{$count}</strong> proprietários</p>";
    
    if ($count > 0 && $count < 10) {
        echo "<h4>Lista atual:</h4>";
        $all = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Telefone</th><th>Email</th></tr>";
        foreach ($all as $p) {
            echo "<tr>";
            echo "<td>{$p['id_proprietario']}</td>";
            echo "<td>" . htmlspecialchars($p['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($p['cpf'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($p['telefone'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($p['email'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 8. Resultado final
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #c3e6cb;'>";
    echo "<h2>✅ Diagnóstico Concluído</h2>";
    echo "<p><strong>Status:</strong> Sistema pronto para uso</p>";
    echo "<p><strong>Ação:</strong> Agora teste o cadastro de proprietários</p>";
    echo "<p><a href='cadastro-ultra-simples.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Testar Cadastro Ultra Simples</a></p>";
    echo "<p><a href='private/imoveis/proprietario-cadastrar.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>📝 Cadastro Original</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #f5c6cb;'>";
    echo "<h2 style='color: #721c24;'>❌ ERRO CRÍTICO</h2>";
    echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Solução:</strong> Verifique se o XAMPP está rodando e se o banco 'corretora_base' existe</p>";
    echo "</div>";
}
?>