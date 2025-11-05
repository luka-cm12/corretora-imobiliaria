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

// Debug: verificar valores carregados do banco
error_log("DEBUG EDITAR - Valores carregados do banco para imóvel ID {$imovel_id}:");
error_log("  Preço: " . ($imovel['preco'] ?? 'NULL'));
error_log("  Condomínio: " . ($imovel['valor_condominio'] ?? 'NULL'));  
error_log("  IPTU: " . ($imovel['valor_iptu'] ?? 'NULL'));
error_log("  Todos os campos: " . json_encode(array_keys($imovel)));

// Verificação de integridade dos valores
if (isset($imovel['preco']) && isset($imovel['valor_condominio'])) {
    if ($imovel['preco'] == $imovel['valor_condominio'] && $imovel['preco'] > 0) {
        error_log("⚠️ ALERTA: Preço e condomínio têm o MESMO valor - possível problema!");
    }
}

// Log do valor formatado que será exibido no HTML
$preco_formatado = isset($imovel['preco']) && $imovel['preco'] > 0 ? number_format((float)$imovel['preco'], 2, ',', '.') : '';
$condominio_formatado = isset($imovel['valor_condominio']) && $imovel['valor_condominio'] > 0 ? number_format($imovel['valor_condominio'], 2, ',', '.') : '';
error_log("  Preço formatado para HTML: '$preco_formatado'");
error_log("  Condomínio formatado para HTML: '$condominio_formatado'");

// Garante colunas necessárias (executa ALTER TABLE se ausentes)
if (function_exists('ensure_table_column')) {
    // Características: usa JSON quando disponível; aqui adotamos TEXT por compatibilidade ampla.
    ensure_table_column('imoveis', 'caracteristicas', 'TEXT NULL');
    // CEP: apenas dígitos; usamos CHAR(8) NULL
    ensure_table_column('imoveis', 'cep', 'CHAR(8) NULL');
}

