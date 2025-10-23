<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');
require_once(__DIR__ . '/../config/config.php');

$error = '';
$success = '';

// Garante token CSRF
if (function_exists('ensureCsrfToken')) {
    ensureCsrfToken();
} else {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Buscar proprietários para o select
$proprietarios = db_query("SELECT id_proprietario, nome FROM proprietarios ORDER BY nome ASC");

// Lista organizada de características por categorias/cômodos
$lista_caracteristicas = [
    'Quartos e Suítes' => [
        'suite' => 'Suíte',
        'closet' => 'Closet',
        'ar_condicionado' => 'Ar condicionado',
        'armarios_embutidos' => 'Armários embutidos',
        'suite_master' => 'Suíte master',
        'varanda_suite' => 'Varanda na suíte'
    ],
    'Banheiros e Bem-estar' => [
        'hidromassagem' => 'Hidromassagem',
        'agua_aquecida' => 'Água aquecida',
        'gas_central' => 'Gás central',
        'banheira' => 'Banheira',
        'box_blindex' => 'Box blindex',
        'sauna' => 'Sauna'
    ],
    'Áreas Sociais' => [
        'sala_de_estar' => 'Sala de estar',
        'varanda' => 'Varanda',
        'sacada' => 'Sacada',
        'sacada_gourmet' => 'Sacada gourmet',
        'area_gourmet' => 'Área gourmet',
        'churrasqueira' => 'Churrasqueira',
        'salao_de_festas' => 'Salão de festas',
        'quiosque' => 'Quiosque',
        'jardim' => 'Jardim',
        'terraço' => 'Terraço'
    ],
    'Lazer e Recreação' => [
        'piscina' => 'Piscina',
        'academia' => 'Academia',
        'quintal' => 'Quintal',
        'playground' => 'Playground',
        'quadra_esportiva' => 'Quadra esportiva',
        'sala_jogos' => 'Sala de jogos'
    ],
    'Funcionalidades' => [
        'elevador' => 'Elevador',
        'portaria_24h' => 'Portaria',
        'mobiliado' => 'Mobiliado',
        'pet_friendly' => 'Pet friendly',
        'lavanderia' => 'Lavanderia',
        'lareira' => 'Lareira',
        'interfone' => 'Interfone',
        'alarme' => 'Sistema de alarme',
        'garagem_coberta' => 'Garagem coberta'
    ]
];

// Ícones para cada categoria
$icones_categorias = [
    'Quartos e Suítes' => '🛏️',
    'Banheiros e Bem-estar' => '🛁',
    'Áreas Sociais' => '🏡',
    'Lazer e Recreação' => '🏊‍♂️',
    'Funcionalidades' => '⚙️'
];

// Lista de opções de posição solar
$opcoes_posicao_solar = [
    'norte' => ['nome' => 'Norte', 'descricao' => 'Maior incidência de sol', 'icon' => '☀️'],
    'sul' => ['nome' => 'Sul', 'descricao' => 'Menor incidência solar', 'icon' => '🌤️'],
    'leste' => ['nome' => 'Leste', 'descricao' => 'Sol da manhã', 'icon' => '🌅'],
    'oeste' => ['nome' => 'Oeste', 'descricao' => 'Sol da tarde', 'icon' => '🌇']
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verifica CSRF se disponível
        if (function_exists('verificaCsrfToken')) {
            verificaCsrfToken();
        }
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
    $cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');
        // Normaliza preço: remove R$, pontos de milhares e converte vírgula decimal para ponto
        $preco = (float) str_replace(['R$', '.', ',', ' '], ['', '', '.', ''], $_POST['preco'] ?? '0');
        $area = (float) str_replace(',', '.', $_POST['area'] ?? '0');
        
        // Novos campos de área
        $area_privativa = (float) str_replace(',', '.', $_POST['area_privativa'] ?? '0');
        $area_comum = (float) str_replace(',', '.', $_POST['area_comum'] ?? '0');
        
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
        $id_proprietario = (int) ($_POST['id_proprietario'] ?? 0);

        // Características principais (opcional) - adaptado para nova estrutura
        $todas_caracteristicas = [];
        foreach ($lista_caracteristicas as $categoria => $items) {
            $todas_caracteristicas = array_merge($todas_caracteristicas, $items);
        }
        
        $caracteristicas_post = isset($_POST['caracteristicas']) && is_array($_POST['caracteristicas'])
            ? array_values(array_intersect(array_keys($todas_caracteristicas), $_POST['caracteristicas']))
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
        if ($id_proprietario <= 0) {
            throw new Exception('Selecione um proprietário válido.');
        }

        $imagens = [];
        $upload_erros = [];
        // Caminho relativo a partir de private/imoveis até a raiz: usar 'public/uploads'
        $uploadDir = 'public/uploads';

        // IMPORTANTE: Para permitir upload de 25 imagens, verifique as configurações do PHP:
        // max_file_uploads = 25 (ou mais)
        // post_max_size = 100M (ou maior, dependendo do tamanho das imagens)
        // upload_max_filesize = 10M (ou maior, para cada imagem individual)

        if (!empty($_FILES['imagens']['name'][0])) {
            foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['imagens']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = upload_imagem([
                        'name' => $_FILES['imagens']['name'][$key],
                        'type' => $_FILES['imagens']['type'][$key],
                        'tmp_name' => $_FILES['imagens']['tmp_name'][$key],
                        'error' => $_FILES['imagens']['error'][$key],
                        'size' => $_FILES['imagens']['size'][$key]
                    ], $uploadDir, 1200, 800);
                    if ($fileName) {
                        $imagens[] = $fileName;
                    } else {
                        $upload_erros[] = "Falha ao processar imagem '" . htmlspecialchars($_FILES['imagens']['name'][$key]) . "'.";
                    }
                }
                else if ($_FILES['imagens']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $upload_erros[] = "Falha ao enviar imagem '" . htmlspecialchars($_FILES['imagens']['name'][$key]) . "': " . upload_error_text($_FILES['imagens']['error'][$key]);
                }
            }
            if (empty($imagens)) throw new Exception('Nenhuma imagem válida foi enviada');
        } else throw new Exception('Pelo menos uma imagem é obrigatória');

        $imagens_str = implode(',', $imagens);

        // Verifica se as colunas existem e monta o SQL dinamicamente
        $colCheck = db_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis'");
        $existing_columns = array_column($colCheck, 'COLUMN_NAME');
        
        // Campos base obrigatórios
        $fields = ['titulo', 'descricao', 'tipo', 'cidade', 'bairro', 'endereco', 'preco', 'area', 'quartos', 'banheiros', 'garagem', 'imagens', 'destaque', 'id_proprietario'];
        $values = [$titulo, $descricao, $tipo, $cidade, $bairro, $endereco, $preco, $area, $quartos, $banheiros, $garagem, $imagens_str, $destaque, $id_proprietario];
        
        // Adiciona campos opcionais se existirem
        if (in_array('cep', $existing_columns)) {
            $fields[] = 'cep';
            $values[] = $cep;
        }
        
        if (in_array('caracteristicas', $existing_columns)) {
            $fields[] = 'caracteristicas';
            $values[] = $caracteristicas_json;
        }
        
        if (in_array('valor_condominio', $existing_columns)) {
            $fields[] = 'valor_condominio';
            $values[] = $valor_condominio;
        }
        
        if (in_array('valor_iptu', $existing_columns)) {
            $fields[] = 'valor_iptu';
            $values[] = $valor_iptu;
        }
        
        if (in_array('matricula', $existing_columns)) {
            $fields[] = 'matricula';
            $values[] = $matricula;
        }
        
        if (in_array('parcelas_iptu', $existing_columns)) {
            $fields[] = 'parcelas_iptu';
            $values[] = $parcelas_iptu;
        }
        
        if (in_array('exclusividade', $existing_columns)) {
            $fields[] = 'exclusividade';
            $values[] = $exclusividade;
        }
        
        if (in_array('taxa_intermediacao', $existing_columns)) {
            $fields[] = 'taxa_intermediacao';
            $values[] = $taxa_intermediacao;
        }
        
        if (in_array('area_privativa', $existing_columns)) {
            $fields[] = 'area_privativa';
            $values[] = $area_privativa;
        }
        
        if (in_array('area_comum', $existing_columns)) {
            $fields[] = 'area_comum';
            $values[] = $area_comum;
        }
        
        if (in_array('chaves_tipo', $existing_columns)) {
            $fields[] = 'chaves_tipo';
            $values[] = $chaves_tipo;
        }
        
        if (in_array('chaves_copias', $existing_columns)) {
            $fields[] = 'chaves_copias';
            $values[] = $chaves_copias;
        }
        
        if (in_array('observacoes_chaves', $existing_columns)) {
            $fields[] = 'observacoes_chaves';
            $values[] = $observacoes_chaves;
        }
        
        if (in_array('posicao_solar', $existing_columns)) {
            $fields[] = 'posicao_solar';
            $values[] = $posicao_solar;
        }
        
        // Monta a query dinamicamente
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = "INSERT INTO imoveis (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        
        $result = db_query($sql, $values);

        if ($result) {
            $success = 'Imóvel cadastrado com sucesso!';
            if (!empty($upload_erros)) {
                $error .= ($error ? ' ' : '') . implode(' ', $upload_erros);
            }
            $_POST = [];
        } else throw new Exception('Erro ao cadastrar imóvel no banco de dados');

    } catch (Exception $e) {
        $error = $e->getMessage();
        if (!empty($imagens)) {
            foreach ($imagens as $img) @unlink(__DIR__ . '/../../public/uploads/' . $img);
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

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Adicionar Novo Imóvel</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="imovel-form mobile-optimized">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <!-- Seção 1: Informações Básicas -->
        <div class="form-section-header">
            <i class="fas fa-info-circle"></i>Informações Básicas
        </div>
        <div class="form-group">
            <label for="id_proprietario">Proprietário *</label>
            <select id="id_proprietario" name="id_proprietario" required>
                <option value="">Selecione o proprietário</option>
                <?php foreach ($proprietarios as $prop): ?>
                <option value="<?= $prop['id_proprietario'] ?>" <?= ($_POST['id_proprietario'] ?? '') == $prop['id_proprietario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prop['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <a href="proprietario-cadastrar.php" class="btn btn-small">+ Novo Proprietário</a>
        </div>


        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="tipo">Tipo *</label>
            <select id="tipo" name="tipo" required>
                <option value="">Selecione o tipo de imóvel</option>
                <option value="casa" <?= ($_POST['tipo'] ?? '') === 'casa' ? 'selected' : '' ?>>🏠 Casa</option>
                <option value="casa_condominio" <?= ($_POST['tipo'] ?? '') === 'casa_condominio' ? 'selected' : '' ?>>🏘️ Casa em Condomínio</option>
                <option value="apartamento" <?= ($_POST['tipo'] ?? '') === 'apartamento' ? 'selected' : '' ?>>🏢 Apartamento</option>
                <option value="apartamento_mobiliado" <?= ($_POST['tipo'] ?? '') === 'apartamento_mobiliado' ? 'selected' : '' ?>>🏢🛋️ Apartamento Mobiliado</option>
                <option value="sobrado" <?= ($_POST['tipo'] ?? '') === 'sobrado' ? 'selected' : '' ?>>🏘️ Sobrado</option>
                <option value="chacara" <?= ($_POST['tipo'] ?? '') === 'chacara' ? 'selected' : '' ?>>🌾 Chácara</option>
                <option value="semi_mobiliado" <?= ($_POST['tipo'] ?? '') === 'semi_mobiliado' ? 'selected' : '' ?>>🛋️ Semi Mobiliado</option>
                <option value="terreno" <?= ($_POST['tipo'] ?? '') === 'terreno' ? 'selected' : '' ?>>🌿 Terreno</option>
                <option value="loft" <?= ($_POST['tipo'] ?? '') === 'loft' ? 'selected' : '' ?>>🏙️ Loft</option>
                <option value="comercial" <?= ($_POST['tipo'] ?? '') === 'comercial' ? 'selected' : '' ?>>🏪 Comercial</option>
            </select>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição *</label>
            <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
        </div>

        <!-- Seção 2: Características -->
        <div class="form-section-header">
            <i class="fas fa-star"></i>Características do Imóvel
        </div>
        
        <div class="form-group">
            <label>Características principais</label>
            <div class="caracteristicas-container">
                <?php foreach ($lista_caracteristicas as $categoria => $items): ?>
                    <div class="categoria-caracteristicas">
                        <h4 class="categoria-titulo">
                            <span class="categoria-icone"><?= $icones_categorias[$categoria] ?? '📋' ?></span>
                            <?= htmlspecialchars($categoria) ?>
                        </h4>
                        <div class="caracteristicas-grid mobile-friendly" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;margin-bottom:20px;">
                            <?php foreach ($items as $key => $rotulo): ?>
                                <label class="caracteristica-item" style="display:flex;gap:8px;align-items:center;padding:12px;background:#f8f9fa;border-radius:8px;border:2px solid transparent;cursor:pointer;transition:all 0.3s ease;">
                                    <input type="checkbox" name="caracteristicas[]" value="<?= $key ?>" <?= in_array($key, $_POST['caracteristicas'] ?? []) ? 'checked' : '' ?> style="width:20px;height:20px;margin:0;">
                                    <span><?= htmlspecialchars($rotulo) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <small class="form-text">Selecione as características que se aplicam ao imóvel.</small>
        </div>

        <!-- Seção 3: Localização -->
        <div class="form-section-header">
            <i class="fas fa-map-marker-alt"></i>Localização
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>" placeholder="00000-000" class="cep-mask">
                <small class="form-text">Digite o CEP e saindo do campo buscaremos o endereço automaticamente.</small>
            </div>
            <div class="form-group">
                <label for="cidade">Cidade *</label>
                <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="bairro">Bairro *</label>
                <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>">
        </div>

        <!-- Seção 4: Detalhes do Imóvel -->
        <div class="form-section-header">
            <i class="fas fa-home"></i>Detalhes do Imóvel
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="preco">Preço (R$) *</label>
                <input type="text" id="preco" name="preco" value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>" required class="money-mask">
            </div>
            <div class="form-group">
                <label for="area">Área Total (m²)</label>
                <input type="text" id="area" name="area" value="<?= htmlspecialchars($_POST['area'] ?? '') ?>" placeholder="Ex: 120,50">
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
                <input type="text" id="area_privativa" name="area_privativa" value="<?= htmlspecialchars($_POST['area_privativa'] ?? '') ?>" placeholder="Ex: 85,30">
                <small class="form-text">
                    <i class="fas fa-home"></i> 
                    Área de uso exclusivo do imóvel (quartos, sala, cozinha, etc.)
                </small>
            </div>
            <div class="form-group">
                <label for="area_comum">Área Comum (m²)</label>
                <input type="text" id="area_comum" name="area_comum" value="<?= htmlspecialchars($_POST['area_comum'] ?? '') ?>" placeholder="Ex: 35,20">
                <small class="form-text">
                    <i class="fas fa-users"></i> 
                    Área de uso comum do condomínio (piscina, salão de festas, etc.)
                </small>
            </div>
        </div>

        <!-- Seção de Informações Internas -->
        <div class="form-section" style="background-color: #f0f8ff; padding: 20px; border-radius: 12px; margin: 25px 0; border-left: 5px solid #007bff;">
            <div class="form-section-header" style="margin-bottom: 20px; color: #007bff;">
                <i class="fas fa-lock"></i>Informações Internas (Uso Administrativo)
            </div>
            
            <!-- Linha 1: Valores Financeiros -->
            <div class="form-row">
                <div class="form-group">
                    <label for="valor_condominio">Valor do condomínio</label>
                    <input type="text" id="valor_condominio" name="valor_condominio" value="<?= htmlspecialchars($_POST['valor_condominio'] ?? '') ?>" placeholder="Digite o valor do condomínio (deixe vazio se não houver)">
                    <small class="form-text">
                        <i class="fas fa-eye-slash"></i> 
                        Taxa mensal de condomínio para controle interno (opcional)
                    </small>
                </div>
                <div class="form-group">
                    <label for="matricula">Número da matrícula</label>
                    <input type="text" id="matricula" name="matricula" value="<?= htmlspecialchars($_POST['matricula'] ?? '') ?>" placeholder="Ex: 12345, ABC123, 456-R" maxlength="100">
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
                    <input type="text" id="valor_iptu" name="valor_iptu" value="<?= htmlspecialchars($_POST['valor_iptu'] ?? '') ?>" placeholder="Digite o valor do IPTU mensal (deixe vazio se não souber)">
                    <small class="form-text">
                        <i class="fas fa-eye-slash"></i> 
                        Valor mensal do IPTU para controle interno (opcional)
                    </small>
                </div>
                <div class="form-group">
                    <label for="parcelas_iptu">Número de parcelas do IPTU</label>
                    <input type="text" id="parcelas_iptu" name="parcelas_iptu" value="<?= htmlspecialchars($_POST['parcelas_iptu'] ?? '') ?>" placeholder="Digite o número de parcelas do IPTU">
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
                    <input type="text" id="taxa_intermediacao" name="taxa_intermediacao" value="<?= htmlspecialchars($_POST['taxa_intermediacao'] ?? '') ?>" placeholder="6,00" maxlength="5">
                    <small class="form-text">
                        <i class="fas fa-percentage"></i> 
                        Percentual da comissão de intermediação
                    </small>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="exclusividade" value="1" <?= ($_POST['exclusividade'] ?? 0) ? 'checked' : '' ?> style="width: auto; margin-right: 8px;">
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
                    <div class="chaves-grid mobile-friendly">
                        <label class="chave-item">
                            <input type="checkbox" name="chaves_tipo[]" value="Portaria" <?= in_array('Portaria', $_POST['chaves_tipo'] ?? []) ? 'checked' : '' ?>>
                            <span>Portaria</span>
                        </label>
                        <label class="chave-item">
                            <input type="checkbox" name="chaves_tipo[]" value="Proprietário" <?= in_array('Proprietário', $_POST['chaves_tipo'] ?? []) ? 'checked' : '' ?>>
                            <span>Proprietário</span>
                        </label>
                        <label class="chave-item">
                            <input type="checkbox" name="chaves_tipo[]" value="Imobiliária" <?= in_array('Imobiliária', $_POST['chaves_tipo'] ?? []) ? 'checked' : '' ?>>
                            <span>Imobiliária</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="chaves_copias">Quantidade de cópias</label>
                    <input type="number" id="chaves_copias" name="chaves_copias" min="0" max="20" value="<?= htmlspecialchars($_POST['chaves_copias'] ?? '0') ?>" placeholder="Digite a quantidade de cópias">
                    <small class="form-text">
                        <i class="fas fa-key"></i> 
                        Quantas cópias das chaves existem
                    </small>
                </div>
            </div>
            
            <!-- Linha 5: Observações -->
            <div class="form-row">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="observacoes_chaves">Observações adicionais</label>
                    <textarea id="observacoes_chaves" name="observacoes_chaves" rows="3" style="width: 100%; resize: vertical;"><?= htmlspecialchars($_POST['observacoes_chaves'] ?? '') ?></textarea>
                    <small class="form-text">
                        <i class="fas fa-info-circle"></i> 
                        Informações adicionais sobre as chaves ou acesso ao imóvel
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
                        <input type="radio" name="posicao_solar" value="<?= $key ?>" <?= ($_POST['posicao_solar'] ?? '') === $key ? 'checked' : '' ?> style="width:20px;height:20px;margin:0;accent-color:#007bff;">
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
                <input type="number" id="quartos" name="quartos" min="0" value="<?= htmlspecialchars($_POST['quartos'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label for="banheiros">Banheiros</label>
                <input type="number" id="banheiros" name="banheiros" min="0" value="<?= htmlspecialchars($_POST['banheiros'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label for="garagem">Vagas</label>
                <input type="number" id="garagem" name="garagem" min="0" value="<?= htmlspecialchars($_POST['garagem'] ?? '0') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="destaque" value="1" <?= ($_POST['destaque'] ?? 0) ? 'checked' : '' ?>>
                Marcar como destaque
            </label>
        </div>

        <!-- Seção 5: Imagens e Finalização -->
        <div class="form-section-header">
            <i class="fas fa-images"></i>Imagens do Imóvel
        </div>
        
        <div class="form-group">
            <label for="imagens">Selecionar Imagens *</label>
            <input type="file" id="imagens" name="imagens[]" multiple accept="image/*" required>
            <small class="form-text">
                <i class="fas fa-info-circle"></i> 
                Selecione várias imagens (máx. 25). A primeira será a imagem principal.
            </small>
        </div>

        <!-- Botão flutuante para mobile -->
        <button type="submit" class="btn mobile-save-btn d-lg-none">
            <i class="fas fa-save me-2"></i>Salvar Imóvel
        </button>
        
        <!-- Botão normal para desktop -->
        <button type="submit" class="btn d-none d-lg-block">
            <i class="fas fa-save me-2"></i>Salvar Imóvel
        </button>
    </form>
    
    <!-- Debug info para testar características -->
    <div id="debugInfo" class="debug-info">
        <div>Características: <span id="caracteristicasCount">0</span></div>
        <div id="caracteristicasList"></div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

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
        const debugInfo = document.getElementById('debugInfo');
        const caracteristicasCount = document.getElementById('caracteristicasCount');
        const caracteristicasList = document.getElementById('caracteristicasList');
        
        function updateDebugInfo() {
            const checkboxes = caracteristicasGrid.querySelectorAll('input[type="checkbox"]');
            const selecionadas = Array.from(checkboxes)
                .filter(box => box.checked)
                .map(box => {
                    const label = box.parentElement.querySelector('span');
                    return { value: box.value, text: label ? label.textContent : box.value };
                });
            
            if (caracteristicasCount) caracteristicasCount.textContent = selecionadas.length;
            if (caracteristicasList) {
                caracteristicasList.innerHTML = selecionadas
                    .map(item => `<div style="font-size:10px;">${item.text}</div>`)
                    .join('');
            }
            
            if (debugInfo) {
                debugInfo.classList.toggle('show', selecionadas.length > 0);
            }
            
            console.log('Características selecionadas:', selecionadas);
        }
        
        // Garantir que cliques nos labels funcionem corretamente
        caracteristicasGrid.addEventListener('click', function(e) {
            const label = e.target.closest('.caracteristica-item');
            if (label && e.target.tagName !== 'INPUT') {
                const checkbox = label.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                    updateDebugInfo();
                }
            }
        });
        
        // Atualizar debug quando checkbox mudar diretamente
        const checkboxes = caracteristicasGrid.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateDebugInfo);
        });
        
        // Inicializar debug
        updateDebugInfo();
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
        // Buscar elemento por outras formas
        console.log('🔍 Tentando encontrar por outras classes...');
        const allGrids = document.querySelectorAll('[class*="chaves"]');
        console.log('Elementos com "chaves" no nome:', allGrids.length);
        allGrids.forEach((el, i) => {
            console.log(`Grid ${i}:`, el.className);
        });
    }

    // Melhorar feedback visual dos campos obrigatórios
    const form = document.querySelector('.imovel-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            const requiredFields = [
                { id: 'titulo', name: 'Título' },
                { id: 'tipo', name: 'Tipo' },
                { id: 'descricao', name: 'Descrição' },
                { id: 'cidade', name: 'Cidade' },
                { id: 'bairro', name: 'Bairro' },
                { id: 'preco', name: 'Preço' },
                { id: 'id_proprietario', name: 'Proprietário' }
            ];
            
            // Limpar mensagens de erro anteriores
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.error-border').forEach(el => el.classList.remove('error-border'));
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                
                // Validação especial para preço
                if (field.id === 'preco') {
                    if (!element || !element.value || element.value.trim() === '' || element.value === 'R$ 0,00') {
                        hasError = true;
                        element.classList.add('error-border');
                        
                        const errorMsg = document.createElement('small');
                        errorMsg.className = 'field-error';
                        errorMsg.style.color = '#dc3545';
                        errorMsg.textContent = `${field.name} é obrigatório`;
                        element.parentNode.appendChild(errorMsg);
                    }
                } else {
                    // Validação normal para outros campos
                    if (element && (!element.value || element.value.trim() === '')) {
                        hasError = true;
                        element.classList.add('error-border');
                        
                        const errorMsg = document.createElement('small');
                        errorMsg.className = 'field-error';
                        errorMsg.style.color = '#dc3545';
                        errorMsg.textContent = `${field.name} é obrigatório`;
                        element.parentNode.appendChild(errorMsg);
                    }
                }
            });
            
            // Verificar preço - validação simplificada
            const precoElement = document.getElementById('preco');
            if (precoElement) {
                const precoValue = precoElement.value.trim();
                
                // Verifica se está vazio ou é zero
                if (!precoValue || precoValue === '' || precoValue === '0,00' || precoValue === 'R$ 0,00' || precoValue === '0') {
                    hasError = true;
                    precoElement.classList.add('error-border');
                    const errorMsg = document.createElement('small');
                    errorMsg.className = 'field-error';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.textContent = 'O preço é obrigatório e deve ser maior que zero';
                    precoElement.parentNode.appendChild(errorMsg);
                }
            }
            
            if (hasError) {
                e.preventDefault();
                showToast('Por favor, corrija os campos marcados em vermelho.', 'error');
                // Rolar para o primeiro campo com erro
                const firstError = document.querySelector('.error-border');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
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

    // Validação do número máximo de imagens
    const imagensInput = document.getElementById('imagens');
    if (imagensInput) {
        imagensInput.addEventListener('change', function() {
            const maxImages = 25;
            const files = this.files;
            
            if (files.length > maxImages) {
                showToast(`Você pode selecionar no máximo ${maxImages} imagens. ${files.length} imagens foram selecionadas.`, 'error');
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
                showToast(`${files.length} imagem(ns) selecionada(s).`, 'success');
            }
        });
    }
});
</script>

<style>
.error-border {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.field-error {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

/* Melhorar visibilidade das características */
.caracteristicas-container {
    margin: 15px 0;
}

.categoria-caracteristicas {
    margin-bottom: 25px;
    padding: 15px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.categoria-titulo {
    font-size: 16px;
    font-weight: 600;
    color: #495057;
    margin: 0 0 12px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 8px;
}

.categoria-icone {
    font-size: 20px;
    display: inline-block;
    width: 24px;
    text-align: center;
}

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
}

/* Debug info */
.debug-info {
    position: fixed;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    z-index: 1000;
    display: none;
}

.debug-info.show {
    display: block;
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

@media (max-width: 768px) {
    .chaves-grid {
        grid-template-columns: 1fr !important;
        gap: 0.5rem !important;
    }
    
    .chave-item {
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