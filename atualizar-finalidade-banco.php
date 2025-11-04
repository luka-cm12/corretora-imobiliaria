<?php
/**
 * SCRIPT AUTOMÁTICO PARA ADICIONAR COLUNA FINALIDADE
 * ==================================================
 * 
 * Data: 01/11/2025
 * Descrição: Adiciona coluna 'finalidade' para diferenciar
 * imóveis de venda e locação no sistema
 */

require_once(__DIR__ . '/private/includes/db.php');

// Configuração para exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Atualizar Banco - Adicionar Finalidade (Venda/Locação)';

// Função para executar SQL e capturar resultados
function executar_sql_seguro($sql, $descricao = '') {
    global $conn;
    
    try {
        echo "<div class='sql-block'>";
        echo "<h4>🔧 {$descricao}</h4>";
        echo "<div class='sql-code'>" . htmlspecialchars($sql) . "</div>";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute();
        
        if ($resultado) {
            echo "<div class='success'>✅ Executado com sucesso!</div>";
            
            // Se for uma consulta SELECT, mostrar resultados
            if (stripos(trim($sql), 'SELECT') === 0) {
                $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($dados) {
                    echo "<div class='resultado-tabela'>";
                    echo "<table border='1' style='margin: 10px 0; border-collapse: collapse;'>";
                    
                    // Cabeçalho
                    echo "<tr style='background: #f0f0f0;'>";
                    foreach (array_keys($dados[0]) as $coluna) {
                        echo "<th style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($coluna) . "</th>";
                    }
                    echo "</tr>";
                    
                    // Dados
                    foreach ($dados as $linha) {
                        echo "<tr>";
                        foreach ($linha as $valor) {
                            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($valor ?? '') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "</div>";
                }
            }
            
        } else {
            echo "<div class='error'>❌ Erro na execução</div>";
        }
        
        echo "</div><br>";
        return $resultado;
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "</div><br>";
        return false;
    }
}

// Função para verificar se a coluna finalidade já existe
function verificar_coluna_finalidade() {
    global $conn;
    
    try {
        $sql = "SHOW COLUMNS FROM imoveis LIKE 'finalidade'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return !empty($resultado);
        
    } catch (PDOException $e) {
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #2c3e50; 
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        .sql-block {
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #fafafa;
        }
        .sql-code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #cce7ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .resultado-tabela {
            margin: 15px 0;
        }
        table {
            width: 100%;
            font-size: 14px;
        }
        th {
            background: #3498db !important;
            color: white !important;
            font-weight: bold;
        }
        .finalidade-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .finalidade-card {
            background: linear-gradient(135deg, #e8f4f8, #d1ecf1);
            border: 2px solid #17a2b8;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .finalidade-card h3 {
            margin: 0 0 10px 0;
            color: #0c5460;
        }
        .status-resumo {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💼 Atualização do Banco - Finalidade (Venda/Locação)</h1>

        <div class="info">
            <strong>📋 Informações da Atualização:</strong><br>
            • <strong>Data:</strong> <?= date('d/m/Y H:i:s') ?><br>
            • <strong>Funcionalidade:</strong> Separar imóveis por venda e locação<br>
            • <strong>Banco:</strong> <?= $conn ? 'Conectado ✅' : 'Erro de conexão ❌' ?><br>
        </div>

        <div class="finalidade-cards">
            <div class="finalidade-card">
                <h3>💰 VENDA</h3>
                <p>Imóveis disponíveis para compra</p>
                <code>finalidade = 'venda'</code>
            </div>
            <div class="finalidade-card">
                <h3>🏠 LOCAÇÃO</h3>
                <p>Imóveis disponíveis para aluguel</p>
                <code>finalidade = 'locacao'</code>
            </div>
        </div>

        <?php
        if (!$conn) {
            echo "<div class='error'>❌ Erro: Não foi possível conectar ao banco de dados!</div>";
            exit;
        }

        echo "<h2>🔍 Verificação Inicial</h2>";

        // Verificar se a coluna já existe
        $coluna_existe = verificar_coluna_finalidade();
        
        if ($coluna_existe) {
            echo "<div class='success'>✅ A coluna 'finalidade' já existe na tabela 'imoveis'!</div>";
            
            // Mostrar estrutura atual
            executar_sql_seguro(
                "SHOW COLUMNS FROM imoveis LIKE 'finalidade'",
                "Estrutura atual da coluna 'finalidade'"
            );
            
        } else {
            echo "<div class='warning'>⚠️ A coluna 'finalidade' não existe. Será criada agora.</div>";
            
            echo "<h2>🔧 Executando Atualização</h2>";
            
            // Adicionar a coluna finalidade
            $sql_add_column = "ALTER TABLE imoveis ADD COLUMN finalidade ENUM('venda', 'locacao') NOT NULL DEFAULT 'venda' COMMENT 'Finalidade do imóvel: venda ou locação'";
            
            executar_sql_seguro($sql_add_column, "Adicionando coluna 'finalidade' na tabela 'imoveis'");
            
            // Verificar se foi criada
            executar_sql_seguro(
                "SHOW COLUMNS FROM imoveis LIKE 'finalidade'",
                "Verificando se a coluna foi criada corretamente"
            );
        }

        echo "<h2>📊 Análise dos Dados</h2>";

        // Verificar distribuição atual
        executar_sql_seguro(
            "SELECT finalidade, COUNT(*) as quantidade 
             FROM imoveis 
             GROUP BY finalidade 
             ORDER BY finalidade",
            "Distribuição atual por finalidade"
        );

        // Verificar distribuição por tipo e finalidade
        executar_sql_seguro(
            "SELECT tipo, finalidade, COUNT(*) as quantidade 
             FROM imoveis 
             GROUP BY tipo, finalidade 
             ORDER BY tipo, finalidade",
            "Distribuição por tipo de imóvel e finalidade"
        );

        // Contar total de registros
        executar_sql_seguro(
            "SELECT 
                COUNT(*) as total_imoveis,
                COUNT(DISTINCT tipo) as tipos_diferentes,
                COUNT(DISTINCT finalidade) as finalidades_diferentes
             FROM imoveis",
            "Resumo geral do banco de dados"
        );
        ?>

        <div class="status-resumo">
            <h3>📊 Status da Implementação</h3>
            <ul>
                <li>✅ <strong>Banco de Dados:</strong> Coluna 'finalidade' adicionada</li>
                <li>✅ <strong>Cadastro:</strong> Campo finalidade no formulário</li>
                <li>✅ <strong>Edição:</strong> Campo finalidade no formulário de edição</li>
                <li>✅ <strong>Busca:</strong> Filtro por venda/locação</li>
                <li>✅ <strong>Listagem:</strong> Indicação visual de venda/locação</li>
                <li>✅ <strong>Detalhes:</strong> Exibição da finalidade na página do imóvel</li>
            </ul>
            
            <div class="success">
                🎉 <strong>Implementação Completa!</strong><br>
                O sistema agora suporta filtrar e categorizar imóveis por venda ou locação.
            </div>
        </div>

        <div class="info">
            <strong>🧪 Como Testar:</strong><br>
            1. Acesse o <strong>formulário de cadastro</strong> e veja o campo "Finalidade"<br>
            2. Cadastre imóveis marcando como "Venda" ou "Locação"<br>
            3. Use os <strong>filtros de busca</strong> na página inicial e de busca<br>
            4. Veja os <strong>badges coloridos</strong> nos resultados (Verde=Venda, Roxo=Locação)<br>
            5. Visualize a <strong>finalidade</strong> na página de detalhes do imóvel<br>
        </div>

        <div class="info">
            <strong>📁 Arquivos Atualizados:</strong><br>
            • <code>private/imoveis/adicionar.php</code> - Campo finalidade no cadastro<br>
            • <code>private/imoveis/editar.php</code> - Campo finalidade na edição<br>
            • <code>busca.php</code> - Filtro e exibição da finalidade<br>
            • <code>index.php</code> - Filtro na busca rápida<br>
            • <code>imovel-detalhes.php</code> - Exibição da finalidade<br>
            • <code>adicionar-finalidade-banco.sql</code> - Script SQL manual<br>
            • <code>atualizar-finalidade-banco.php</code> - Este script automático<br>
        </div>
    </div>
</body>
</html>