// Lista organizada de características por categorias/cômodos (mesma do adicionar.php)
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
        'garagem_coberta' => 'Garagem coberta',
        'estacionamento' => 'Estacionamento'
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
        
        // Processar preço - GARANTIR que seja o preço do imóvel, não do condomínio
        $preco_raw = trim($_POST['preco'] ?? '0');
        $preco = 0;
        if (!empty($preco_raw)) {
            // Remove R$, pontos (milhares) e espaços, converte vírgula em ponto
            $preco_limpo = str_replace(['R$', '.', ' '], ['', '', ''], $preco_raw);
            $preco_limpo = str_replace(',', '.', $preco_limpo);
            $preco = (float) $preco_limpo;
        }
        
        $area = (float) str_replace(',', '.', $_POST['area'] ?? '0');
        
        // Valores internos (condomínio e IPTU) - SEPARADOS do preço principal
        $valor_condominio = 0;
        $condominio_raw = trim($_POST['valor_condominio'] ?? '');
        if (!empty($condominio_raw)) {
            $condominio_limpo = str_replace(['R$', '.', ' '], ['', '', ''], $condominio_raw);
            $condominio_limpo = str_replace(',', '.', $condominio_limpo);
            $valor_condominio = (float) $condominio_limpo;
        }
        
        $valor_iptu = 0;
        $iptu_raw = trim($_POST['valor_iptu'] ?? '');
        if (!empty($iptu_raw)) {
            $iptu_limpo = str_replace(['R$', '.', ' '], ['', '', ''], $iptu_raw);
            $iptu_limpo = str_replace(',', '.', $iptu_limpo);
            $valor_iptu = (float) $iptu_limpo;
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
        // Debug COMPLETO dos valores processados
        error_log("DEBUG EDITAR - VALORES PROCESSADOS:");
        error_log("  Preço recebido: " . var_export($_POST['preco'] ?? 'VAZIO', true));
        error_log("  Preço processado: " . $preco);
        error_log("  Condomínio recebido: " . var_export($_POST['valor_condominio'] ?? 'VAZIO', true));
        error_log("  Condomínio processado: " . $valor_condominio);
        error_log("  IPTU recebido: " . var_export($_POST['valor_iptu'] ?? 'VAZIO', true));
        error_log("  IPTU processado: " . $valor_iptu);
        error_log("  Área recebida: " . var_export($_POST['area'] ?? 'VAZIO', true));
        error_log("  Área processada: " . $area);
        error_log("  Área privativa recebida: " . var_export($_POST['area_privativa'] ?? 'VAZIO', true));
        error_log("  Área privativa processada: " . $area_privativa);
        error_log("  Área comum recebida: " . var_export($_POST['area_comum'] ?? 'VAZIO', true));
        error_log("  Área comum processada: " . $area_comum);
        
        if ($preco <= 0) {
            throw new Exception('O preço deve ser maior que zero. Valor recebido: ' . $preco);
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

        // CORREÇÃO CRÍTICA: Construir parâmetros na ordem EXATA do SQL
        $params = [];
        
        // Grupo 1: Campos básicos obrigatórios
        $params[] = $titulo;
        $params[] = $descricao; 
        $params[] = $tipo;
        $params[] = $cidade;
        $params[] = $bairro;
        $params[] = $endereco;
        
        // CEP condicional (vem logo após endereco no SQL)
        if (is_array($cepCol) && count($cepCol) > 0) {
            $sqlUpdate = str_replace('%CEP%', 'cep = ?, ', $sqlUpdate);
            $params[] = $cep;
        } else {
            $sqlUpdate = str_replace('%CEP%', '', $sqlUpdate);
        }
        
        // PREÇO (obrigatório) - vem após CEP no SQL
        $params[] = $preco;
        
        // Valores internos opcionais (na ordem do SQL)
        if (is_array($condominioCol) && count($condominioCol) > 0) {
            $sqlUpdate = str_replace('%CONDOMINIO%', 'valor_condominio = ?, ', $sqlUpdate);
            $params[] = $valor_condominio;
        } else {
            $sqlUpdate = str_replace('%CONDOMINIO%', '', $sqlUpdate);
        }

        if (is_array($iptuCol) && count($iptuCol) > 0) {
            $sqlUpdate = str_replace('%IPTU%', 'valor_iptu = ?, ', $sqlUpdate);
            $params[] = $valor_iptu;
        } else {
            $sqlUpdate = str_replace('%IPTU%', '', $sqlUpdate);
        }

        if (is_array($parcelasIptuCol) && count($parcelasIptuCol) > 0) {
            $sqlUpdate = str_replace('%PARCELAS_IPTU%', 'parcelas_iptu = ?, ', $sqlUpdate);
            $params[] = $parcelas_iptu;
        } else {
            $sqlUpdate = str_replace('%PARCELAS_IPTU%', '', $sqlUpdate);
        }

        if (is_array($matriculaCol) && count($matriculaCol) > 0) {
            $sqlUpdate = str_replace('%MATRICULA%', 'matricula = ?, ', $sqlUpdate);
            $params[] = $matricula;
        } else {
            $sqlUpdate = str_replace('%MATRICULA%', '', $sqlUpdate);
        }

        if (is_array($exclusividadeCol) && count($exclusividadeCol) > 0) {
            $sqlUpdate = str_replace('%EXCLUSIVIDADE%', 'exclusividade = ?, ', $sqlUpdate);
            $params[] = $exclusividade;
        } else {
            $sqlUpdate = str_replace('%EXCLUSIVIDADE%', '', $sqlUpdate);
        }

        if (is_array($taxaCol) && count($taxaCol) > 0) {
            $sqlUpdate = str_replace('%TAXA%', 'taxa_intermediacao = ?, ', $sqlUpdate);
            $params[] = $taxa_intermediacao;
        } else {
            $sqlUpdate = str_replace('%TAXA%', '', $sqlUpdate);
        }

        if (is_array($chavesTipoCol) && count($chavesTipoCol) > 0) {
            $sqlUpdate = str_replace('%CHAVES_TIPO%', 'chaves_tipo = ?, ', $sqlUpdate);
            $params[] = $chaves_tipo;
        } else {
            $sqlUpdate = str_replace('%CHAVES_TIPO%', '', $sqlUpdate);
        }

        if (is_array($chavesCopiasCol) && count($chavesCopiasCol) > 0) {
            $sqlUpdate = str_replace('%CHAVES_COPIAS%', 'chaves_copias = ?, ', $sqlUpdate);
            $params[] = $chaves_copias;
        } else {
            $sqlUpdate = str_replace('%CHAVES_COPIAS%', '', $sqlUpdate);
        }

        if (is_array($observacoesChavesCol) && count($observacoesChavesCol) > 0) {
            $sqlUpdate = str_replace('%OBSERVACOES_CHAVES%', 'observacoes_chaves = ?, ', $sqlUpdate);
            $params[] = $observacoes_chaves;
        } else {
            $sqlUpdate = str_replace('%OBSERVACOES_CHAVES%', '', $sqlUpdate);
        }
        
        // ÁREA TOTAL (obrigatória) - vem após campos internos
        $params[] = $area;
        
        // Áreas detalhadas opcionais
        if (is_array($areaPrivativaCol) && count($areaPrivativaCol) > 0) {
            $sqlUpdate = str_replace('%AREA_PRIVATIVA%', 'area_privativa = ?, ', $sqlUpdate);
            $params[] = $area_privativa;
        } else {
            $sqlUpdate = str_replace('%AREA_PRIVATIVA%', '', $sqlUpdate);
        }

        if (is_array($areaComumCol) && count($areaComumCol) > 0) {
            $sqlUpdate = str_replace('%AREA_COMUM%', 'area_comum = ?, ', $sqlUpdate);
            $params[] = $area_comum;
        } else {
            $sqlUpdate = str_replace('%AREA_COMUM%', '', $sqlUpdate);
        }
        
        // Campos básicos finais (na ordem do SQL)
        $params[] = $quartos;
        $params[] = $banheiros;
        $params[] = $garagem;
        $params[] = $imagens_str;
        $params[] = $destaque;
        
        // Campos opcionais finais
        if (is_array($posicaoSolarCol) && count($posicaoSolarCol) > 0) {
            $sqlUpdate = str_replace('%POSICAO_SOLAR%', ', posicao_solar = ?', $sqlUpdate);
            $params[] = $posicao_solar;
        } else {
            $sqlUpdate = str_replace('%POSICAO_SOLAR%', '', $sqlUpdate);
        }

        if (is_array($caracCol) && count($caracCol) > 0) {
            $sqlUpdate = str_replace('%CARAC%', ', caracteristicas = ?', $sqlUpdate);
            $params[] = $caracteristicas_json;
        } else {
            $sqlUpdate = str_replace('%CARAC%', '', $sqlUpdate);
        }
        
        // ID do imóvel (WHERE clause)
        $params[] = $imovel_id;
        
        // Debug: log da query final e parâmetros
        error_log("DEBUG SQL FINAL:");
        error_log("Query: " . $sqlUpdate);
        error_log("Parâmetros (count=" . count($params) . "): " . json_encode($params));
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
            
            // IMPORTANTE: Recarregar dados atualizados do banco para garantir consistência
            $imovel_atualizado = db_query(
                "SELECT * FROM imoveis WHERE id = ?",
                [$imovel_id]
            );
            
            if (is_array($imovel_atualizado) && count($imovel_atualizado) > 0) {
                $imovel = $imovel_atualizado[0];
                $imovel['imagens'] = $todas_imagens; // Manter imagens atualizadas
                
                // Debug: verificar valores após atualização
                error_log("DEBUG EDITAR - Valores após atualização no banco:");
                error_log("  Preço: " . ($imovel['preco'] ?? 'NULL'));
                error_log("  Condomínio: " . ($imovel['valor_condominio'] ?? 'NULL'));
                error_log("  IPTU: " . ($imovel['valor_iptu'] ?? 'NULL'));
                
                // Recarregar características se disponível
                if (is_array($caracCol) && count($caracCol) > 0 && !empty($imovel['caracteristicas'])) {
                    $decoded = json_decode($imovel['caracteristicas'], true);
                    if (is_array($decoded)) $caracteristicas_atual = $decoded;
                }
            } else {
                error_log("ERRO: Não foi possível recarregar dados atualizados do imóvel");
            }
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
    
    <form action="editar.php?id=<?= $imovel_id ?>&t=<?= time() ?>" method="post" enctype="multipart/form-data" class="imovel-form">
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
                    <option value="pavilhao" <?= $imovel['tipo'] === 'pavilhao' ? 'selected' : '' ?>>🏭 Pavilhão</option>
                    <option value="fazenda" <?= $imovel['tipo'] === 'fazenda' ? 'selected' : '' ?>>🚜 Fazenda</option>
                    <option value="laja_terrea" <?= $imovel['tipo'] === 'laja_terrea' ? 'selected' : '' ?>>🏘️ Laja Térrea</option>
                    <option value="sala_area" <?= $imovel['tipo'] === 'sala_area' ? 'selected' : '' ?>>📦 Sala Aérea</option>
                    <option value="area_terras" <?= $imovel['tipo'] === 'area_terras' ? 'selected' : '' ?>>🌍 Área de Terras</option>
                    <option value="loteamento" <?= $imovel['tipo'] === 'loteamento' ? 'selected' : '' ?>>🗺️ Loteamento</option>
                    <option value="condominio_fechado" <?= $imovel['tipo'] === 'condominio_fechado' ? 'selected' : '' ?>>🏛️ Condomínio Fechado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="finalidade">Finalidade *</label>
                <select id="finalidade" name="finalidade" required>
                    <option value="">Selecione a finalidade</option>
                    <option value="venda" <?= ($imovel['finalidade'] ?? 'venda') === 'venda' ? 'selected' : '' ?>>💰 Venda</option>
                    <option value="locacao" <?= ($imovel['finalidade'] ?? 'venda') === 'locacao' ? 'selected' : '' ?>>🏠 Locação</option>
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
                                    <input type="checkbox" name="caracteristicas[]" value="<?= $key ?>" <?= in_array($key, $caracteristicas_atual) ? 'checked' : '' ?> style="width:20px;height:20px;margin:0;">
                                    <span><?= htmlspecialchars($rotulo) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
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
                <!-- Debug: Preço do banco = <?= var_export($imovel['preco'] ?? 'NULL', true) ?> -->
                <input type="text" id="preco" name="preco" value="<?= isset($imovel['preco']) && $imovel['preco'] > 0 ? number_format((float)$imovel['preco'], 2, ',', '.') : '' ?>" required class="money-mask" placeholder="R$ 0,00">
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
                    <!-- Debug: Condomínio do banco = <?= var_export($imovel['valor_condominio'] ?? 'NULL', true) ?> -->
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
                    <!-- Debug: IPTU do banco = <?= var_export($imovel['valor_iptu'] ?? 'NULL', true) ?> -->
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

<style>
/* Estilos específicos para posição solar */
.posicao-solar-item:hover {
    border-color: #007bff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.posicao-solar-item:has(input:checked) {
    border-color: #007bff !important;
    background-color: #e3f2fd !important;
    box-shadow: 0 4px 12px rgba(0,123,255,0.3) !important;
}

/* Feedback visual para cliques */
.posicao-solar-item:active {
    transform: translateY(1px);
}

/* Garantir que radio buttons sejam visíveis */
.posicao-solar-item input[type="radio"] {
    appearance: auto !important;
    -webkit-appearance: radio !important;
}
</style>

<script>
// PROBLEMA DOS PARÂMETROS CORRIGIDO - Modo simples
console.log('� PARÂMETROS SQL CORRIGIDOS - Testando formulário');

if (window.jQuery) {
    console.log('✅ jQuery disponível - modo simples sem máscaras automáticas');
    
    // Máscara de CEP 
    $('#cep').mask('00000-000');
    
    // Garantir que o campo matrícula aceite números e letras
    $('#matricula').off('input.mask').on('input', function() {
        let value = $(this).val();
        value = value.replace(/[^a-zA-Z0-9\-\s]/g, '');
        $(this).val(value);
    });
    
    // PROTEÇÃO MÁXIMA: Apenas aplicar máscaras durante digitação ativa
    console.log('💰 Configurando proteção de valores...');
    
    // Armazenar valores originais para proteção
    const valoresOriginais = {
        preco: $('#preco').val(),
        condominio: $('#valor_condominio').val(),
        iptu: $('#valor_iptu').val()
    };
    
    console.log('� Valores originais protegidos:', valoresOriginais);
    
    // Remover TODAS as máscaras automáticas
    $('#preco, #valor_condominio, #valor_iptu').off('.mask');
    
    // Função CONSERVADORA para formatar apenas quando o usuário digita
    function aplicarMascaraConservativa(input) {
        $(input).on('keyup', function(e) {
            // APENAS formatar durante digitação ativa
            if (e.key && e.key.length === 1 && /[0-9]/.test(e.key)) {
                let value = $(this).val().replace(/\D/g, '');
                
                if (value.length > 0) {
                    let numValue = parseInt(value);
                    numValue = (numValue / 100).toFixed(2);
                    numValue = numValue.replace('.', ',');
                    numValue = numValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    $(this).val(numValue);
                }
            }
        });
    }
    
    // Aplicar APENAS ao preço (obrigatório)
    aplicarMascaraConservativa($('#preco'));
    
    // Para campos opcionais, ser EXTREMAMENTE conservador
    $('#valor_condominio, #valor_iptu').on('focus', function() {
        console.log('📝 Campo focado:', this.id, 'Valor atual:', $(this).val());
    });
}

// Busca ViaCEP ao sair do campo CEP
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM carregado - Iniciando funcionalidades do formulário');
    
    // PRESERVAR e PROTEGER valores originais dos campos no carregamento da página
    const precoField = document.getElementById('preco');
    const condominioField = document.getElementById('valor_condominio');
    const iptuField = document.getElementById('valor_iptu');
    
    const valoresOriginais = {
        preco: precoField?.value || '',
        condominio: condominioField?.value || '',
        iptu: iptuField?.value || ''
    };
    
    console.log('💰 Valores originais preservados:');
    console.log('   Preço:', valoresOriginais.preco);
    console.log('   Condomínio:', valoresOriginais.condominio);
    console.log('   IPTU:', valoresOriginais.iptu);
    
    // PROTEÇÃO: Verificar se algum valor foi trocado incorretamente
    function verificarValores() {
        const precoAtual = precoField?.value || '';
        const condominioAtual = condominioField?.value || '';
        
        // Se o preço está vazio MAS o condomínio tem valor, pode ter havido troca
        if (precoAtual === '' && condominioAtual !== '' && valoresOriginais.preco !== '') {
            console.warn('⚠️ POSSÍVEL PROBLEMA: Preço vazio mas condomínio preenchido');
            console.warn('   Restaurando preço original:', valoresOriginais.preco);
            if (precoField) precoField.value = valoresOriginais.preco;
        }
        
        // Se o preço tem o valor do condomínio, corrigir
        if (precoAtual === valoresOriginais.condominio && precoAtual !== valoresOriginais.preco) {
            console.warn('⚠️ PROBLEMA DETECTADO: Preço contém valor do condomínio');
            console.warn('   Corrigindo preço de:', precoAtual, 'para:', valoresOriginais.preco);
            if (precoField) precoField.value = valoresOriginais.preco;
        }
    }
    
    // Verificar valores após um tempo (depois que jQuery Mask executar)
    setTimeout(verificarValores, 500);
    
    // Debug: verificar se há valores pré-selecionados na posição solar
    const posicaoSolarRadios = document.querySelectorAll('input[name="posicao_solar"]');
    const posicaoSelecionada = Array.from(posicaoSolarRadios).find(r => r.checked);
    if (posicaoSelecionada) {
        console.log('📍 Posição solar pré-selecionada:', posicaoSelecionada.value);
    } else {
        console.log('📍 Nenhuma posição solar pré-selecionada');
    }

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
        
        // Melhorar evento de clique para labels
        posicaoSolarGrid.addEventListener('click', function(e) {
            // Se clicou no label ou no span, procurar o radio button
            let target = e.target;
            let label = target.closest('.posicao-solar-item');
            
            if (label) {
                const radio = label.querySelector('input[type="radio"]');
                if (radio && target.tagName !== 'INPUT') {
                    // Desmarcar todos os outros radio buttons primeiro
                    const allRadios = posicaoSolarGrid.querySelectorAll('input[type="radio"]');
                    allRadios.forEach(r => r.checked = false);
                    
                    // Marcar apenas o selecionado
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log('🔄 Posição solar selecionada:', radio.value);
                }
            }
        });
        
        // Adicionar eventos diretos nos radio buttons também
        const radios = posicaoSolarGrid.querySelectorAll('input[type="radio"]');
        console.log('📋 Radio buttons de posição solar encontrados:', radios.length);
        
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                console.log('✨ Posição solar selecionada:', this.value);
                // Atualizar visual de seleção
                const allLabels = posicaoSolarGrid.querySelectorAll('.posicao-solar-item');
                allLabels.forEach(l => l.style.backgroundColor = '');
                
                const selectedLabel = this.closest('.posicao-solar-item');
                if (selectedLabel) {
                    selectedLabel.style.backgroundColor = '#e3f2fd';
                }
            });
            
            // Marcar o inicial se houver valor
            if (radio.checked) {
                radio.dispatchEvent(new Event('change'));
            }
        });
    } else {
        console.error('❌ Grid de posição solar NÃO encontrado!');
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

// ===== DEBUG ADICIONAL =====
console.log('🔍 EXECUTANDO DEBUG FINAL...');

// Aguardar um tempo e verificar se valores mudaram
setTimeout(function() {
    console.log('⏰ VERIFICAÇÃO APÓS 2 SEGUNDOS:');
    console.log('  Preço atual:', $('#preco').val());
    console.log('  Condomínio atual:', $('#valor_condominio').val()); 
    console.log('  IPTU atual:', $('#valor_iptu').val());
    
    // Verificar se há alguma intervenção do jQuery Mask
    if (window.jQuery && $.fn.mask) {
        console.log('⚠️ jQuery Mask está presente - pode estar interferindo');
        
        // Forçar remoção de máscaras
        $('#preco, #valor_condominio, #valor_iptu').unmask();
        console.log('🚫 Máscaras removidas forçadamente');
    }
}, 2000);
</script>

<style>
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