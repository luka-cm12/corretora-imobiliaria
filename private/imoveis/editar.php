<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');
require_once(__DIR__ . '/../config/config.php');
require_login();

// Garante token CSRF
if (function_exists('ensureCsrfToken')) {
    ensureCsrfToken();
} else {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Verificar se o ID do imóvel foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php');
    exit;
}

$imovel_id = intval($_GET['id']);

// Buscar os dados do imóvel
$imovel_result = db_query(
    "SELECT * FROM imoveis WHERE id = ?",
    [$imovel_id]
);

if (!is_array($imovel_result) || count($imovel_result) === 0) {
    header('Location: listar.php');
    exit;
}

$imovel = $imovel_result[0];
$imovel['imagens'] = array_values(array_filter(explode(',', $imovel['imagens'])));

// Garante colunas necessárias (executa ALTER TABLE se ausentes)
if (function_exists('ensure_table_column')) {
    // Características: usa JSON quando disponível; aqui adotamos TEXT por compatibilidade ampla.
    ensure_table_column('imoveis', 'caracteristicas', 'TEXT NULL');
    // CEP: apenas dígitos; usamos CHAR(8) NULL
    ensure_table_column('imoveis', 'cep', 'CHAR(8) NULL');
}

// Lista padrão de características principais (mesma do adicionar.php)
$lista_caracteristicas = [
    'ar_condicionado' => 'Ar condicionado',
    'armarios_embutidos' => 'Armários embutidos',
    'churrasqueira' => 'Churrasqueira',
    'varanda' => 'Varanda',
    'sacada' => 'Sacada',
    'piscina' => 'Piscina',
    'academia' => 'Academia',
    'area_gourmet' => 'Área gourmet',
    'portaria_24h' => 'Portaria 24h',
    'elevador' => 'Elevador',
    'mobiliado' => 'Mobiliado',
    'pet_friendly' => 'Pet friendly',
    'quintal' => 'Quintal',
    'lavanderia' => 'Lavanderia',
    'lareira' => 'Lareira'
];

// Lista de opções de posição solar
$opcoes_posicao_solar = [
    'norte' => ['nome' => 'Norte', 'descricao' => 'Maior incidência de sol', 'icon' => '☀️'],
    'sul' => ['nome' => 'Sul', 'descricao' => 'Menor incidência solar', 'icon' => '🌤️'],
    'leste' => ['nome' => 'Leste', 'descricao' => 'Sol da manhã', 'icon' => '🌅'],
    'oeste' => ['nome' => 'Oeste', 'descricao' => 'Sol da tarde', 'icon' => '🌇']
];

// Carrega características atuais do imóvel (se existir a coluna caracteristicas)
$caracCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'caracteristicas'");
$caracteristicas_atual = [];
if (is_array($caracCol) && count($caracCol) > 0) {
    if (!empty($imovel['caracteristicas'])) {
        $decoded = json_decode($imovel['caracteristicas'], true);
        if (is_array($decoded)) $caracteristicas_atual = $decoded;
    }
}

