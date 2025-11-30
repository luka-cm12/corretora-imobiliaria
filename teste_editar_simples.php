<?php
// Versão simplificada para testar o proprietario-editar.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/private/includes/db.php');

// Verificar se o ID foi fornecido
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Erro: ID não fornecido ou inválido. Use: teste_editar_simples.php?id=2");
}

echo "<h1>Teste Simples - Editar Proprietário</h1>";

try {
    echo "<h2>1. Buscando proprietário ID: {$id}</h2>";
    
    // Buscar proprietário
    $proprietario = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id]);
    
    if (empty($proprietario)) {
        die("<p style='color: red;'>❌ Proprietário não encontrado com ID: {$id}</p>");
    }
    
    $proprietario = $proprietario[0];
    echo "<p style='color: green;'>✅ Proprietário encontrado: " . htmlspecialchars($proprietario['nome']) . "</p>";
    
    echo "<h2>2. Dados atuais:</h2>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    foreach ($proprietario as $campo => $valor) {
        $valor_exibir = $valor ?? '<em>NULL</em>';
        echo "<tr><td><strong>{$campo}</strong></td><td>" . htmlspecialchars($valor_exibir) . "</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Imóveis do proprietário:</h2>";
    $imoveis = db_query("SELECT id_imovel, titulo, tipo, cidade, preco FROM imoveis WHERE id_proprietario = ? ORDER BY titulo", [$id]);
    
    if (count($imoveis) > 0) {
        echo "<p>✅ Encontrados " . count($imoveis) . " imóvel(is):</p>";
        echo "<ul>";
        foreach ($imoveis as $imovel) {
            echo "<li>ID {$imovel['id_imovel']}: {$imovel['titulo']} ({$imovel['tipo']} em {$imovel['cidade']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>⚠️ Nenhum imóvel cadastrado para este proprietário.</p>";
    }
    
    echo "<h2>4. Estrutura da tabela:</h2>";
    $colunas = db_query("SHOW COLUMNS FROM proprietarios");
    $tem_tipo_documento = false;
    $tem_cnpj = false;
    
    echo "<ul>";
    foreach ($colunas as $col) {
        echo "<li>{$col['Field']} ({$col['Type']})</li>";
        if ($col['Field'] === 'tipo_documento') $tem_tipo_documento = true;
        if ($col['Field'] === 'cnpj') $tem_cnpj = true;
    }
    echo "</ul>";
    
    echo "<p>Estrutura nova (tipo_documento + cnpj): " . ($tem_tipo_documento && $tem_cnpj ? "✅ SIM" : "❌ NÃO") . "</p>";
    
    echo "<h2>5. Formulário de teste:</h2>";
    
    // Determinar valores atuais
    $tipo_atual = $proprietario['tipo_documento'] ?? 'cpf';
    $documento_atual = '';
    
    if ($tipo_atual === 'cpf' && !empty($proprietario['cpf'])) {
        $documento_atual = $proprietario['cpf'];
    } elseif ($tipo_atual === 'cnpj' && !empty($proprietario['cnpj'] ?? '')) {
        $documento_atual = $proprietario['cnpj'];
    }
    
    ?>
    <form method="post" style="max-width: 600px; margin: 20px 0;">
        <p><strong>Nome:</strong><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($proprietario['nome']) ?>" required style="width: 100%; padding: 8px;"></p>
        
        <p><strong>Tipo de Documento:</strong><br>
        <select name="tipo_documento" style="width: 100%; padding: 8px;">
            <option value="cpf" <?= $tipo_atual === 'cpf' ? 'selected' : '' ?>>CPF</option>
            <option value="cnpj" <?= $tipo_atual === 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
        </select></p>
        
        <p><strong>Documento:</strong><br>
        <input type="text" name="documento" value="<?= htmlspecialchars($documento_atual) ?>" required style="width: 100%; padding: 8px;"></p>
        
        <p><strong>Telefone:</strong><br>
        <input type="text" name="telefone" value="<?= htmlspecialchars($proprietario['telefone'] ?? '') ?>" style="width: 100%; padding: 8px;"></p>
        
        <p><strong>Email:</strong><br>
        <input type="email" name="email" value="<?= htmlspecialchars($proprietario['email'] ?? '') ?>" style="width: 100%; padding: 8px;"></p>
        
        <p><strong>Endereço:</strong><br>
        <textarea name="endereco" style="width: 100%; padding: 8px; height: 80px;"><?= htmlspecialchars($proprietario['endereco'] ?? '') ?></textarea></p>
        
        <input type="hidden" name="csrf_token" value="teste_token">
        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px;">Salvar Alterações</button>
    </form>
    
    <?php
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h2>6. Processando formulário:</h2>";
        
        $nome = trim($_POST['nome'] ?? '');
        $tipo_documento = trim($_POST['tipo_documento'] ?? 'cpf');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        
        echo "<p>✅ Dados recebidos:</p>";
        echo "<ul>";
        echo "<li>Nome: " . htmlspecialchars($nome) . "</li>";
        echo "<li>Tipo: {$tipo_documento}</li>";
        echo "<li>Documento: " . htmlspecialchars($documento) . "</li>";
        echo "<li>Telefone: " . htmlspecialchars($telefone) . "</li>";
        echo "<li>Email: " . htmlspecialchars($email) . "</li>";
        echo "</ul>";
        
        if ($tem_tipo_documento && $tem_cnpj) {
            if ($tipo_documento === 'cpf') {
                $sql = "UPDATE proprietarios SET nome = ?, tipo_documento = 'cpf', cpf = ?, cnpj = NULL, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
                $params = [$nome, $documento, $telefone ?: null, $email ?: null, $endereco ?: null, $id];
            } else {
                $sql = "UPDATE proprietarios SET nome = ?, tipo_documento = 'cnpj', cpf = NULL, cnpj = ?, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
                $params = [$nome, $documento, $telefone ?: null, $email ?: null, $endereco ?: null, $id];
            }
        } else {
            $sql = "UPDATE proprietarios SET nome = ?, cpf = ?, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
            $params = [$nome, $documento, $telefone ?: null, $email ?: null, $endereco ?: null, $id];
        }
        
        echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql) . "</p>";
        
        try {
            $resultado = db_query($sql, $params);
            echo "<p style='color: green;'>✅ Atualização executada com sucesso!</p>";
            echo "<p><strong><a href='teste_editar_simples.php?id={$id}'>Recarregar para ver alterações</a></strong></p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro na atualização: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<h2>7. Links úteis:</h2>";
    echo "<ul>";
    echo "<li><a href='private/imoveis/proprietario-editar.php?id={$id}'>Abrir página oficial de edição</a></li>";
    echo "<li><a href='private/imoveis/proprietarios-listar.php'>Voltar para listagem</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO CRÍTICO:</h2>";
    echo "<p>Mensagem: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Arquivo: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
table { width: 100%; }
td { padding: 8px; border: 1px solid #ddd; }
</style>