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

// Lista padrão de características principais
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
        // Normaliza preço: remove milhares e converte vírgula decimal para ponto
        $preco = (float) str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0');
        $area = (float) str_replace(',', '.', $_POST['area'] ?? '0');
        $quartos = (int) ($_POST['quartos'] ?? 0);
        $banheiros = (int) ($_POST['banheiros'] ?? 0);
        $garagem = (int) ($_POST['garagem'] ?? 0);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $id_proprietario = (int) ($_POST['id_proprietario'] ?? 0);

        // Características principais (opcional)
        $caracteristicas_post = isset($_POST['caracteristicas']) && is_array($_POST['caracteristicas'])
            ? array_values(array_intersect(array_keys($lista_caracteristicas), $_POST['caracteristicas']))
            : [];
        $caracteristicas_json = json_encode($caracteristicas_post, JSON_UNESCAPED_UNICODE);

        if (empty($titulo) || empty($descricao) || empty($tipo) || empty($cidade) || empty($bairro) || $preco <= 0 || $id_proprietario <= 0) {
            throw new Exception('Preencha todos os campos obrigatórios corretamente.');
        }

        $imagens = [];
        $upload_erros = [];
        // Caminho relativo a partir de private/imoveis até a raiz: usar 'public/uploads'
        $uploadDir = 'public/uploads';

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

        // Verifica se a coluna 'caracteristicas' existe; se existir, inclui no INSERT
        $colCheck = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'caracteristicas'");
        if (is_array($colCheck) && count($colCheck) > 0) {
            // Características existe
            // Verifica coluna CEP
            $cepCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'cep'");
            if (is_array($cepCol) && count($cepCol) > 0) {
                $sql = "INSERT INTO imoveis (titulo, descricao, tipo, cidade, bairro, endereco, cep, preco, area, quartos, banheiros, garagem, imagens, destaque, id_proprietario, caracteristicas) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$titulo,$descricao,$tipo,$cidade,$bairro,$endereco,$cep,$preco,$area,$quartos,$banheiros,$garagem,$imagens_str,$destaque,$id_proprietario,$caracteristicas_json];
            } else {
                $sql = "INSERT INTO imoveis (titulo, descricao, tipo, cidade, bairro, endereco, preco, area, quartos, banheiros, garagem, imagens, destaque, id_proprietario, caracteristicas) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$titulo,$descricao,$tipo,$cidade,$bairro,$endereco,$preco,$area,$quartos,$banheiros,$garagem,$imagens_str,$destaque,$id_proprietario,$caracteristicas_json];
            }
        } else {
            // Características não existe
            // Verifica coluna CEP
            $cepCol = db_query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME = 'cep'");
            if (is_array($cepCol) && count($cepCol) > 0) {
                $sql = "INSERT INTO imoveis (titulo, descricao, tipo, cidade, bairro, endereco, cep, preco, area, quartos, banheiros, garagem, imagens, destaque, id_proprietario) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$titulo,$descricao,$tipo,$cidade,$bairro,$endereco,$cep,$preco,$area,$quartos,$banheiros,$garagem,$imagens_str,$destaque,$id_proprietario];
            } else {
                $sql = "INSERT INTO imoveis (titulo, descricao, tipo, cidade, bairro, endereco, preco, area, quartos, banheiros, garagem, imagens, destaque, id_proprietario) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$titulo,$descricao,$tipo,$cidade,$bairro,$endereco,$preco,$area,$quartos,$banheiros,$garagem,$imagens_str,$destaque,$id_proprietario];
            }
        }
        $result = db_query($sql, $params);

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
                <option value="">Selecione</option>
                <option value="casa" <?= ($_POST['tipo'] ?? '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                <option value="apartamento" <?= ($_POST['tipo'] ?? '') === 'apartamento' ? 'selected' : '' ?>>Apartamento</option>
                <option value="terreno" <?= ($_POST['tipo'] ?? '') === 'terreno' ? 'selected' : '' ?>>Terreno</option>
                <option value="comercial" <?= ($_POST['tipo'] ?? '') === 'comercial' ? 'selected' : '' ?>>Comercial</option>
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
            <div class="caracteristicas-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;">
                <?php foreach ($lista_caracteristicas as $key => $rotulo): ?>
                    <label style="display:flex;gap:8px;align-items:center;">
                        <input type="checkbox" name="caracteristicas[]" value="<?= $key ?>" <?= in_array($key, $_POST['caracteristicas'] ?? []) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($rotulo) ?></span>
                    </label>
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
                <label for="area">Área (m²)</label>
                <input type="text" id="area" name="area" value="<?= htmlspecialchars($_POST['area'] ?? '') ?>">
            </div>
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
                Selecione várias imagens (máx. 10). A primeira será a imagem principal.
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
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

<script>
// Máscara de CEP simples (se jQuery Mask já carregado no admin-footer)
if (window.jQuery && $.fn.mask) {
    $('#cep').mask('00000-000');
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
});
</script>