// Processar formulário de edição
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verifica CSRF se disponível
        if (function_exists('verificaCsrfToken')) {
            verificaCsrfToken();
        }
        // Validar dados
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
    $preco = (float) str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0');
        $area = (float) str_replace(',', '.', $_POST['area'] ?? '0');
        
        // Valores internos (condomínio e IPTU)
        $valor_condominio = 0;
        if (!empty($_POST['valor_condominio'])) {
            $valor_condominio = (float) str_replace(['R$', '.', ',', ' '], ['', '', '.', ''], $_POST['valor_condominio']);
        }
        
        $valor_iptu = 0;
        if (!empty($_POST['valor_iptu'])) {
            $valor_iptu = (float) str_replace(['R$', '.', ',', ' '], ['', '', '.', ''], $_POST['valor_iptu']);
        }
        
        // Novos campos internos
        $matricula = trim($_POST['matricula'] ?? '');
        $parcelas_iptu = trim($_POST['parcelas_iptu'] ?? '');
        $exclusividade = isset($_POST['exclusividade']) ? 1 : 0;
        $taxa_intermediacao = 0;
        if (!empty($_POST['taxa_intermediacao'])) {
            $taxa_intermediacao = (float) str_replace([','], ['.'], $_POST['taxa_intermediacao']);
        }

        // Novos campos de área
        $area_privativa = (float) str_replace(',', '.', $_POST['area_privativa'] ?? '0');
        $area_comum = (float) str_replace(',', '.', $_POST['area_comum'] ?? '0');
        
        // Novos campos de chaves
        $chaves_tipo = isset($_POST['chaves_tipo']) && is_array($_POST['chaves_tipo']) 
            ? implode(',', array_map('trim', $_POST['chaves_tipo'])) 
            : '';
        $chaves_copias = (int) ($_POST['chaves_copias'] ?? 0);
        $observacoes_chaves = trim($_POST['observacoes_chaves'] ?? '');
        
        // Posição solar
        $posicao_solar = trim($_POST['posicao_solar'] ?? '');
        
        $quartos = (int) ($_POST['quartos'] ?? 0);
        $banheiros = (int) ($_POST['banheiros'] ?? 0);
        $garagem = (int) ($_POST['garagem'] ?? 0);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
    // CEP (opcional)
    $cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');
        // Características principais (opcional)
        $caracteristicas_post = isset($_POST['caracteristicas']) && is_array($_POST['caracteristicas'])
            ? array_values(array_intersect(array_keys($lista_caracteristicas), $_POST['caracteristicas']))
            : [];
        $caracteristicas_json = json_encode($caracteristicas_post, JSON_UNESCAPED_UNICODE);
        
        // Validações básicas - apenas campos essenciais obrigatórios
        if (empty($titulo)) {
            throw new Exception('O título é obrigatório.');
        }
        if (empty($descricao)) {
            throw new Exception('A descrição é obrigatória.');
        }
        if (empty($tipo)) {
            throw new Exception('O tipo do imóvel é obrigatório.');
        }
        if (empty($cidade)) {
            throw new Exception('A cidade é obrigatória.');
        }
        if (empty($bairro)) {
            throw new Exception('O bairro é obrigatório.');
        }
        if ($preco <= 0) {
            throw new Exception('O preço deve ser maior que zero.');
        }
        
    // Diretório de uploads (upload_imagem usa caminho relativo à raiz do projeto)
    // Em functions.php, upload_imagem monta __DIR__ . '/../../' . $pasta
    // Portanto devemos usar 'public/uploads' aqui
    $uploadDir = 'public/uploads';

    // IMPORTANTE: Para permitir upload de 25 imagens, verifique as configurações do PHP:
    // max_file_uploads = 25 (ou mais)
    // post_max_size = 100M (ou maior, dependendo do tamanho das imagens)
    // upload_max_filesize = 10M (ou maior, para cada imagem individual)

    $novas_imagens = [];
        $substituicoes_novas = [];
        $imagens_removidas = [];
        $todas_imagens = [];
    $upload_erros = [];

        // Lista marcada para remoção
        $imagens_para_remover = isset($_POST['imagens_remover']) && is_array($_POST['imagens_remover'])
            ? $_POST['imagens_remover'] : [];

        // Processa imagens existentes: remover, substituir ou manter
        foreach ($imovel['imagens'] as $idx => $imgAtual) {
            // Substituir? (prioridade sobre remover se ambos marcados)
            if (isset($_FILES['substituicoes']['name'][$idx]) && $_FILES['substituicoes']['name'][$idx] !== '') {
                $filePart = [
                    'name' => $_FILES['substituicoes']['name'][$idx],
                    'type' => $_FILES['substituicoes']['type'][$idx],
                    'tmp_name' => $_FILES['substituicoes']['tmp_name'][$idx],
                    'error' => $_FILES['substituicoes']['error'][$idx],
                    'size' => $_FILES['substituicoes']['size'][$idx],
                ];
                if ($filePart['error'] === UPLOAD_ERR_OK) {
                    $novoNome = upload_imagem($filePart, $uploadDir, 1200, 800);
                    if ($novoNome) {
                        $todas_imagens[] = $novoNome;
                        $substituicoes_novas[] = $novoNome;
                        $imagens_removidas[] = $imgAtual; // remover a antiga
                        continue;
                    } else {
                        $upload_erros[] = "Falha ao processar substituição da imagem #" . ($idx + 1) . ".";
                    }
                } else if ($filePart['error'] !== UPLOAD_ERR_NO_FILE) {
                    $upload_erros[] = "Falha ao substituir a imagem #" . ($idx + 1) . ": " . upload_error_text($filePart['error']);
                }
            }

            // Remover?
            if (in_array($imgAtual, $imagens_para_remover, true)) {
                $imagens_removidas[] = $imgAtual;
                continue;
            }

            // Manter imagem original
            $todas_imagens[] = $imgAtual;
        }

        // Processar novas imagens enviadas
        if (!empty($_FILES['novas_imagens']['name'][0])) {
            foreach ($_FILES['novas_imagens']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['novas_imagens']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = upload_imagem([
                        'name' => $_FILES['novas_imagens']['name'][$key],
                        'type' => $_FILES['novas_imagens']['type'][$key],
                        'tmp_name' => $_FILES['novas_imagens']['tmp_name'][$key],
                        'error' => $_FILES['novas_imagens']['error'][$key],
                        'size' => $_FILES['novas_imagens']['size'][$key]
                    ], $uploadDir, 1200, 800);
                    if ($fileName) {
                        $novas_imagens[] = $fileName;
                        $todas_imagens[] = $fileName;
                    } else {
                        $upload_erros[] = "Falha ao processar nova imagem '" . htmlspecialchars($_FILES['novas_imagens']['name'][$key]) . "'.";
                    }
                } else if ($_FILES['novas_imagens']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $upload_erros[] = "Falha ao enviar nova imagem '" . htmlspecialchars($_FILES['novas_imagens']['name'][$key]) . "': " . upload_error_text($_FILES['novas_imagens']['error'][$key]);
                }
            }
        }
        
        if (empty($todas_imagens)) {
            throw new Exception('Pelo menos uma imagem é obrigatória');
        }
        
    $imagens_str = implode(',', $todas_imagens);
        
        // Atualizar no banco de dados
        // Atualizar no banco de dados (condicionalmente inclui CEP e campos internos se existirem as colunas)
        $cepCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'cep'");
        $condominioCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'valor_condominio'");
        $iptuCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'valor_iptu'");
        $parcelasIptuCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'parcelas_iptu'");
        $matriculaCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'matricula'");
        $exclusividadeCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'exclusividade'");
        $taxaCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'taxa_intermediacao'");
        $areaPrivativaCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'area_privativa'");
        $areaComumCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'area_comum'");
        $chavesTipoCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'chaves_tipo'");
        $chavesCopiasCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'chaves_copias'");
        $observacoesChavesCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'observacoes_chaves'");
        $posicaoSolarCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'posicao_solar'");
        $sqlUpdate = "UPDATE imoveis SET 
                titulo = ?, 
                descricao = ?, 
                tipo = ?, 
                cidade = ?, 
                bairro = ?, 
                endereco = ?, 
                %CEP%
                preco = ?, 
                %CONDOMINIO%
                %IPTU%
                %PARCELAS_IPTU%
                %MATRICULA%
                %EXCLUSIVIDADE%
                %TAXA%
                %CHAVES_TIPO%
                %CHAVES_COPIAS%
                %OBSERVACOES_CHAVES%
                area = ?, 
                %AREA_PRIVATIVA%
                %AREA_COMUM%
                quartos = ?, 
                banheiros = ?, 
                garagem = ?, 
                imagens = ?, 
                destaque = ?
                %POSICAO_SOLAR%
                %CARAC% 
             WHERE id = ?";

        $params = [$titulo, $descricao, $tipo, $cidade, $bairro, $endereco];
        $tail = [$preco, $area, $quartos, $banheiros, $garagem, $imagens_str, $destaque];

        // CEP condicional
        if (is_array($cepCol) && count($cepCol) > 0) {
            $sqlUpdate = str_replace('%CEP%', 'cep = ?, ', $sqlUpdate);
            $params[] = $cep;
        } else {
            $sqlUpdate = str_replace('%CEP%', '', $sqlUpdate);
        }

        // Valor Condomínio condicional
        if (is_array($condominioCol) && count($condominioCol) > 0) {
            $sqlUpdate = str_replace('%CONDOMINIO%', 'valor_condominio = ?, ', $sqlUpdate);
            $params[] = $valor_condominio;
        } else {
            $sqlUpdate = str_replace('%CONDOMINIO%', '', $sqlUpdate);
        }

        // Valor IPTU condicional
        if (is_array($iptuCol) && count($iptuCol) > 0) {
            $sqlUpdate = str_replace('%IPTU%', 'valor_iptu = ?, ', $sqlUpdate);
            $params[] = $valor_iptu;
        } else {
            $sqlUpdate = str_replace('%IPTU%', '', $sqlUpdate);
        }

        // Parcelas IPTU condicional
        if (is_array($parcelasIptuCol) && count($parcelasIptuCol) > 0) {
            $sqlUpdate = str_replace('%PARCELAS_IPTU%', 'parcelas_iptu = ?, ', $sqlUpdate);
            $params[] = $parcelas_iptu;
        } else {
            $sqlUpdate = str_replace('%PARCELAS_IPTU%', '', $sqlUpdate);
        }

        // Matrícula condicional
        if (is_array($matriculaCol) && count($matriculaCol) > 0) {
            $sqlUpdate = str_replace('%MATRICULA%', 'matricula = ?, ', $sqlUpdate);
            $params[] = $matricula;
        } else {
            $sqlUpdate = str_replace('%MATRICULA%', '', $sqlUpdate);
        }

        // Exclusividade condicional
        if (is_array($exclusividadeCol) && count($exclusividadeCol) > 0) {
            $sqlUpdate = str_replace('%EXCLUSIVIDADE%', 'exclusividade = ?, ', $sqlUpdate);
            $params[] = $exclusividade;
        } else {
            $sqlUpdate = str_replace('%EXCLUSIVIDADE%', '', $sqlUpdate);
        }

        // Taxa intermediação condicional
        if (is_array($taxaCol) && count($taxaCol) > 0) {
            $sqlUpdate = str_replace('%TAXA%', 'taxa_intermediacao = ?, ', $sqlUpdate);
            $params[] = $taxa_intermediacao;
        } else {
            $sqlUpdate = str_replace('%TAXA%', '', $sqlUpdate);
        }

        // Chaves tipo condicional
        if (is_array($chavesTipoCol) && count($chavesTipoCol) > 0) {
            $sqlUpdate = str_replace('%CHAVES_TIPO%', 'chaves_tipo = ?, ', $sqlUpdate);
            $params[] = $chaves_tipo;
        } else {
            $sqlUpdate = str_replace('%CHAVES_TIPO%', '', $sqlUpdate);
        }

        // Chaves cópias condicional
        if (is_array($chavesCopiasCol) && count($chavesCopiasCol) > 0) {
            $sqlUpdate = str_replace('%CHAVES_COPIAS%', 'chaves_copias = ?, ', $sqlUpdate);
            $params[] = $chaves_copias;
        } else {
            $sqlUpdate = str_replace('%CHAVES_COPIAS%', '', $sqlUpdate);
        }

        // Observações chaves condicional
        if (is_array($observacoesChavesCol) && count($observacoesChavesCol) > 0) {
            $sqlUpdate = str_replace('%OBSERVACOES_CHAVES%', 'observacoes_chaves = ?, ', $sqlUpdate);
            $params[] = $observacoes_chaves;
        } else {
            $sqlUpdate = str_replace('%OBSERVACOES_CHAVES%', '', $sqlUpdate);
        }

        // Área privativa condicional
        if (is_array($areaPrivativaCol) && count($areaPrivativaCol) > 0) {
            $sqlUpdate = str_replace('%AREA_PRIVATIVA%', 'area_privativa = ?, ', $sqlUpdate);
            $params[] = $area_privativa;
        } else {
            $sqlUpdate = str_replace('%AREA_PRIVATIVA%', '', $sqlUpdate);
        }

        // Área comum condicional
        if (is_array($areaComumCol) && count($areaComumCol) > 0) {
            $sqlUpdate = str_replace('%AREA_COMUM%', 'area_comum = ?, ', $sqlUpdate);
            $params[] = $area_comum;
        } else {
            $sqlUpdate = str_replace('%AREA_COMUM%', '', $sqlUpdate);
        }

        // Posição solar condicional
        if (is_array($posicaoSolarCol) && count($posicaoSolarCol) > 0) {
            $sqlUpdate = str_replace('%POSICAO_SOLAR%', ', posicao_solar = ?', $sqlUpdate);
            $tail[] = $posicao_solar;
        } else {
            $sqlUpdate = str_replace('%POSICAO_SOLAR%', '', $sqlUpdate);
        }

        // Características condicional
        if (is_array($caracCol) && count($caracCol) > 0) {
            $sqlUpdate = str_replace('%CARAC%', ', caracteristicas = ?', $sqlUpdate);
            $tail[] = $caracteristicas_json;
        } else {
            $sqlUpdate = str_replace('%CARAC%', '', $sqlUpdate);
        }

        $params = array_merge($params, $tail, [$imovel_id]);
        $result = db_query($sqlUpdate, $params);
        
        if ($result) {
            $success = 'Imóvel atualizado com sucesso!';
            if (!empty($upload_erros)) {
                $error .= ($error ? ' ' : '') . implode(' ', $upload_erros);
            }
            
            // Excluir imagens removidas
            foreach ($imagens_removidas as $imagem_removida) {
                @unlink(__DIR__ . '/../../public/uploads/' . $imagem_removida);
            }
            
            // Atualizar dados do imóvel para exibição
            $imovel['titulo'] = $titulo;
            $imovel['descricao'] = $descricao;
            $imovel['tipo'] = $tipo;
            $imovel['cidade'] = $cidade;
            $imovel['bairro'] = $bairro;
            $imovel['endereco'] = $endereco;
            if (!empty($cep)) { $imovel['cep'] = $cep; }
            $imovel['preco'] = $preco;
            $imovel['area'] = $area;
            $imovel['quartos'] = $quartos;
            $imovel['banheiros'] = $banheiros;
            $imovel['garagem'] = $garagem;
            $imovel['destaque'] = $destaque;
            $imovel['imagens'] = $todas_imagens;
            if (isset($caracteristicas_post)) $caracteristicas_atual = $caracteristicas_post;
        } else {
            throw new Exception('Erro ao atualizar imóvel no banco de dados');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Excluir novas imagens que foram enviadas em caso de erro
        $uploads_temp = array_merge(
            (isset($novas_imagens) ? $novas_imagens : []), 
            (isset($substituicoes_novas) ? $substituicoes_novas : [])
        );
        if (!empty($uploads_temp)) {
            foreach ($uploads_temp as $imagem) {
                @unlink(__DIR__ . '/../../public/uploads/' . $imagem);
            }
        }
    }
}

