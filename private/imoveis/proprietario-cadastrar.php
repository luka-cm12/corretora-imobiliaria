<?php
require_once(__DIR__ . '/../includes/db.php');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if (empty($nome) || empty($cpf)) {
        $error = "Nome e CPF são obrigatórios.";
    } else {
        $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
        $result = db_query($sql, [$nome, $cpf, $telefone, $email, $endereco]);

        if ($result > 0) {
            $success = "Proprietário cadastrado com sucesso!";
            $_POST = []; // limpa form
        } else {
            $error = "Erro ao cadastrar proprietário.";
        }
    }
}

include __DIR__ . '/../includes/admin-header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Proprietário</title>
</head>
<body>
  <h1>Cadastrar Proprietário</h1>
  
  <?php if ($error): ?><div style="color:red;"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div style="color:green;"><?= $success ?></div><?php endif; ?>

  <form method="POST">
    <label>Nome *</label><br>
    <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required><br><br>

    <label>CPF *</label><br>
    <input type="text" name="cpf" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" required><br><br>

    <label>Telefone</label><br>
    <input type="text" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"><br><br>

    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"><br><br>

    <label>Endereço</label><br>
    <textarea name="endereco"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea><br><br>

    <button type="submit">Salvar Proprietário</button>
  </form>

  <a href="adicionar.php">Voltar para cadastro de imóveis</a>
</body>
</html>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>