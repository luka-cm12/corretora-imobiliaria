<?php
require_once(__DIR__ . '/../includes/db.php');

// Puxar os contatos do banco
$contatos = db_query("SELECT * FROM contatos ORDER BY id DESC");
include __DIR__ . '/../includes/admin-header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes | Corretora Base</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 15px;
        }
        table th, table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background: #333;
            color: #fff;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .container {
            padding: 30px;
        }
    </style>
</head>
<body>
    <section class="page-header">
        <div class="container">
            <h1><i class="fas fa-users"></i> Lista de Clientes</h1>
            <p>Aqui estão todos os contatos recebidos pelo site</p>
        </div>
    </section>

    <section class="container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Assunto</th>
                    <th>Imóvel de Interesse</th>
                    <th>Mensagem</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($contatos && count($contatos) > 0): ?>
                    <?php foreach ($contatos as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['id']) ?></td>
                            <td><?= htmlspecialchars($c['nome']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['telefone']) ?></td>
                            <td><?= htmlspecialchars($c['assunto']) ?></td>
                            <td><?= htmlspecialchars($c['imovel_interesse']) ?></td>
                            <td><?= htmlspecialchars($c['mensagem']) ?></td>
                            <td><?= isset($c['created_at']) ? htmlspecialchars($c['created_at']) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Nenhum cliente encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

<?php include __DIR__ . '/../includes/admin-footer.php';  ?>
</body>
</html>