// Helper: traduz códigos de erro de upload
if (!function_exists('upload_error_text')) {
    function upload_error_text($code) {
        $map = [
            UPLOAD_ERR_INI_SIZE   => 'Arquivo excede o tamanho máximo permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'Arquivo excede o limite de tamanho do formulário.',
            UPLOAD_ERR_PARTIAL    => 'Upload feito parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
            UPLOAD_ERR_EXTENSION  => 'Uma extensão do PHP interrompeu o upload.'
        ];
        return $map[$code] ?? ('Erro de upload (código ' . (int)$code . ').');
    }
}

// Incluir header administrativo
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Editar Imóvel</h1>
    <p class="breadcrumb">
    <a href="<?= BASE_URL ?>private/admin/dashboard.php">Dashboard</a> /
        <a href="listar.php">Imóveis</a> /
        <span>Editar</span>
    </p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <form action="editar.php?id=<?= $imovel_id ?>" method="post" enctype="multipart/form-data" class="imovel-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($imovel['titulo']) ?>" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecione o tipo de imóvel</option>
                    <option value="casa" <?= $imovel['tipo'] === 'casa' ? 'selected' : '' ?>>🏠 Casa</option>
                    <option value="casa_condominio" <?= $imovel['tipo'] === 'casa_condominio' ? 'selected' : '' ?>>🏘️ Casa em Condomínio</option>
                    <option value="apartamento" <?= $imovel['tipo'] === 'apartamento' ? 'selected' : '' ?>>🏢 Apartamento</option>
                    <option value="apartamento_mobiliado" <?= $imovel['tipo'] === 'apartamento_mobiliado' ? 'selected' : '' ?>>🏢🛋️ Apartamento Mobiliado</option>
                    <option value="sobrado" <?= $imovel['tipo'] === 'sobrado' ? 'selected' : '' ?>>🏘️ Sobrado</option>
                    <option value="chacara" <?= $imovel['tipo'] === 'chacara' ? 'selected' : '' ?>>🌾 Chácara</option>
                    <option value="semi_mobiliado" <?= $imovel['tipo'] === 'semi_mobiliado' ? 'selected' : '' ?>>🛋️ Semi Mobiliado</option>
                    <option value="terreno" <?= $imovel['tipo'] === 'terreno' ? 'selected' : '' ?>>🌿 Terreno</option>
                    <option value="loft" <?= $imovel['tipo'] === 'loft' ? 'selected' : '' ?>>🏙️ Loft</option>
                    <option value="comercial" <?= $imovel['tipo'] === 'comercial' ? 'selected' : '' ?>>🏪 Comercial</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="descricao">Descrição *</label>
            <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($imovel['descricao']) ?></textarea>
        </div>

        <?php if (is_array($caracCol) && count($caracCol) > 0): ?>
        <div class="form-group">
            <label>Características principais</label>
            <div class="caracteristicas-grid mobile-friendly" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;">
                <?php foreach ($lista_caracteristicas as $key => $rotulo): ?>
                    <label class="caracteristica-item" style="display:flex;gap:8px;align-items:center;padding:12px;background:#f8f9fa;border-radius:8px;border:2px solid transparent;cursor:pointer;transition:all 0.3s ease;">
                        <input type="checkbox" name="caracteristicas[]" value="<?= $key ?>" <?= in_array($key, $caracteristicas_atual) ? 'checked' : '' ?> style="width:20px;height:20px;margin:0;">
                        <span><?= htmlspecialchars($rotulo) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <small class="form-text">Atualize as características que se aplicam ao imóvel.</small>
        </div>
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" value="<?= htmlspecialchars($imovel['cep'] ?? '') ?>" placeholder="00000-000">
            </div>
            <div class="form-group">
                <label for="cidade">Cidade *</label>
                <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($imovel['cidade']) ?>" required>
            </div>
            <div class="form-group">
                <label for="bairro">Bairro *</label>
                <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($imovel['bairro']) ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($imovel['endereco'] ?? '') ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="preco">Preço (R$) *</label>
                <input type="text" id="preco" name="preco" value="<?= number_format($imovel['preco'], 2, ',', '.') ?>" required class="preco-input">
            </div>
            <div class="form-group">
                <label for="area">Área Total (m²)</label>
                <input type="text" id="area" name="area" value="<?= str_replace('.', ',', $imovel['area']) ?>" class="area-input" placeholder="Ex: 120,50">
                <small class="form-text">
                    <i class="fas fa-ruler"></i> 
                    Área total do imóvel em metros quadrados
                </small>
            </div>
        </div>

        <!-- Seção de Áreas Detalhadas -->
        <div class="form-section-header">
            <i class="fas fa-expand-arrows-alt"></i>Detalhamento de Áreas
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="area_privativa">Área Privativa (m²)</label>
                <input type="text" id="area_privativa" name="area_privativa" value="<?= isset($imovel['area_privativa']) ? str_replace('.', ',', $imovel['area_privativa']) : '' ?>" class="area-input" placeholder="Ex: 85,30">
                <small class="form-text">
                    <i class="fas fa-home"></i> 
                    Área de uso exclusivo do imóvel (quartos, sala, cozinha, etc.)
                </small>
            </div>
            <div class="form-group">
                <label for="area_comum">Área Comum (m²)</label>
                <input type="text" id="area_comum" name="area_comum" value="<?= isset($imovel['area_comum']) ? str_replace('.', ',', $imovel['area_comum']) : '' ?>" class="area-input" placeholder="Ex: 35,20">
                <small class="form-text">
                    <i class="fas fa-users"></i> 
                    Área de uso comum do condomínio (piscina, salão de festas, etc.)
                </small>
            </div>
        </div>
        
        <!-- Seção de Informações Internas -->
        <div class="form-section" style="background-color: #f0f8ff; padding: 20px; border-radius: 12px; margin: 25px 0; border-left: 5px solid #007bff;">
            <div class="form-section-header" style="margin-bottom: 20px; color: #007bff;">
                <h4 style="margin: 0; font-size: 18px;"><i class="fas fa-lock"></i> Informações Internas (Uso Administrativo)</h4>
            </div>
            
            <!-- Linha 1: Valores Financeiros -->
            <div class="form-row">
                <div class="form-group">
                    <label for="valor_condominio">Valor do condomínio</label>
                    <input type="text" 
                           id="valor_condominio" 
                           name="valor_condominio" 
                           value="<?= isset($imovel['valor_condominio']) && $imovel['valor_condominio'] > 0 ? number_format($imovel['valor_condominio'], 2, ',', '.') : '' ?>" 
                           placeholder="Digite o valor do condomínio (deixe vazio se não houver)">
                    <small class="form-text">
                        <i class="fas fa-eye-slash"></i> 
                        Taxa mensal de condomínio para controle interno (opcional)
                    </small>
                </div>
                <div class="form-group">
                    <label for="matricula">Número da matrícula</label>
                    <input type="text" 
                           id="matricula" 
                           name="matricula" 
                           value="<?= htmlspecialchars($imovel['matricula'] ?? '') ?>" 
                           placeholder="Ex: 12345, ABC123, 456-R" 
                           maxlength="100">
                    <small class="form-text">
                        <i class="fas fa-file-alt"></i> 
                        Número da matrícula do registro do imóvel (aceita números e letras)
                    </small>
                </div>
            </div>
            
            <!-- Linha 2: IPTU e Parcelas -->
            <div class="form-row">
                <div class="form-group">
                    <label for="valor_iptu">Valor mensal do IPTU</label>
                    <input type="text" 
                           id="valor_iptu" 
                           name="valor_iptu" 
                           value="<?= isset($imovel['valor_iptu']) && $imovel['valor_iptu'] > 0 ? number_format($imovel['valor_iptu'], 2, ',', '.') : '' ?>" 
                           placeholder="Digite o valor do IPTU mensal (deixe vazio se não souber)">
                    <small class="form-text">
                        <i class="fas fa-eye-slash"></i> 
                        Valor mensal do IPTU para controle interno (opcional)
                    </small>
                </div>
                <div class="form-group">
                    <label for="parcelas_iptu">Número de parcelas do IPTU</label>
                    <input type="text" 
                           id="parcelas_iptu" 
                           name="parcelas_iptu" 
                           value="<?= htmlspecialchars($imovel['parcelas_iptu'] ?? '') ?>" 
                           placeholder="Digite o número de parcelas do IPTU">
                    <small class="form-text">
                        <i class="fas fa-calendar-alt"></i> 
                        Quantidade de parcelas para pagamento do IPTU
                    </small>
                </div>
            </div>
            
            <!-- Linha 3: Taxa e Exclusividade -->
            <div class="form-row">
                <div class="form-group">
                    <label for="taxa_intermediacao">Taxa Intermediação (%)</label>
                    <input type="text" 
                           id="taxa_intermediacao" 
                           name="taxa_intermediacao" 
                           value="<?= isset($imovel['taxa_intermediacao']) && $imovel['taxa_intermediacao'] > 0 ? str_replace('.', ',', $imovel['taxa_intermediacao']) : '' ?>" 
                           placeholder="6,00" 
                           maxlength="5">
                    <small class="form-text">
                        <i class="fas fa-percentage"></i> 
                        Percentual da comissão de intermediação
                    </small>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" 
                               name="exclusividade" 
                               value="1" 
                               <?= isset($imovel['exclusividade']) && $imovel['exclusividade'] ? 'checked' : '' ?> 
                               style="width: auto; margin-right: 8px;">
                        <strong>Imóvel em Exclusividade</strong>
                    </label>
                    <small class="form-text">
                        <i class="fas fa-star"></i> 
                        Marque se o imóvel está sendo comercializado em exclusividade
                    </small>
                </div>
            </div>
            
            <!-- Linha 4: Controle de Chaves -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label>Chaves do imóvel</label>
                    <?php 
                        $chaves_selecionadas = isset($imovel['chaves_tipo']) ? explode(',', $imovel['chaves_tipo']) : [];
                    ?>
                    <div class="chaves-grid mobile-friendly">
                        <label class="chave-item">
                            <input type="checkbox" name="chaves_tipo[]" value="Portaria" 
                                   <?= in_array('Portaria', $chaves_selecionadas) ? 'checked' : '' ?>>
                            <span>Portaria</span>
                        </label>
                        <label class="chave-item">
                            <input type="checkbox" name="chaves_tipo[]" value="Proprietário" 
                                   <?= in_array('Proprietário', $chaves_selecionadas) ? 'checked' : '' ?>>
                            <span>Proprietário</span>
                        </label>
                        <label class="chave-item">
                            <input type="checkbox" name="chaves_tipo[]" value="Imobiliária" 
                                   <?= in_array('Imobiliária', $chaves_selecionadas) ? 'checked' : '' ?>>
                            <span>Imobiliária</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="chaves_copias">Quantidade de cópias</label>
                    <input type="number" 
                           id="chaves_copias" 
                           name="chaves_copias" 
                           min="0" 
                           max="50" 
                           value="<?= htmlspecialchars($imovel['chaves_copias'] ?? '0') ?>">
                    <small class="form-text">
                        <i class="fas fa-key"></i> 
                        Número total de cópias das chaves disponíveis
                    </small>
                </div>
                <div class="form-group">
                    <label for="observacoes_chaves">Observações adicionais</label>
                    <textarea id="observacoes_chaves" 
                              name="observacoes_chaves" 
                              rows="3" 
                              placeholder="Informações extras sobre as chaves..."><?= htmlspecialchars($imovel['observacoes_chaves'] ?? '') ?></textarea>
                    <small class="form-text">
                        <i class="fas fa-info-circle"></i> 
                        Detalhes adicionais sobre o controle de chaves
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Seção de Posição Solar -->
        <div class="form-section-header">
            <i class="fas fa-sun"></i>Posição Solar do Imóvel
        </div>
        
        <div class="form-group">
            <label for="posicao_solar">Posição solar principal</label>
            <div class="posicao-solar-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin:15px 0;">
                <?php foreach ($opcoes_posicao_solar as $key => $opcao): ?>
                    <label class="posicao-solar-item" style="display:flex;gap:12px;align-items:center;padding:16px;background:#fff;border-radius:12px;border:2px solid #e9ecef;cursor:pointer;transition:all 0.3s ease;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <input type="radio" name="posicao_solar" value="<?= $key ?>" <?= (isset($imovel['posicao_solar']) && $imovel['posicao_solar'] === $key) ? 'checked' : '' ?> style="width:20px;height:20px;margin:0;accent-color:#007bff;">
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                <span style="font-size:24px;"><?= $opcao['icon'] ?></span>
                                <strong style="color:#333;font-size:16px;"><?= htmlspecialchars($opcao['nome']) ?></strong>
                            </div>
                            <small style="color:#666;font-size:13px;"><?= htmlspecialchars($opcao['descricao']) ?></small>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <small class="form-text">
                <i class="fas fa-info-circle"></i> 
                Selecione a posição solar predominante do imóvel para ajudar os interessados.
            </small>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="quartos">Quartos</label>
                <input type="number" id="quartos" name="quartos" min="0" value="<?= $imovel['quartos'] ?>">
            </div>
            <div class="form-group">
                <label for="banheiros">Banheiros</label>
                <input type="number" id="banheiros" name="banheiros" min="0" value="<?= $imovel['banheiros'] ?>">
            </div>
            <div class="form-group">
                <label for="garagem">Vagas</label>
                <input type="number" id="garagem" name="garagem" min="0" value="<?= $imovel['garagem'] ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="destaque-checkbox" style="display:flex;gap:8px;align-items:center;padding:12px;background:#fff3cd;border-radius:8px;border:2px solid #ffc107;cursor:pointer;transition:all 0.3s ease;">
                <input type="checkbox" name="destaque" value="1" <?= $imovel['destaque'] ? 'checked' : '' ?> style="width:20px;height:20px;margin:0;accent-color:#ffc107;">
                <span><strong>⭐ Marcar como destaque</strong></span>
            </label>
            <small class="form-text">Imóveis em destaque aparecem primeiro nas listagens.</small>
        </div>
        
        <div class="form-group">
            <label>Imagens Atuais</label>
            <div class="imagens-grid">
                <?php foreach ($imovel['imagens'] as $index => $imagem): ?>
                    <div class="imagem-item" style="border:1px solid #eee; padding:10px; border-radius:8px;">
                        <img src="<?= BASE_URL ?>public/uploads/<?= htmlspecialchars($imagem) ?>" alt="Imagem <?= $index + 1 ?> do imóvel" style="max-width:180px; display:block; margin-bottom:8px;">
                        <div class="imagem-acoes" style="display:flex; gap:8px; align-items:center;">
                            <label style="display:flex; gap:6px; align-items:center;">
                                <input type="checkbox" name="imagens_remover[]" value="<?= htmlspecialchars($imagem) ?>">
                                <span>Remover</span>
                            </label>
                            <div>
                                <label style="font-size:12px;">Substituir:
                                    <input type="file" name="substituicoes[<?= $index ?>]" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="novas_imagens">Adicionar Novas Imagens</label>
            <input type="file" id="novas_imagens" name="novas_imagens[]" multiple accept="image/*">
            <small class="form-text">Selecione várias imagens (máx. 25)</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="listar.php" class="btn btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
