<?php
require_once 'private/includes/auth.php';
require_once 'private/includes/db.php';
require_once 'private/includes/functions.php';
require_login();

// Verificar se o ID do imóvel foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php');
    exit;
}

$imovel_id = intval($_GET['id']);

// Buscar os dados do imóvel
$imovel = db_query(
    "SELECT * FROM imoveis WHERE id = ?", 
    [$imovel_id]
);

if (!$imovel || $imovel->num_rows === 0) {
    header('Location: listar.php');
    exit;
}

$imovel = $imovel->fetch_assoc();
$imovel['imagens'] = explode(',', $imovel['imagens']);

// Processar formulário de edição
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        
        // Processar upload de novas imagens
        $uploadDir = 'public/uploads/';
        $novas_imagens = [];
        $imagens_para_manter = $_POST['imagens_existentes'] ?? [];
        $imagens_removidas = [];
        
        // Identificar imagens que foram removidas
        foreach ($imovel['imagens'] as $imagem_existente) {
            if (!in_array($imagem_existente, $imagens_para_manter)) {
                $imagens_removidas[] = $imagem_existente;
            }
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
                    }
                }
            }
        }
        
        // Combinar imagens mantidas com novas imagens
        $todas_imagens = array_merge($imagens_para_manter, $novas_imagens);
        
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
            
            // Excluir imagens removidas
            foreach ($imagens_removidas as $imagem_removida) {
                @unlink($uploadDir . $imagem_removida);
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
        if (!empty($novas_imagens)) {
            foreach ($novas_imagens as $imagem) {
                @unlink($uploadDir . $imagem);
            }
        }
    }
}

// Incluir header administrativo
include 'private/includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Editar Imóvel</h1>
    <p class="breadcrumb">
        <a href="private/admin/dashboard.php">Dashboard</a> /
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
                    <div class="imagem-item">
                        <img src="public/uploads/<?= htmlspecialchars($imagem) ?>" alt="Imagem <?= $index + 1 ?> do imóvel">
                        <label class="checkbox-container">
                            <input type="checkbox" name="imagens_existentes[]" value="<?= htmlspecialchars($imagem) ?>" checked>
                            <span class="checkmark"></span>
                            <span class="remove-text">Remover</span>
                        </label>
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
include 'private/includes/admin-footer.php';
?>