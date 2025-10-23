<?php
// Script PHP para verificar e mostrar o que precisa ser adicionado no banco
include_once 'private/includes/db.php';

echo "🔧 VERIFICAÇÃO DO BANCO DE DADOS\n";
echo "================================\n\n";

// 1. Verificar estrutura atual da tabela imoveis
echo "📊 ESTRUTURA ATUAL DA TABELA 'imoveis':\n";
echo "---------------------------------------\n";

try {
    $sql_structure = "DESCRIBE imoveis";
    $result = mysqli_query($conn, $sql_structure);
    
    $existing_columns = [];
    if ($result) {
        echo "Colunas existentes:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_columns[] = $row['Field'];
            echo "✓ {$row['Field']} ({$row['Type']}) - {$row['Null']}\n";
        }
    } else {
        echo "❌ Erro ao verificar estrutura da tabela\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit;
}

// 2. Definir colunas necessárias para o sistema completo
echo "\n🎯 COLUNAS NECESSÁRIAS PARA O SISTEMA:\n";
echo "--------------------------------------\n";

$required_columns = [
    // Colunas básicas (já devem existir)
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'titulo' => 'VARCHAR(255) NOT NULL',
    'descricao' => 'TEXT NOT NULL',
    'tipo' => 'VARCHAR(50) NOT NULL',
    'cidade' => 'VARCHAR(100) NOT NULL',
    'bairro' => 'VARCHAR(100) NOT NULL',
    'endereco' => 'VARCHAR(255)',
    'preco' => 'DECIMAL(12,2) NOT NULL',
    'area' => 'DECIMAL(8,2)',
    'quartos' => 'INT DEFAULT 0',
    'banheiros' => 'INT DEFAULT 0',
    'garagem' => 'INT DEFAULT 0',
    'imagens' => 'TEXT',
    'destaque' => 'TINYINT(1) DEFAULT 0',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    
    // Colunas para as novas funcionalidades
    'caracteristicas' => 'TEXT NULL COMMENT "Características em JSON"',
    'posicao_solar' => 'VARCHAR(10) NULL COMMENT "Posição solar"',
    'cep' => 'CHAR(8) NULL COMMENT "CEP do imóvel"',
    
    // Colunas administrativas opcionais
    'valor_condominio' => 'DECIMAL(10,2) NULL COMMENT "Valor mensal condomínio"',
    'valor_iptu' => 'DECIMAL(10,2) NULL COMMENT "Valor mensal IPTU"',
    'matricula' => 'VARCHAR(100) NULL COMMENT "Matrícula do imóvel"',
    'exclusividade' => 'TINYINT(1) DEFAULT 0 COMMENT "Exclusividade"',
    'chaves_tipo' => 'VARCHAR(255) NULL COMMENT "Tipos de chaves"',
    'chaves_copias' => 'INT DEFAULT 0 COMMENT "Cópias das chaves"',
    'observacoes_chaves' => 'TEXT NULL COMMENT "Obs. sobre chaves"',
    'area_privativa' => 'DECIMAL(8,2) NULL COMMENT "Área privativa"',
    'area_comum' => 'DECIMAL(8,2) NULL COMMENT "Área comum"',
    'parcelas_iptu' => 'VARCHAR(50) NULL COMMENT "Parcelas IPTU"',
    'taxa_intermediacao' => 'DECIMAL(5,2) NULL COMMENT "Taxa intermediação"',
    'id_proprietario' => 'INT NULL COMMENT "ID do proprietário"'
];

// 3. Verificar quais colunas estão faltando
echo "\n🔍 ANÁLISE DE COLUNAS FALTANTES:\n";
echo "--------------------------------\n";

$missing_columns = [];
$essential_missing = [];

$essential_columns = ['caracteristicas', 'posicao_solar', 'cep'];

foreach ($required_columns as $column => $definition) {
    if (!in_array($column, $existing_columns)) {
        $missing_columns[] = $column;
        
        if (in_array($column, $essential_columns)) {
            $essential_missing[] = $column;
            echo "❌ ESSENCIAL: $column - $definition\n";
        } else {
            echo "⚠️  OPCIONAL: $column - $definition\n";
        }
    } else {
        if (in_array($column, $essential_columns)) {
            echo "✅ ESSENCIAL: $column (já existe)\n";
        }
    }
}