// Incluir footer administrativo
include __DIR__ . '/../includes/admin-footer.php';
?>

<script>
// Máscara de CEP simples (se jQuery Mask já carregado no admin-footer)
if (window.jQuery && $.fn.mask) {
    $('#cep').mask('00000-000');
    
    // Garantir que o campo matrícula aceite números e letras
    $('#matricula').off('input.mask').on('input', function() {
        // Remove qualquer máscara que possa ter sido aplicada
        let value = $(this).val();
        // Permite apenas letras, números, hífens e espaços
        value = value.replace(/[^a-zA-Z0-9\-\s]/g, '');
        $(this).val(value);
    });
    
    // Máscara de preço livre para valores altos
    $('#preco').mask('000.000.000.000,00', {
        reverse: true
    });
    
    // Adicionar R$ no placeholder e permitir valores livres
    $('#preco').attr('placeholder', 'R$ 0,00');
    
    // Função para formatar valores monetários
    function formatMoney(input) {
        let value = $(input).val();
        // Remove tudo que não é dígito
        value = value.replace(/\D/g, '');
        
        if (value.length > 0) {
            // Converte para centavos
            let numValue = parseInt(value);
            // Divide por 100 para ter as casas decimais
            numValue = (numValue / 100).toFixed(2);
            // Formata com pontos e vírgula
            numValue = numValue.replace('.', ',');
            numValue = numValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            // Define o valor formatado
            $(input).val(numValue);
        } else {
            $(input).val('');
        }
    }
    
    // Aplicar formatação monetária apenas para o preço (obrigatório)
    $('#preco').off('input.mask').on('input', function() {
        formatMoney(this);
    });
    
    // Para campos opcionais, aplicar formatação apenas quando há valor
    $('#valor_condominio, #valor_iptu').off('input.mask').on('input', function() {
        if ($(this).val().trim() !== '') {
            formatMoney(this);
        }
    });
    
    // Permitir limpar os campos opcionais
    $('#valor_condominio, #valor_iptu').on('keydown', function(e) {
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if ($(this).val().length <= 1) {
                $(this).val('');
                e.preventDefault();
            }
        }
    });
}

