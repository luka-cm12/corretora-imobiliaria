<?php
// Script para adicionar automaticamente as colunas necessárias no banco
include_once 'private/includes/db.php';

echo "🔧 ATUALIZANDO BANCO DE DADOS AUTOMATICAMENTE\n";
echo "==============================================\n\n";

// Função para executar SQL e mostrar resultado
function executarSQL($conn, $sql, $descricao) {
    echo "Executando: $descricao\n";
    
    if (mysqli_query($conn, $sql)) {
        echo "✅ Sucesso: $descricao\n\n";
        return true;
    } else {
        $erro = mysqli_error($conn);
        if (strpos($erro, 'Duplicate column name') !== false) {
            echo "ℹ️  Já existe: $descricao\n\n";
            return true;
        } else {
            echo "❌ Erro: $erro\n\n";
            return false;
        }
    }
}

// Lista de alterações essenciais
$alteracoes = [
    [
        'sql' => "ALTER TABLE imoveis ADD caracteristicas TEXT NULL",
        'desc' => "Adicionar coluna 'caracteristicas' para armazenar características em JSON"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD posicao_solar VARCHAR(10) NULL",
        'desc' => "Adicionar coluna 'posicao_solar' para orientação solar"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD cep CHAR(8) NULL",
        'desc' => "Adicionar coluna 'cep' para código postal"
    ]
];

// Lista de alterações opcionais (campos administrativos)
$alteracoes_opcionais = [
    [
        'sql' => "ALTER TABLE imoveis ADD valor_condominio DECIMAL(10,2) NULL",
        'desc' => "Valor mensal do condomínio"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD valor_iptu DECIMAL(10,2) NULL",
        'desc' => "Valor mensal do IPTU"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD matricula VARCHAR(100) NULL",
        'desc' => "Número da matrícula do imóvel"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD exclusividade TINYINT(1) DEFAULT 0",
        'desc' => "Imóvel em exclusividade"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD chaves_tipo VARCHAR(255) NULL",
        'desc' => "Tipos de chaves disponíveis"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD chaves_copias INT DEFAULT 0",
        'desc' => "Quantidade de cópias das chaves"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD observacoes_chaves TEXT NULL",
        'desc' => "Observações sobre as chaves"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD area_privativa DECIMAL(8,2) NULL",
        'desc' => "Área privativa em m²"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD area_comum DECIMAL(8,2) NULL",
        'desc' => "Área comum em m²"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD parcelas_iptu VARCHAR(50) NULL",
        'desc' => "Número de parcelas do IPTU"
    ],
    [
        'sql' => "ALTER TABLE imoveis ADD taxa_intermediacao DECIMAL(5,2) NULL",
        'desc' => "Taxa de intermediação em %"
    ]
];

echo "🎯 EXECUTANDO ALTERAÇÕES ESSENCIAIS:\n";
echo "------------------------------------\n";

$sucesso_essencial = true;
foreach ($alteracoes as $alteracao) {
    if (!executarSQL($conn, $alteracao['sql'], $alteracao['desc'])) {
        $sucesso_essencial = false;
    }
}

if ($sucesso_essencial) {
    echo "✅ TODAS AS ALTERAÇÕES ESSENCIAIS FORAM EXECUTADAS COM SUCESSO!\n\n";
} else {
    echo "❌ Algumas alterações essenciais falharam. Verifique os erros acima.\n\n";
}

// Perguntar sobre alterações opcionais (simular input)
echo "⚙️  ALTERAÇÕES OPCIONAIS DISPONÍVEIS:\n";
echo "-------------------------------------\n";
echo "As alterações opcionais adicionam campos administrativos internos.\n";
echo "Execute este script novamente com parâmetro 'completo' para incluí-las:\n";
echo "php atualizar-banco-automatico.php completo\n\n";

// Se passou parâmetro 'completo', executar opcionais também
if (isset($argv[1]) && $argv[1] === 'completo') {
    echo "🔧 EXECUTANDO ALTERAÇÕES OPCIONAIS:\n";
    echo "-----------------------------------\n";
    
    foreach ($alteracoes_opcionais as $alteracao) {
        executarSQL($conn, $alteracao['sql'], $alteracao['desc']);
    }
}

// Mostrar estrutura final
echo "📊 ESTRUTURA FINAL DA TABELA:\n";
echo "-----------------------------\n";

$result = mysqli_query($conn, "DESCRIBE imoveis");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tipo = $row['Type'];
        $null = $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        $extra = $row['Extra'] ? " ({$row['Extra']})" : '';
        echo "✓ {$row['Field']} - $tipo $null$extra\n";
    }
} else {
    echo "❌ Erro ao mostrar estrutura da tabela\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "-------------------\n";
echo "1. ✅ Banco de dados atualizado\n";
echo "2. 🔧 Teste o formulário de cadastro: private/imoveis/adicionar.php\n";
echo "3. 🔧 Teste o formulário de edição: private/imoveis/editar.php\n";
echo "4. 📋 Execute o teste: teste-novas-caracteristicas.php\n";
echo "5. 🌐 Verifique a exibição pública em: imovel-detalhes.php\n";

mysqli_close($conn);
?>