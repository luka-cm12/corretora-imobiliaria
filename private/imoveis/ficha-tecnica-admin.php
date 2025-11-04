<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../includes/db.php');

// Verificar se o ID do imóvel foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php');
    exit;
}

$imovel_id = intval($_GET['id']);

// Buscar os dados do imóvel com proprietário
$imovel_result = db_query(
    "SELECT i.*, p.nome as proprietario_nome, p.email as proprietario_email, p.telefone as proprietario_telefone 
     FROM imoveis i 
     LEFT JOIN proprietarios p ON i.id_proprietario = p.id_proprietario 
     WHERE i.id = ?", 
    [$imovel_id]
);

// Verifica se encontrou algum resultado
if (!is_array($imovel_result) || count($imovel_result) === 0) {
    header('Location: listar.php');
    exit;
}

$imovel = $imovel_result[0];

// Formatar preço
$preco_formatado = 'R$ ' . number_format($imovel['preco'], 2, ',', '.');

// Tipos de imóvel para exibição amigável
$tipos = [
    'casa' => 'Casa',
    'casa_condominio' => 'Casa em Condomínio',
    'apartamento' => 'Apartamento',
    'apartamento_mobiliado' => 'Apartamento Mobiliado',
    'sobrado' => 'Sobrado',
    'chacara' => 'Chácara',
    'semi_mobiliado' => 'Semi Mobiliado',
    'terreno' => 'Terreno',
    'loft' => 'Loft',
    'comercial' => 'Comercial',
    'pavilhao' => 'Pavilhão',
    'fazenda' => 'Fazenda',
    'laja_terrea' => 'Laja Térrea',
    'sala_area' => 'Sala Área',
    'area_terras' => 'Área de Terras',
    'loteamento' => 'Loteamento',
    'condominio_fechado' => 'Condomínio Fechado'
];

// Características principais
$caracteristicas_lista = [
    'suite' => ['Suíte', '🛏️'],
    'closet' => ['Closet', '👔'],
    'ar_condicionado' => ['Ar condicionado', '❄️'],
    'armarios_embutidos' => ['Armários embutidos', '🗄️'],
    'suite_master' => ['Suíte master', '👑'],
    'varanda_suite' => ['Varanda na suíte', '🚪'],
    'hidromassagem' => ['Hidromassagem', '🛁'],
    'agua_aquecida' => ['Água aquecida', '🌡️'],
    'gas_central' => ['Gás central', '🔥'],
    'banheira' => ['Banheira', '🛁'],
    'box_blindex' => ['Box blindex', '🚿'],
    'sauna' => ['Sauna', '🧖‍♀️'],
    'sala_de_estar' => ['Sala de estar', '🛋️'],
    'varanda' => ['Varanda', '🏢'],
    'sacada' => ['Sacada', '🏗️'],
    'sacada_gourmet' => ['Sacada gourmet', '🍽️'],
    'area_gourmet' => ['Área gourmet', '🍽️'],
    'churrasqueira' => ['Churrasqueira', '🔥'],
    'salao_de_festas' => ['Salão de festas', '🎉'],
    'quiosque' => ['Quiosque', '🏖️'],
    'jardim' => ['Jardim', '🌱'],
    'terraço' => ['Terraço', '🏛️'],
    'piscina' => ['Piscina', '🏊‍♂️'],
    'area_de_lazer' => ['Área de lazer', '🎮'],
    'academia' => ['Academia', '🏋️‍♂️'],
    'quadra' => ['Quadra', '🏀'],
    'playground' => ['Playground', '👶'],
    'campo_futebol' => ['Campo de futebol', '⚽'],
    'cozinha_americana' => ['Cozinha americana', '🍽️'],
    'cozinha_planejada' => ['Cozinha planejada', '🍳'],
    'copa' => ['Copa', '☕'],
    'despensa' => ['Despensa', '📦'],
    'area_servico' => ['Área de serviço', '🧼'],
    'lavanderia' => ['Lavanderia', '👕'],
    'portaria' => ['Portaria 24h', '🛡️'],
    'seguranca' => ['Segurança', '👮‍♂️'],
    'alarme' => ['Alarme', '🔔'],
    'cerca_eletrica' => ['Cerca elétrica', '⚡'],
    'cameras' => ['Câmeras', '📹'],
    'interfone' => ['Interfone', '📞'],
    'elevador' => ['Elevador', '🛗'],
    'mobiliado' => ['Mobiliado', '🛋️'],
    'vista_mar' => ['Vista para o mar', '🌊'],
    'frente_mar' => ['Frente ao mar', '🏖️'],
    'quintal' => ['Quintal', '🌳'],
    'escritorio' => ['Escritório', '💼'],
    'biblioteca' => ['Biblioteca', '📚'],
    'deposito' => ['Depósito', '🏬']
];

