<?php
// Debug específico para o cadastro de proprietários
require_once(__DIR__ . '/private/includes/db.php');

echo "<h2>🔧 Debug - Cadastro de Proprietário</h2>";

// Simular dados de teste
$nome_teste = "João Silva Teste";
$documento_teste = "12345678901";
$telefone_teste = "(11) 99999-9999";
$email_teste = "joao.teste@email.com";
$endereco_teste = "Rua Teste, 123";

try {
    echo "<h3>1️⃣ Testando conexão com banco...</h3>";
    $test_query = db_query("SELECT COUNT(*) as total FROM proprietarios");
    echo "✅ Conexão OK! Total atual: " . $test_query[0]['total'] . "<br><br>";
    
    echo "<h3>2️⃣ Testando estrutura da tabela...</h3>";
    $structure = db_query("DESCRIBE proprietarios");
    $campos_existentes = [];
    foreach ($structure as $field) {
        $campos_existentes[] = $field['Field'];
        echo "📋 Campo: <strong>{$field['Field']}</strong> - Tipo: {$field['Type']}<br>";
    }
    echo "<br>";
    
    echo "<h3>3️⃣ Testando validações...</h3>";
    
    // Teste função normalizar_documento
    function normalizar_documento($valor) {
        return preg_replace('/\D+/', '', $valor);
    }
    
    function cpf_valido($cpf) {
        $cpf = normalizar_documento($cpf);
        if (strlen($cpf) < 11) return false;
        if (strlen($cpf) > 14) return false;
        if (strlen($cpf) >= 11) {
            if (strlen($cpf) === 14 && preg_match('/^(\d)\1{13}$/', $cpf)) return false;
            return true;
        }
        return false;
    }
    
    $doc_normalizado = normalizar_documento($documento_teste);
    echo "📄 Documento original: {$documento_teste}<br>";
    echo "📄 Documento normalizado: {$doc_normalizado}<br>";
    echo "✅ CPF válido: " . (cpf_valido($documento_teste) ? "SIM" : "NÃO") . "<br>";
    echo "📏 Tamanho: " . strlen($doc_normalizado) . " dígitos<br><br>";
    
    echo "<h3>4️⃣ Testando verificação de duplicidade...</h3>";
    $duplicata = db_query(
        "SELECT id_proprietario FROM proprietarios WHERE REPLACE(REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', ''), ' ', '') = ? LIMIT 1",
        [$doc_normalizado]
    );
    echo "🔍 Documento duplicado: " . (empty($duplicata) ? "NÃO" : "SIM (ID: " . $duplicata[0]['id_proprietario'] . ")") . "<br>";
    
    $email_dup = db_query(
        "SELECT id_proprietario FROM proprietarios WHERE LOWER(email) = LOWER(?) LIMIT 1",
        [$email_teste]
    );
    echo "📧 Email duplicado: " . (empty($email_dup) ? "NÃO" : "SIM (ID: " . $email_dup[0]['id_proprietario'] . ")") . "<br><br>";
    
    echo "<h3>5️⃣ Testando INSERT (SIMULAÇÃO)...</h3>";
    $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
    echo "🔧 SQL: " . htmlspecialchars($sql) . "<br>";
    echo "📝 Parâmetros:<br>";
    echo "&nbsp;&nbsp;- Nome: {$nome_teste}<br>";
    echo "&nbsp;&nbsp;- CPF: {$documento_teste}<br>";
    echo "&nbsp;&nbsp;- Telefone: {$telefone_teste}<br>";
    echo "&nbsp;&nbsp;- Email: {$email_teste}<br>";
    echo "&nbsp;&nbsp;- Endereço: {$endereco_teste}<br><br>";
    
    echo "<h3>6️⃣ Executando INSERT REAL...</h3>";
    
    // Verificar se já existe antes de inserir
    $existe = db_query("SELECT COUNT(*) as count FROM proprietarios WHERE nome = ? AND cpf = ?", [$nome_teste, $documento_teste]);
    if ($existe[0]['count'] > 0) {
        echo "⚠️ Registro de teste já existe, não inserindo duplicata<br>";
    } else {
        $result = db_query($sql, [$nome_teste, $documento_teste, $telefone_teste, $email_teste, $endereco_teste]);
        if ($result > 0) {
            echo "✅ <strong>INSERT executado com sucesso!</strong> ID: {$result}<br>";
            
            // Buscar o registro inserido
            $novo_registro = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$result]);
            if (!empty($novo_registro)) {
                echo "📋 Dados inseridos:<br>";
                foreach ($novo_registro[0] as $campo => $valor) {
                    echo "&nbsp;&nbsp;- {$campo}: " . htmlspecialchars($valor) . "<br>";
                }
            }
        } else {
            echo "❌ <strong>FALHA no INSERT!</strong> Retorno: " . var_export($result, true) . "<br>";
        }
    }
    
    echo "<br><h3>7️⃣ Verificando sessão PHP...</h3>";
    session_start();
    echo "🔑 Session ID: " . session_id() . "<br>";
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        echo "🔐 CSRF Token criado: " . substr($_SESSION['csrf_token'], 0, 16) . "...<br>";
    } else {
        echo "✅ CSRF Token existente: " . substr($_SESSION['csrf_token'], 0, 16) . "...<br>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ ERRO CAPTURADO:</h3>";
    echo "<div style='background: #fee; padding: 10px; border-left: 4px solid red;'>";
    echo "<strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Stack Trace:</strong><br>";
    echo "<pre style='font-size: 11px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📝 Formulário de Teste Manual:</h3>";
echo '<form method="POST" action="private/imoveis/proprietario-cadastrar.php" style="border: 1px solid #ccc; padding: 20px; background: #f9f9f9;">';
echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">';
echo '<p><label>Nome: <input type="text" name="nome" value="Teste Manual" required style="width: 200px;"></label></p>';
echo '<p><label>Tipo: <select name="tipo_documento"><option value="cpf">CPF</option><option value="cnpj">CNPJ</option></select></label></p>';
echo '<p><label>Documento: <input type="text" name="documento" value="98765432100" style="width: 200px;"></label></p>';
echo '<p><label>Telefone: <input type="text" name="telefone" value="(11) 88888-8888" style="width: 200px;"></label></p>';
echo '<p><label>Email: <input type="email" name="email" value="teste.manual@email.com" style="width: 200px;"></label></p>';
echo '<p><label>Endereço: <textarea name="endereco" style="width: 300px;">Rua Manual, 456</textarea></label></p>';
echo '<p><button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px;">🚀 Testar Cadastro</button></p>';
echo '</form>';

echo "<br><a href='private/imoveis/proprietario-cadastrar.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 Ir para Cadastro Original</a>";
?>