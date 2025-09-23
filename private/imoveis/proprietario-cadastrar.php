<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nome) || empty($cpf)) {
            throw new Exception('Nome e CPF são obrigatórios.');
        }

        $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email) VALUES (?, ?, ?, ?)";
        $result = db_query($sql, [$nome, $cpf, $telefone, $email]);

        if ($result) {
            $success = 'Proprietário cadastrado com sucesso!';
            $_POST = [];
        } else {
            throw new Exception('Erro ao cadastrar proprietário.');
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <h1>Cadastrar Proprietário</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="post" class="proprietario-form">
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <button type="submit" class="btn">Salvar Proprietário</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