// Função para obter características ativas
function getCaracteristicasAtivas($imovel, $caracteristicas_lista) {
    $ativas = [];
    foreach ($caracteristicas_lista as $campo => $info) {
        if (isset($imovel[$campo]) && $imovel[$campo] == 1) {
            $ativas[] = [
                'nome' => $info[0],
                'emoji' => $info[1] ?? '✓'
            ];
        }
    }
    return $ativas;
}

$caracteristicas_ativas = getCaracteristicasAtivas($imovel, $caracteristicas_lista);

// Decodificar características salvas em JSON se existir
$caracteristicas_json = [];
if (isset($imovel['caracteristicas']) && !empty($imovel['caracteristicas'])) {
    $decoded = json_decode($imovel['caracteristicas'], true);
    if (is_array($decoded)) {
        $caracteristicas_json = array_map(function($item) {
            return [
                'nome' => $item,
                'emoji' => '✓'
            ];
        }, $decoded);
    }
}

// Combinar características
$todas_caracteristicas = array_merge($caracteristicas_ativas, $caracteristicas_json);
$todas_caracteristicas = array_unique($todas_caracteristicas, SORT_REGULAR);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Captação de Imóvel - <?= htmlspecialchars($imovel['titulo']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            body { margin: 0; font-size: 11pt; line-height: 1.2; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .container { max-width: none; margin: 0; padding: 15mm; }
            .print-actions { display: none !important; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.3;
            color: #000;
            background: #fff;
            font-size: 12px;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn-print { background: #28a745; }
        .btn-back { background: #6c757d; }
        .btn-edit { background: #ffc107; color: #212529; }
        
        .header {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        
        .company-logo {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        
        .header-right {
            text-align: right;
            font-size: 10px;
        }
        
        .form-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px 0;
        }
        
        .form-subtitle {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
        }
        
        .reference-line {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            border: 1px solid #000;
            padding: 5px 10px;
            margin-bottom: 10px;
        }
        
        .form-section {
            border: 1px solid #000;
            margin-bottom: 15px;
            background: #fff;
        }
        
        .section-header {
            background: #f0f0f0;
            border-bottom: 1px solid #000;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .section-content {
            padding: 15px;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }
        
        .form-row.full-width {
            display: block;
        }
        
        .form-field {
            display: flex;
            align-items: center;
            margin-right: 30px;
            flex: 1;
        }
        
        .form-field.half {
            flex: 0 0 45%;
        }
        
        .form-field.quarter {
            flex: 0 0 20%;
        }
        
        .field-label {
            font-weight: bold;
            margin-right: 8px;
            min-width: 80px;
            font-size: 11px;
        }
        
        .field-value {
            border-bottom: 1px solid #000;
            min-height: 20px;
            flex: 1;
            padding: 2px 5px;
            font-size: 11px;
        }
        
        .checkbox-field {
            display: flex;
            align-items: center;
            margin-right: 20px;
            margin-bottom: 8px;
        }
        
        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 5px;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
        }
        
        .checkbox.checked::after {
            content: "✓";
        }
        
        .table-form {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .table-form td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
        }
        
        .table-form .label-cell {
            background: #f0f0f0;
            font-weight: bold;
            width: 25%;
        }
        
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            border: 1px solid #000;
            height: 60px;
            margin-bottom: 5px;
        }
        
        .signature-label {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .print-actions { 
                position: relative; 
                top: 0; 
                right: 0; 
                margin-bottom: 20px;
                justify-content: center;
            }
            .form-row {
                flex-direction: column;
            }
            .form-field {
                margin-right: 0;
                margin-bottom: 10px;
            }
            .form-field.half, .form-field.quarter {
                flex: 1;
            }
            .checkbox-field {
                margin-bottom: 5px;
            }
            .header-top {
                flex-direction: column;
                text-align: center;
            }
            .reference-line {
                flex-direction: column;
                gap: 5px;
            }
        }
        
        @media print {
            .form-section {
                break-inside: avoid;
            }
            .signature-section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <a href="listar.php" class="action-btn btn-back">
            ← Voltar à Lista
        </a>
        <a href="editar.php?id=<?= $imovel['id'] ?>" class="action-btn btn-edit">
            ✏️ Editar
        </a>
        <button onclick="window.print()" class="action-btn btn-print">
            🖨️ Imprimir
        </button>
    </div>
    
    <div class="container">
        <!-- Cabeçalho da Ficha -->
        <div class="header">
            <div class="header-top">
                <div class="company-logo">CORRETORA BASE</div>
                <div class="header-right">
                    <div>CRECI: ________</div>
                    <div>Data: <?= date('d/m/Y') ?></div>
                </div>
            </div>
            
            <div class="form-title">FICHA DE CAPTAÇÃO DE IMÓVEL</div>
            <div class="form-subtitle">Documento para Avaliação e Cadastramento</div>
            
            <div class="reference-line">
                <span><strong>Código Ref.:</strong> <?= str_pad($imovel['id'], 6, '0', STR_PAD_LEFT) ?></span>
                <span><strong>Corretor:</strong> <?= htmlspecialchars($_SESSION['admin_nome'] ?? '____________________') ?></span>
                <span><strong>Fone:</strong> ____________________</span>
            </div>
        </div>
        
        <!-- Dados do Proprietário -->
        <?php if (!empty($imovel['proprietario_nome'])): ?>
        <div class="form-section">
            <div class="section-header">DADOS DO PROPRIETÁRIO</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Nome:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['proprietario_nome']) ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">CPF/CNPJ:</span>
                        <span class="field-value">_______________________</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">RG:</span>
                        <span class="field-value">_______________________</span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Estado Civil:</span>
                        <span class="field-value">_______________________</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Endereço Completo:</span>
                        <span class="field-value">_________________________________________________</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Telefone:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['proprietario_telefone'] ?? '____________________') ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Email:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['proprietario_email'] ?? '____________________') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dados do Imóvel -->
        <div class="form-section">
            <div class="section-header">IDENTIFICAÇÃO DO IMÓVEL</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Título:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['titulo']) ?></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Endereço Completo:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['endereco'] ?? ($imovel['bairro'] . ', ' . $imovel['cidade'])) ?></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Bairro:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['bairro']) ?></span>
                    </div>
                    <div class="form-field quarter">
                        <span class="field-label">CEP:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['cep'] ?? '__________') ?></span>
                    </div>
                    <div class="form-field quarter">
                        <span class="field-label">Cidade:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['cidade']) ?></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Tipo:</span>
                        <span class="field-value"><?= $tipos[$imovel['tipo']] ?? ucfirst($imovel['tipo']) ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Finalidade:</span>
                        <span class="field-value"><?= ucfirst($imovel['finalidade'] ?? 'Não informada') ?></span>
                    </div>
                </div>
                <?php if (!empty($imovel['matricula'])): ?>
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Matrícula:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['matricula']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Características Técnicas -->
        <div class="form-section">
            <div class="section-header">CARACTERÍSTICAS TÉCNICAS</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-field quarter">
                        <span class="field-label">Quartos:</span>
                        <span class="field-value"><?= $imovel['quartos'] > 0 ? $imovel['quartos'] : '____' ?></span>
                    </div>
                    <div class="form-field quarter">
                        <span class="field-label">Banheiros:</span>
                        <span class="field-value"><?= $imovel['banheiros'] > 0 ? $imovel['banheiros'] : '____' ?></span>
                    </div>
                    <div class="form-field quarter">
                        <span class="field-label">Garagem:</span>
                        <span class="field-value"><?= $imovel['garagem'] > 0 ? $imovel['garagem'] : '____' ?></span>
                    </div>
                    <div class="form-field quarter">
                        <span class="field-label">Suítes:</span>
                        <span class="field-value">____</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Área Construída:</span>
                        <span class="field-value"><?= $imovel['area'] > 0 ? number_format($imovel['area'], 0) . ' m²' : '____ m²' ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Área do Terreno:</span>
                        <span class="field-value"><?= !empty($imovel['area_terreno']) && $imovel['area_terreno'] > 0 ? number_format($imovel['area_terreno'], 0) . ' m²' : '____ m²' ?></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Área Privativa:</span>
                        <span class="field-value"><?= !empty($imovel['area_privativa']) && $imovel['area_privativa'] > 0 ? number_format($imovel['area_privativa'], 0) . ' m²' : '____ m²' ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Área Comum:</span>
                        <span class="field-value"><?= !empty($imovel['area_comum']) && $imovel['area_comum'] > 0 ? number_format($imovel['area_comum'], 0) . ' m²' : '____ m²' ?></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Posição Solar:</span>
                        <span class="field-value"><?= ucfirst($imovel['posicao_solar'] ?? '________________') ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Idade:</span>
                        <span class="field-value">_______ anos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valores e Documentação -->
        <div class="form-section">
            <div class="section-header">VALORES E DOCUMENTAÇÃO</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Valor de <?= ucfirst($imovel['finalidade'] ?? 'Venda') ?>:</span>
                        <span class="field-value"><?= $preco_formatado ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Valor Avaliado:</span>
                        <span class="field-value">R$ ____________________</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Taxa Intermediação:</span>
                        <span class="field-value"><?= !empty($imovel['taxa_intermediacao']) && $imovel['taxa_intermediacao'] > 0 ? number_format($imovel['taxa_intermediacao'], 2) . '%' : '_____%' ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Parcelas IPTU:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['parcelas_iptu'] ?? '__________') ?></span>
                    </div>
                </div>
                
                <?php if (isset($imovel['valor_condominio']) && $imovel['valor_condominio'] > 0): ?>
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Condomínio:</span>
                        <span class="field-value">R$ <?= number_format($imovel['valor_condominio'], 2, ',', '.') ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">IPTU:</span>
                        <span class="field-value"><?= isset($imovel['valor_iptu']) && $imovel['valor_iptu'] > 0 ? 'R$ ' . number_format($imovel['valor_iptu'], 2, ',', '.') : 'R$ ___________' ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Documentação:</span>
                        <div style="display: flex; gap: 20px; margin-left: 10px;">
                            <span class="checkbox-field">
                                <span class="checkbox <?= !empty($imovel['matricula']) ? 'checked' : '' ?>"></span>
                                <span>Escritura</span>
                            </span>
                            <span class="checkbox-field">
                                <span class="checkbox"></span>
                                <span>IPTU</span>
                            </span>
                            <span class="checkbox-field">
                                <span class="checkbox"></span>
                                <span>Matrícula</span>
                            </span>
                            <span class="checkbox-field">
                                <span class="checkbox"></span>
                                <span>RGI</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Características e Comodidades -->
        <div class="form-section">
            <div class="section-header">CARACTERÍSTICAS E COMODIDADES</div>
            <div class="section-content">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">
                    
                    <!-- Primeira coluna -->
                    <div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['suite']) && $imovel['suite'] ? 'checked' : '' ?>"></span>
                            <span>Suíte</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['ar_condicionado']) && $imovel['ar_condicionado'] ? 'checked' : '' ?>"></span>
                            <span>Ar condicionado</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['armarios_embutidos']) && $imovel['armarios_embutidos'] ? 'checked' : '' ?>"></span>
                            <span>Armários embutidos</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['closet']) && $imovel['closet'] ? 'checked' : '' ?>"></span>
                            <span>Closet</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['hidromassagem']) && $imovel['hidromassagem'] ? 'checked' : '' ?>"></span>
                            <span>Hidromassagem</span>
                        </div>
                    </div>
                    
                    <!-- Segunda coluna -->
                    <div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['piscina']) && $imovel['piscina'] ? 'checked' : '' ?>"></span>
                            <span>Piscina</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['churrasqueira']) && $imovel['churrasqueira'] ? 'checked' : '' ?>"></span>
                            <span>Churrasqueira</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['area_gourmet']) && $imovel['area_gourmet'] ? 'checked' : '' ?>"></span>
                            <span>Área gourmet</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['jardim']) && $imovel['jardim'] ? 'checked' : '' ?>"></span>
                            <span>Jardim</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['quintal']) && $imovel['quintal'] ? 'checked' : '' ?>"></span>
                            <span>Quintal</span>
                        </div>
                    </div>
                    
                    <!-- Terceira coluna -->
                    <div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['varanda']) && $imovel['varanda'] ? 'checked' : '' ?>"></span>
                            <span>Varanda</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['sacada']) && $imovel['sacada'] ? 'checked' : '' ?>"></span>
                            <span>Sacada</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['elevador']) && $imovel['elevador'] ? 'checked' : '' ?>"></span>
                            <span>Elevador</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['portaria']) && $imovel['portaria'] ? 'checked' : '' ?>"></span>
                            <span>Portaria 24h</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['academia']) && $imovel['academia'] ? 'checked' : '' ?>"></span>
                            <span>Academia</span>
                        </div>
                    </div>
                    
                    <!-- Quarta coluna -->
                    <div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['playground']) && $imovel['playground'] ? 'checked' : '' ?>"></span>
                            <span>Playground</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['salao_de_festas']) && $imovel['salao_de_festas'] ? 'checked' : '' ?>"></span>
                            <span>Salão de festas</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['quadra']) && $imovel['quadra'] ? 'checked' : '' ?>"></span>
                            <span>Quadra esportiva</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['interfone']) && $imovel['interfone'] ? 'checked' : '' ?>"></span>
                            <span>Interfone</span>
                        </div>
                        <div class="checkbox-field">
                            <span class="checkbox <?= isset($imovel['alarme']) && $imovel['alarme'] ? 'checked' : '' ?>"></span>
                            <span>Alarme</span>
                        </div>
                    </div>
                </div>
                
                <!-- Características extras do JSON -->
                <?php if (!empty($caracteristicas_json)): ?>
                <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #000;">
                    <strong>Outras características:</strong><br>
                    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 5px;">
                        <?php foreach ($caracteristicas_json as $caracteristica): ?>
                            <span class="checkbox-field">
                                <span class="checkbox checked"></span>
                                <span><?= htmlspecialchars($caracteristica['nome']) ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Descrição/Observações -->
        <div class="form-section">
            <div class="section-header">OBSERVAÇÕES E DESCRIÇÃO</div>
            <div class="section-content">
                <div class="form-row full-width">
                    <div style="border: 1px solid #000; min-height: 80px; padding: 10px; width: 100%;">
                        <?= !empty($imovel['descricao']) ? nl2br(htmlspecialchars($imovel['descricao'])) : '' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="form-section">
            <div class="section-header">INFORMAÇÕES COMPLEMENTARES</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Escritura:</span>
                        <span class="field-value"><?= !empty($imovel['escritura']) ? 'Sim' : '[ ] Sim  [ ] Não' ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Situação Legal:</span>
                        <span class="field-value">[ ] Regular  [ ] Pendente  [ ] Irregular</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Aceita Financiamento:</span>
                        <span class="field-value">[ ] Sim  [ ] Não  [ ] A negociar</span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Aceita FGTS:</span>
                        <span class="field-value">[ ] Sim  [ ] Não  [ ] A negociar</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Ocupado:</span>
                        <span class="field-value">[ ] Sim  [ ] Não  [ ] Parcialmente</span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Disponível para Visitas:</span>
                        <span class="field-value">[ ] Imediato  [ ] Agendado  [ ] Restrito</span>
                    </div>
                </div>
                
                <?php if (!empty($imovel['descricao_localizacao'])): ?>
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Localização:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['descricao_localizacao']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Controle Interno -->
        <div class="form-section">
            <div class="section-header">CONTROLE INTERNO</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Data de Cadastro:</span>
                        <span class="field-value"><?= date('d/m/Y', strtotime($imovel['created_at'])) ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Chaves na Imobiliária:</span>
                        <span class="field-value"><?= isset($imovel['chaves_copias']) && $imovel['chaves_copias'] > 0 ? $imovel['chaves_copias'] . ' cópias' : 'Não' ?></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Tipo de Chaves:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['chaves_tipo'] ?? 'Não informado') ?></span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Taxa Intermediação:</span>
                        <span class="field-value"><?= !empty($imovel['taxa_intermediacao']) && $imovel['taxa_intermediacao'] > 0 ? number_format($imovel['taxa_intermediacao'], 2) . '%' : '_____%' ?></span>
                    </div>
                </div>
                
                <?php if (!empty($imovel['observacoes_chaves'])): ?>
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Obs. Chaves:</span>
                        <span class="field-value"><?= htmlspecialchars($imovel['observacoes_chaves']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-field half">
                        <span class="field-label">Status:</span>
                        <span class="field-value">
                            <?php if ($imovel['destaque'] ?? false): ?>
                                Destaque
                            <?php else: ?>
                                Ativo
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="form-field half">
                        <span class="field-label">Exclusividade:</span>
                        <span class="field-value"><?= isset($imovel['exclusividade']) && $imovel['exclusividade'] ? 'Sim' : 'Não' ?></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <span class="field-label">Observações Internas:</span>
                        <span class="field-value" style="min-height: 25px;">_________________________________________________</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Assinaturas e Aprovações -->
        <div class="form-section" style="margin-top: 20px;">
            <div class="section-header">ASSINATURAS E APROVAÇÕES</div>
            <div class="section-content">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 20px;">
                    <div style="text-align: center;">
                        <div style="border-bottom: 1px solid #000; height: 50px; margin-bottom: 5px;"></div>
                        <div style="font-size: 10px; font-weight: bold;">PROPRIETÁRIO</div>
                        <div style="font-size: 9px;">Data: ____/____/______</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="border-bottom: 1px solid #000; height: 50px; margin-bottom: 5px;"></div>
                        <div style="font-size: 10px; font-weight: bold;">CORRETOR RESPONSÁVEL</div>
                        <div style="font-size: 9px;">CRECI: ______________</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="border-bottom: 1px solid #000; height: 50px; margin-bottom: 5px;"></div>
                        <div style="font-size: 10px; font-weight: bold;">DIRETOR COMERCIAL</div>
                        <div style="font-size: 9px;">Data: ____/____/______</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rodapé Final -->
        <div style="margin-top: 30px; padding: 15px; border: 2px solid #000; text-align: center; background: #f8f8f8;">
            <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;">CORRETORA BASE</div>
            <div style="font-size: 10px;">
                Endereço: _________________________________ | Fone: _________________ | Email: _________________<br>
                CRECI: ______________ | Site: www.corretorabase.com.br
            </div>
            <div style="font-size: 8px; margin-top: 10px; color: #666;">
                Documento gerado automaticamente em <?= date('d/m/Y \à\s H:i') ?> | Código Interno: <?= $imovel['id'] ?>
            </div>
        </div>
    </div>
    
    <script>
        // Função para imprimir automaticamente se solicitado
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            window.addEventListener('load', function() {
                setTimeout(() => window.print(), 500);
            });
        }
        
        // Melhorar experiência de impressão
        window.addEventListener('beforeprint', function() {
            document.body.style.fontSize = '11pt';
        });
        
        window.addEventListener('afterprint', function() {
            document.body.style.fontSize = '';
        });
    </script>
</body>
</html>