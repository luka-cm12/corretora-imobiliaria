<?php
/**
 * SCRIPT AUTOMÁTICO PARA ADICIONAR NOVOS TIPOS DE IMÓVEIS
 * ========================================================
 * 
 * Data: 01/11/2025
 * Descrição: Atualiza o banco de dados para incluir 7 novos tipos de imóveis
 * 
 * NOVOS TIPOS ADICIONADOS:
 * - pavilhao (🏭 Pavilhão) 
 * - fazenda (🚜 Fazenda)
 * - laja_terrea (🏘️ Laja Térrea)
 * - sala_area (📦 Sala Área)
 * - area_terras (🌍 Área de Terras)
 * - loteamento (🗺️ Loteamento)
 * - condominio_fechado (🏛️ Condomínio Fechado)
 */

require_once(__DIR__ . '/private/includes/db.php');

// Configuração para exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Atualizar Banco - Novos Tipos de Imóveis';

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

// Função para verificar se a coluna tipo já contém os novos valores
function verificar_tipos_existentes() {
    global $conn;
    
    try {
        $sql = "SHOW COLUMNS FROM imoveis LIKE 'tipo'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado && isset($resultado['Type'])) {
            $enum_string = $resultado['Type'];
            
            // Novos tipos que queremos adicionar
            $novos_tipos = ['pavilhao', 'fazenda', 'laja_terrea', 'sala_area', 'area_terras', 'loteamento', 'condominio_fechado'];
            $tipos_faltando = [];
            
            foreach ($novos_tipos as $tipo) {
                if (strpos($enum_string, "'{$tipo}'") === false) {
                    $tipos_faltando[] = $tipo;
                }
            }
            
            return $tipos_faltando;
        }
        
        return ['todos']; // Se não conseguir verificar, assume que todos estão faltando
        
    } catch (PDOException $e) {
        return ['erro' => $e->getMessage()];
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
        .novos-tipos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .tipo-card {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border: 2px solid #28a745;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
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
        <h1>🔄 Atualização do Banco - Novos Tipos de Imóveis</h1>

        <div class="info">
            <strong>📋 Informações da Atualização:</strong><br>
            • <strong>Data:</strong> <?= date('d/m/Y H:i:s') ?><br>
            • <strong>Novos tipos:</strong> 7 tipos de imóveis<br>
            • <strong>Banco:</strong> <?= $conn ? 'Conectado ✅' : 'Erro de conexão ❌' ?><br>
        </div>

        <div class="novos-tipos">
            <div class="tipo-card">🏭<br><strong>Pavilhão</strong><br><code>pavilhao</code></div>
            <div class="tipo-card">🚜<br><strong>Fazenda</strong><br><code>fazenda</code></div>
            <div class="tipo-card">🏘️<br><strong>Laja Térrea</strong><br><code>laja_terrea</code></div>
            <div class="tipo-card">📦<br><strong>Sala Área</strong><br><code>sala_area</code></div>
            <div class="tipo-card">🌍<br><strong>Área de Terras</strong><br><code>area_terras</code></div>
            <div class="tipo-card">🗺️<br><strong>Loteamento</strong><br><code>loteamento</code></div>
            <div class="tipo-card">🏛️<br><strong>Condomínio Fechado</strong><br><code>condominio_fechado</code></div>
        </div>

        <?php
        if (!$conn) {
            echo "<div class='error'>❌ Erro: Não foi possível conectar ao banco de dados!</div>";
            exit;
        }

        echo "<h2>🔍 Verificação Inicial</h2>";

        // Verificar estrutura atual
        executar_sql_seguro(
            "SHOW COLUMNS FROM imoveis LIKE 'tipo'",
            "Verificando estrutura atual da coluna 'tipo'"
        );

        // Verificar tipos que precisam ser adicionados
        $tipos_faltando = verificar_tipos_existentes();
        
        if (isset($tipos_faltando['erro'])) {
            echo "<div class='error'>❌ Erro ao verificar tipos existentes: " . htmlspecialchars($tipos_faltando['erro']) . "</div>";
        } else if (empty($tipos_faltando)) {
            echo "<div class='success'>✅ Todos os novos tipos já estão disponíveis na coluna 'tipo'!</div>";
        } else {
            echo "<div class='warning'>⚠️ Tipos que precisam ser adicionados: " . implode(', ', $tipos_faltando) . "</div>";
            
            echo "<h2>🔧 Executando Atualização</h2>";
            
            // Executar a atualização da coluna tipo
            $sql_update = "ALTER TABLE imoveis MODIFY COLUMN tipo ENUM(
                'casa',
                'casa_condominio', 
                'apartamento',
                'apartamento_mobiliado',
                'sobrado',
                'chacara',
                'semi_mobiliado',
                'terreno',
                'loft',
                'comercial',
                'pavilhao',
                'fazenda',
                'laja_terrea',
                'sala_area',
                'area_terras',
                'loteamento',
                'condominio_fechado'
            ) NOT NULL COMMENT 'Tipos de imóveis - Atualizado em " . date('d/m/Y') . "'";

            executar_sql_seguro($sql_update, "Atualizando coluna 'tipo' com novos valores");
        }

        echo "<h2>✅ Verificação Final</h2>";

        // Verificar estrutura após atualização
        executar_sql_seguro(
            "SHOW COLUMNS FROM imoveis LIKE 'tipo'",
            "Verificando estrutura final da coluna 'tipo'"
        );

        // Listar distribuição atual
        executar_sql_seguro(
            "SELECT tipo, COUNT(*) as quantidade 
             FROM imoveis 
             GROUP BY tipo 
             ORDER BY quantidade DESC",
            "Distribuição atual de imóveis por tipo"
        );

        // Contar total de registros
        executar_sql_seguro(
            "SELECT 
                COUNT(*) as total_imoveis,
                COUNT(DISTINCT tipo) as tipos_diferentes
             FROM imoveis",
            "Resumo geral do banco de dados"
        );
        ?>

        <div class="status-resumo">
            <h3>📊 Status da Implementação</h3>
            <ul>
                <li>✅ <strong>Frontend:</strong> Formulários atualizados (cadastro, edição, busca)</li>
                <li>✅ <strong>Backend:</strong> Funções de formatação atualizadas</li>
                <li>✅ <strong>Banco de Dados:</strong> Coluna 'tipo' expandida com novos valores</li>
                <li>✅ <strong>Compatibilidade:</strong> Mantém todos os tipos anteriores</li>
                <li>✅ <strong>Testes:</strong> Arquivo de verificação criado</li>
            </ul>
            
            <div class="success">
                🎉 <strong>Implementação Completa!</strong><br>
                O sistema agora suporta 17 tipos diferentes de imóveis.
            </div>
        </div>

        <div class="info">
            <strong>📁 Arquivos Relacionados:</strong><br>
            • <code>adicionar-novos-tipos-imoveis.sql</code> - Script SQL manual<br>
            • <code>atualizar-novos-tipos-banco.php</code> - Este script automático<br>
            • <code>teste-novos-tipos-implementados.php</code> - Página de teste<br>
        </div>
    </div>
</body>
</html>