// Busca ViaCEP ao sair do campo CEP
document.addEventListener('DOMContentLoaded', () => {
    const cepInput = document.getElementById('cep');
    if (!cepInput) return;

    const cidade = document.getElementById('cidade');
    const bairro = document.getElementById('bairro');
    const endereco = document.getElementById('endereco');

    function showToast(msg, tipo = 'error') {
        if (window.toastr) {
            toastr.options.timeOut = 4000;
            toastr[tipo](msg);
        } else {
            alert(msg);
        }
    }

    function limpaCampos() {
        if (endereco) endereco.value = '';
        if (bairro) bairro.value = '';
        if (cidade) cidade.value = '';
    }

    async function buscarCEP(cepLimpo) {
        try {
            const resp = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
            if (!resp.ok) throw new Error('Falha ao consultar o ViaCEP');
            const data = await resp.json();
            if (data.erro) {
                limpaCampos();
                showToast('CEP não encontrado. Verifique e tente novamente.');
                return;
            }
            if (endereco) endereco.value = [data.logradouro, data.complemento].filter(Boolean).join(' ');
            if (bairro) bairro.value = data.bairro || '';
            if (cidade) cidade.value = data.localidade || '';
        } catch (e) {
            showToast('Não foi possível buscar o CEP agora.');
        }
    }

    cepInput.addEventListener('blur', () => {
        const cep = cepInput.value.replace(/\D+/g, '');
        if (cep.length === 8) buscarCEP(cep);
    });

    // Melhorar interação com características
    const caracteristicasGrid = document.querySelector('.caracteristicas-grid');
    if (caracteristicasGrid) {
        console.log('✅ Grid de características encontrado!');
        
        // Garantir que cliques nos labels funcionem corretamente
        caracteristicasGrid.addEventListener('click', function(e) {
            const label = e.target.closest('.caracteristica-item');
            if (label && e.target.tagName !== 'INPUT') {
                const checkbox = label.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                    console.log('🔄 Característica alternada:', checkbox.value, '=', checkbox.checked);
                }
            }
        });
        
        // Atualizar visual quando checkbox mudar
        const checkboxes = caracteristicasGrid.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                console.log('✨ Característica selecionada/desmarcada:', this.value, 'Checked:', this.checked);
            });
        });
    }

    // Funcionalidade para checkboxes de chaves (usando mesma lógica das características)
    const chavesGrid = document.querySelector('.chaves-grid');
    if (chavesGrid) {
        console.log('✅ Grid de chaves encontrado!');
        
        // Usar a mesma lógica das características que já funciona
        chavesGrid.addEventListener('click', function(e) {
            const label = e.target.closest('.chave-item');
            if (label && e.target.tagName !== 'INPUT') {
                const checkbox = label.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                    console.log('🔄 Checkbox alternado:', checkbox.value, '=', checkbox.checked);
                }
            }
        });
        
        // Adicionar eventos de mudança nos checkboxes
        const checkboxes = chavesGrid.querySelectorAll('input[type="checkbox"]');
        console.log('📋 Checkboxes de chaves encontrados:', checkboxes.length);
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                console.log('✨ Chave selecionada/desmarcada:', this.value, 'Checked:', this.checked);
            });
        });
    } else {
        console.error('❌ Grid de chaves NÃO encontrado!');
    }

    // Funcionalidade para checkbox de destaque
    const destaqueCheckbox = document.querySelector('.destaque-checkbox');
    if (destaqueCheckbox) {
        console.log('✅ Checkbox de destaque encontrado!');
        
        destaqueCheckbox.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                    console.log('🔄 Destaque alternado:', checkbox.checked);
                }
            }
        });
        
        const checkbox = destaqueCheckbox.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                console.log('✨ Destaque selecionado/desmarcado:', this.checked);
            });
        }
    }

    // Funcionalidade para radio buttons de posição solar
    const posicaoSolarGrid = document.querySelector('.posicao-solar-grid');
    if (posicaoSolarGrid) {
        console.log('✅ Grid de posição solar encontrado!');
        
        posicaoSolarGrid.addEventListener('click', function(e) {
            const label = e.target.closest('.posicao-solar-item');
            if (label && e.target.tagName !== 'INPUT') {
                const radio = label.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                    console.log('🔄 Posição solar selecionada:', radio.value);
                }
            }
        });
        
        // Adicionar eventos de mudança nos radio buttons
        const radios = posicaoSolarGrid.querySelectorAll('input[type="radio"]');
        console.log('📋 Radio buttons de posição solar encontrados:', radios.length);
        
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                console.log('✨ Posição solar selecionada:', this.value);
            });
        });
    }

    // Validação do número máximo de imagens para novas imagens
    const novasImagensInput = document.getElementById('novas_imagens');
    if (novasImagensInput) {
        novasImagensInput.addEventListener('change', function() {
            const maxImages = 25;
            const files = this.files;
            
            if (files.length > maxImages) {
                showToast(`Você pode adicionar no máximo ${maxImages} novas imagens. ${files.length} imagens foram selecionadas.`, 'error');
                // Remove o excesso de arquivos mantendo apenas os primeiros 25
                const dt = new DataTransfer();
                for (let i = 0; i < maxImages; i++) {
                    if (files[i]) {
                        dt.items.add(files[i]);
                    }
                }
                this.files = dt.files;
                showToast(`Apenas as primeiras ${maxImages} imagens foram mantidas.`, 'warning');
            } else if (files.length > 0) {
                showToast(`${files.length} nova(s) imagem(ns) selecionada(s).`, 'success');
            }
        });
    }
});
</script>

