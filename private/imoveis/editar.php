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
        $quartos = (int) ($_POST['quartos'] ?? 0);
        $banheiros = (int) ($_POST['banheiros'] ?? 0);
        $garagem = (int) ($_POST['garagem'] ?? 0);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        
        // Validações básicas
        if (empty($titulo) || empty($descricao) || empty($tipo) || empty($cidade) || empty($bairro) || $preco <= 0) {
            throw new Exception('Preencha todos os campos obrigatórios');
        }
        
    // Diretório de uploads (upload_imagem usa caminho relativo à raiz do projeto)
    // Em functions.php, upload_imagem monta __DIR__ . '/../../' . $pasta
    // Portanto devemos usar 'public/uploads' aqui
    $uploadDir = 'public/uploads';

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
        $result = db_query(
            "UPDATE imoveis SET 
                titulo = ?, 
                descricao = ?, 
                tipo = ?, 
                cidade = ?, 
                bairro = ?, 
                endereco = ?, 
                preco = ?, 
                area = ?, 
                quartos = ?, 
                banheiros = ?, 
                garagem = ?, 
                imagens = ?, 
                destaque = ? 
             WHERE id = ?",
            [
                $titulo, $descricao, $tipo, $cidade, $bairro, $endereco, 
                $preco, $area, $quartos, $banheiros, $garagem, 
                $imagens_str, $destaque, $imovel_id
            ]
        );
        
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
            $imovel['preco'] = $preco;
            $imovel['area'] = $area;
            $imovel['quartos'] = $quartos;
            $imovel['banheiros'] = $banheiros;
            $imovel['garagem'] = $garagem;
            $imovel['destaque'] = $destaque;
            $imovel['imagens'] = $todas_imagens;
        } else {
            throw new Exception('Erro ao atualizar imóvel no banco de dados');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Excluir novas imagens que foram enviadas em caso de erro
        $uploads_temp = array_merge($novas_imagens, $substituicoes_novas);
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
                    <option value="">Selecione</option>
                    <option value="casa" <?= $imovel['tipo'] === 'casa' ? 'selected' : '' ?>>Casa</option>
                    <option value="apartamento" <?= $imovel['tipo'] === 'apartamento' ? 'selected' : '' ?>>Apartamento</option>
                    <option value="terreno" <?= $imovel['tipo'] === 'terreno' ? 'selected' : '' ?>>Terreno</option>
                    <option value="comercial" <?= $imovel['tipo'] === 'comercial' ? 'selected' : '' ?>>Comercial</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="descricao">Descrição *</label>
            <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($imovel['descricao']) ?></textarea>
        </div>
        
        <div class="form-row">
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
                <label for="area">Área (m²)</label>
                <input type="text" id="area" name="area" value="<?= str_replace('.', ',', $imovel['area']) ?>" class="area-input">
            </div>
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
            <label>
                <input type="checkbox" name="destaque" value="1" <?= $imovel['destaque'] ? 'checked' : '' ?>>
                Marcar como destaque
            </label>
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
            <small class="form-text">Selecione várias imagens (máx. 10)</small>
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