<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');
require_once(__DIR__ . '/../config/config.php');

$error = '';
$success = '';

// Buscar proprietários para o select
$proprietarios = db_query("SELECT id_proprietario as id, nome FROM proprietarios ORDER BY nome ASC");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $preco = (float) str_replace(['.',''], ['',''], $_POST['preco'] ?? '0');
        $area = (float) str_replace(',', '.', $_POST['area'] ?? '0');
        $quartos = (int) ($_POST['quartos'] ?? 0);
        $banheiros = (int) ($_POST['banheiros'] ?? 0);
        $garagem = (int) ($_POST['garagem'] ?? 0);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $id_proprietario = (int) ($_POST['id_proprietario'] ?? 0);

        if (empty($titulo) || empty($descricao) || empty($tipo) || empty($cidade) || empty($bairro) || $preco <= 0 || $id_proprietario <= 0) {
            throw new Exception('Preencha todos os campos obrigatórios corretamente.');
        }

        $imagens = [];
        $uploadDir = '../public/uploads';

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

                    if ($fileName) $imagens[] = $fileName;
                }
            }
            if (empty($imagens)) throw new Exception('Nenhuma imagem válida foi enviada');
        } else throw new Exception('Pelo menos uma imagem é obrigatória');

        $imagens_str = implode(',', $imagens);

        $sql = "INSERT INTO imoveis (titulo, descricao, tipo, cidade, bairro, endereco, preco, area, quartos, banheiros, garagem, imagens, destaque, id_proprietario) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = db_query($sql, [$titulo,$descricao,$tipo,$cidade,$bairro,$endereco,$preco,$area,$quartos,$banheiros,$garagem,$imagens_str,$destaque,$id_proprietario]);

        if ($result) {
            $success = 'Imóvel cadastrado com sucesso!';
            $_POST = [];
        } else throw new Exception('Erro ao cadastrar imóvel no banco de dados');

    } catch (Exception $e) {
        $error = $e->getMessage();
        if (!empty($imagens)) {
            foreach ($imagens as $img) @unlink($uploadDir . '/' . $img);
        }
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

    <form action="" method="post" enctype="multipart/form-data" class="imovel-form">
        <div class="form-group">
            <label for="id_proprietario">Proprietário *</label>
            <select id="id_proprietario" name="id_proprietario" required>
                <option value="">Selecione o proprietário</option>
                <?php foreach ($proprietarios as $prop): ?>
                <option value="<?= $prop['id'] ?>" <?= ($_POST['id_proprietario'] ?? '') == $prop['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prop['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
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

        <div class="form-row">
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

        <div class="form-row">
            <div class="form-group">
                <label for="preco">Preço (R$) *</label>
                <input type="text" id="preco" name="preco" value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>" required>
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

        <div class="form-group">
            <label for="imagens">Imagens *</label>
            <input type="file" id="imagens" name="imagens[]" multiple accept="image/*" required>
            <small class="form-text">Selecione várias imagens (máx. 10, primeira imagem será a principal)</small>
        </div>

        <button type="submit" class="btn">Salvar Imóvel</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>