// 4. Gerar comandos SQL específicos
if (!empty($missing_columns)) {
    echo "\n📝 COMANDOS SQL PARA EXECUTAR:\n";
    echo "------------------------------\n";
    echo "Execute estes comandos no seu MySQL/phpMyAdmin:\n\n";
    
    // Comandos essenciais primeiro
    if (in_array('caracteristicas', $missing_columns)) {
        echo "-- ESSENCIAL para características:\n";
        echo "ALTER TABLE imoveis ADD caracteristicas TEXT NULL COMMENT 'Características em JSON';\n\n";
    }
    
    if (in_array('posicao_solar', $missing_columns)) {
        echo "-- ESSENCIAL para posição solar:\n";
        echo "ALTER TABLE imoveis ADD posicao_solar VARCHAR(10) NULL COMMENT 'Posição solar';\n\n";
    }
    
    if (in_array('cep', $missing_columns)) {
        echo "-- ESSENCIAL para CEP:\n";
        echo "ALTER TABLE imoveis ADD cep CHAR(8) NULL COMMENT 'CEP do imóvel';\n\n";
    }
    
    // Comandos opcionais
    echo "-- OPCIONAIS (execute se quiser funcionalidades administrativas):\n";
    foreach ($missing_columns as $column) {
        if (!in_array($column, $essential_columns)) {
            $definition = $required_columns[$column];
            echo "ALTER TABLE imoveis ADD $column $definition;\n";
        }
    }
    
} else {
    echo "\n✅ PARABÉNS! Todas as colunas necessárias já existem!\n";
}

// 5. Verificar se há imóveis com características
echo "\n📊 ESTATÍSTICAS ATUAIS:\n";
echo "-----------------------\n";

$sql_count = "SELECT COUNT(*) as total FROM imoveis";
$result_count = mysqli_query($conn, $sql_count);
$total_imoveis = mysqli_fetch_assoc($result_count)['total'];

echo "Total de imóveis: $total_imoveis\n";

if (in_array('caracteristicas', $existing_columns)) {
    $sql_carac = "SELECT COUNT(*) as com_caracteristicas FROM imoveis WHERE caracteristicas IS NOT NULL AND caracteristicas != ''";
    $result_carac = mysqli_query($conn, $sql_carac);
    $com_caracteristicas = mysqli_fetch_assoc($result_carac)['com_caracteristicas'];
    echo "Imóveis com características: $com_caracteristicas\n";
}

if (in_array('posicao_solar', $existing_columns)) {
    $sql_solar = "SELECT COUNT(*) as com_solar FROM imoveis WHERE posicao_solar IS NOT NULL AND posicao_solar != ''";
    $result_solar = mysqli_query($conn, $sql_solar);
    $com_solar = mysqli_fetch_assoc($result_solar)['com_solar'];
    echo "Imóveis com posição solar: $com_solar\n";
}

// 6. Prioridades
echo "\n🎯 PRIORIDADES DE IMPLEMENTAÇÃO:\n";
echo "--------------------------------\n";
echo "1. ALTA PRIORIDADE (execute primeiro):\n";

if (!empty($essential_missing)) {
    foreach ($essential_missing as $col) {
        echo "   ❗ $col\n";
    }
} else {
    echo "   ✅ Todas as colunas essenciais já existem!\n";
}

echo "\n2. BAIXA PRIORIDADE (opcionais):\n";
$optional_missing = array_diff($missing_columns, $essential_missing);
if (!empty($optional_missing)) {
    foreach ($optional_missing as $col) {
        echo "   ⚪ $col\n";
    }
} else {
    echo "   ✅ Todas as colunas opcionais já existem!\n";
}

echo "\n📋 RESUMO:\n";
echo "----------\n";
echo "Colunas existentes: " . count($existing_columns) . "\n";
echo "Colunas faltantes: " . count($missing_columns) . "\n";
echo "Essenciais faltantes: " . count($essential_missing) . "\n";

mysqli_close($conn);
?>