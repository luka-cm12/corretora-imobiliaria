<?php
/**
 * SCRIPT PHP PARA ADICIONAR COLUNAS NO BANCO DE DADOS
 * 
 * Execute este arquivo via navegador ou linha de comando
 * Exemplo: http://localhost/corretora-imobiliaria-11/adicionar-colunas-banco.php
 * 
 * IMPORTANTE: Faça backup do banco antes de executar!
 */

// Inclui as configurações do banco
require_once(__DIR__ . '/private/includes/db.php');

// Função para verificar se uma coluna existe
function colunaExiste($tabela, $coluna) {
    try {
        $result = db_query("SHOW COLUMNS FROM $tabela LIKE '$coluna'");
        return count($result) > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Função para adicionar coluna com verificação
function adicionarColuna($tabela, $coluna, $definicao, $descricao) {
    if (colunaExiste($tabela, $coluna)) {
        echo "✅ Coluna '$coluna' já existe<br>";
        return true;
    }
    
    try {
        $sql = "ALTER TABLE $tabela ADD $coluna $definicao";
        db_query($sql);
        echo "✅ Coluna '$coluna' adicionada com sucesso - $descricao<br>";
        return true;
    } catch (Exception $e) {
        echo "❌ Erro ao adicionar coluna '$coluna': " . $e->getMessage() . "<br>";
        return false;
    }
}

// Função para modificar coluna existente
function modificarColuna($tabela, $coluna, $definicao, $descricao) {
    try {
        $sql = "ALTER TABLE $tabela MODIFY $coluna $definicao";
        db_query($sql);
        echo "✅ Coluna '$coluna' modificada com sucesso - $descricao<br>";
        return true;
    } catch (Exception $e) {
        echo "❌ Erro ao modificar coluna '$coluna': " . $e->getMessage() . "<br>";
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Colunas no Banco</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .code { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔧 Adicionar Colunas no Banco de Dados</h1>
    
    <?php
    try {
        echo "<div class='status warning'>";
        echo "<strong>⚠️ ATENÇÃO:</strong> Este script irá modificar a estrutura do banco de dados. ";
        echo "Certifique-se de ter feito um backup antes de continuar!";
        echo "</div>";
        
        // Verificar conexão com o banco
        echo "<h2>1. Verificando conexão com o banco...</h2>";
        $test = db_query("SELECT 1");
        echo "✅ Conexão com banco estabelecida com sucesso<br><br>";
        
        // Verificar estrutura atual
        echo "<h2>2. Estrutura atual da tabela 'imoveis':</h2>";
        echo "<div class='code'>";
        $columns = db_query("DESCRIBE imoveis");
        foreach ($columns as $col) {
            echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] == 'YES' ? ' NULL' : ' NOT NULL') . "<br>";
        }
        echo "</div>";
        
        echo "<h2>3. Adicionando novas colunas...</h2>";
        
        // 1. Características e posição solar
        echo "<h3>📋 Características e Posição Solar</h3>";
        adicionarColuna('imoveis', 'caracteristicas', 'TEXT', 'Características do imóvel em formato JSON');
        adicionarColuna('imoveis', 'posicao_solar', 'VARCHAR(10)', 'Posição solar do imóvel');
        adicionarColuna('imoveis', 'cep', 'CHAR(8)', 'CEP do imóvel');
        
        // 2. Valores financeiros
        echo "<h3>💰 Valores Financeiros Internos</h3>";
        adicionarColuna('imoveis', 'valor_condominio', 'DECIMAL(10,2) DEFAULT 0.00', 'Valor mensal do condomínio');
        adicionarColuna('imoveis', 'valor_iptu', 'DECIMAL(10,2) DEFAULT 0.00', 'Valor anual do IPTU');
        
        // 3. Campos administrativos
        echo "<h3>📊 Campos Administrativos</h3>";
        adicionarColuna('imoveis', 'matricula', 'VARCHAR(100)', 'Número da matrícula do imóvel');
        adicionarColuna('imoveis', 'parcelas_iptu', 'VARCHAR(50)', 'Número de parcelas do IPTU');
        adicionarColuna('imoveis', 'exclusividade', 'TINYINT(1) DEFAULT 0', 'Imóvel em exclusividade');
        adicionarColuna('imoveis', 'taxa_intermediacao', 'DECIMAL(5,2) DEFAULT 0.00', 'Taxa de intermediação em %');
        
        // 4. Áreas detalhadas
        echo "<h3>📐 Áreas Detalhadas</h3>";
        adicionarColuna('imoveis', 'area_privativa', 'DECIMAL(10,2) DEFAULT 0.00', 'Área privativa em m²');
        adicionarColuna('imoveis', 'area_comum', 'DECIMAL(10,2) DEFAULT 0.00', 'Área comum em m²');
        
        // 5. Controle de chaves
        echo "<h3>🔑 Controle de Chaves</h3>";
        adicionarColuna('imoveis', 'chaves_tipo', 'VARCHAR(255)', 'Tipos de chaves disponíveis');
        adicionarColuna('imoveis', 'chaves_copias', 'INT DEFAULT 0', 'Quantidade de cópias das chaves');
        adicionarColuna('imoveis', 'observacoes_chaves', 'TEXT', 'Observações sobre as chaves');
        
        // 6. Atualizar enum do tipo
        echo "<h3>🏠 Atualizando Tipos de Imóveis</h3>";
        modificarColuna('imoveis', 'tipo', "ENUM('casa','casa_condominio','apartamento','apartamento_mobiliado','sobrado','chacara','semi_mobiliado','terreno','loft','comercial') NOT NULL", 'Todos os tipos de imóvel incluindo Loft');
        
        // Verificação final
        echo "<h2>4. Estrutura final da tabela:</h2>";
        echo "<div class='code'>";
        $final_columns = db_query("DESCRIBE imoveis");
        foreach ($final_columns as $col) {
            $is_new = !in_array($col['Field'], ['id', 'titulo', 'descricao', 'tipo', 'cidade', 'bairro', 'endereco', 'preco', 'area', 'quartos', 'banheiros', 'garagem', 'imagens', 'destaque', 'data_cadastro', 'id_proprietario']);
            echo ($is_new ? '<strong style="color: green;">🆕 ' : '') . $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] == 'YES' ? ' NULL' : ' NOT NULL') . ($is_new ? '</strong>' : '') . "<br>";
        }
        echo "</div>";
        
        echo "<div class='status success'>";
        echo "<h2>✅ Processo Concluído!</h2>";
        echo "Todas as colunas foram processadas. Verifique os logs acima para detalhes.";
        echo "</div>";
        
        // Exemplo de uso
        echo "<h2>5. Exemplos de uso das novas colunas:</h2>";
        echo "<div class='code'>";
        echo "<strong>Inserir características em JSON:</strong><br>";
        echo "UPDATE imoveis SET caracteristicas = '[\"suite\",\"piscina\",\"churrasqueira\"]' WHERE id = 1;<br><br>";
        
        echo "<strong>Definir posição solar:</strong><br>";
        echo "UPDATE imoveis SET posicao_solar = 'norte' WHERE id = 1;<br><br>";
        
        echo "<strong>Buscar por características:</strong><br>";
        echo "SELECT * FROM imoveis WHERE JSON_CONTAINS(caracteristicas, '\"piscina\"');<br><br>";
        
        echo "<strong>Buscar por posição solar:</strong><br>";
        echo "SELECT * FROM imoveis WHERE posicao_solar = 'norte';<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='status error'>";
        echo "<h2>❌ Erro durante a execução:</h2>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    ?>
    
    <div style="margin-top: 30px; padding: 15px; background-color: #f0f8ff; border-radius: 5px;">
        <h3>🔗 Próximos passos:</h3>
        <ol>
            <li>Teste o formulário de adição de imóveis</li>
            <li>Verifique se todos os campos estão sendo salvos corretamente</li>
            <li>Teste as funcionalidades de busca por características</li>
            <li>Configure os campos internos conforme necessário</li>
        </ol>
    </div>
    
</body>
</html>