<style>
/* Melhorar visibilidade das características */
.caracteristicas-grid {
    margin: 10px 0;
}

.caracteristica-item {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    min-height: 48px;
}

.caracteristica-item:hover {
    background: #e9ecef !important;
    border-color: #dee2e6 !important;
}

.caracteristica-item input[type="checkbox"]:checked {
    accent-color: #007bff;
}

.caracteristica-item:has(input:checked) {
    background: rgba(0, 123, 255, 0.1) !important;
    border-color: #007bff !important;
}

.caracteristica-item:has(input:checked) span {
    font-weight: 600;
    color: #007bff;
}

/* Estilos para checkboxes de chaves - igual às características */
.chaves-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 8px;
    margin: 10px 0;
}

.chave-item {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    min-height: 48px;
}

.chave-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin: 0;
    accent-color: #007bff;
}

.chave-item:hover {
    background: #e9ecef !important;
    border-color: #dee2e6 !important;
}

.chave-item:has(input:checked) {
    background: rgba(0, 123, 255, 0.1) !important;
    border-color: #007bff !important;
}

.chave-item:has(input:checked) span {
    font-weight: 600;
    color: #007bff;
}

/* Estilos para checkbox de destaque */
.destaque-checkbox {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    min-height: 48px;
}

.destaque-checkbox:hover {
    background: #fff8e1 !important;
    border-color: #ff9800 !important;
}

.destaque-checkbox:has(input:checked) {
    background: rgba(255, 193, 7, 0.2) !important;
    border-color: #ff9800 !important;
}

.destaque-checkbox:has(input:checked) span {
    color: #e65100;
}

/* Estilos para radio buttons de posição solar */
.posicao-solar-grid {
    margin: 15px 0;
}

.posicao-solar-item {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    min-height: 60px;
}

.posicao-solar-item:hover {
    background: #f8f9fa !important;
    border-color: #007bff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.posicao-solar-item input[type="radio"]:checked {
    accent-color: #007bff;
}

.posicao-solar-item:has(input:checked) {
    background: rgba(0, 123, 255, 0.1) !important;
    border-color: #007bff !important;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3) !important;
}

.posicao-solar-item:has(input:checked) strong {
    color: #007bff;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .caracteristicas-grid {
        grid-template-columns: 1fr !important;
        gap: 0.5rem !important;
    }
    
    .caracteristica-item {
        padding: 16px !important;
        font-size: 15px !important;
    }
    
    .chaves-grid {
        grid-template-columns: 1fr !important;
        gap: 0.5rem !important;
    }
    
    .chave-item {
        padding: 16px !important;
        font-size: 15px !important;
    }
    
    .destaque-checkbox {
        padding: 16px !important;
        font-size: 15px !important;
    }
    
    .posicao-solar-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }
    
    .posicao-solar-item {
        padding: 18px !important;
        font-size: 15px !important;
    }
}
</style>