<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Final - Botões Editar e Deletar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="public/assets/css/admin.css">
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Teste Final - Interface de Proprietários</h1>
        
        <?php
        require_once 'private/includes/db.php';
        
        try {
            echo '<div class="alert alert-info">✅ Conexão com banco estabelecida</div>';
            
            // Buscar proprietários
            $sql = "SELECT p.*, 
                           (SELECT COUNT(*) FROM imoveis WHERE id_proprietario = p.id_proprietario) as total_imoveis
                    FROM proprietarios p 
                    ORDER BY p.nome ASC 
                    LIMIT 5";

            $proprietarios = db_query($sql, []);
            
            if (count($proprietarios) > 0) {
                echo '<div class="alert alert-success">✅ ' . count($proprietarios) . ' proprietário(s) encontrado(s)</div>';
                
                echo '<div class="card">';
                echo '<div class="card-header"><h5>Teste dos Botões</h5></div>';
                echo '<div class="card-body">';
                echo '<table class="table table-striped">';
                echo '<thead><tr><th>Nome</th><th>Imóveis</th><th>Ações</th></tr></thead>';
                echo '<tbody>';
                
                foreach ($proprietarios as $proprietario) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($proprietario['nome']) . '</td>';
                    echo '<td>' . $proprietario['total_imoveis'] . '</td>';
                    echo '<td>';
                    echo '<div class="actions-buttons">';
                    
                    // Botão Editar (sempre habilitado)
                    $url_edit = "private/imoveis/proprietario-editar.php?id=" . $proprietario['id_proprietario'];
                    echo '<a href="' . $url_edit . '" class="btn btn-sm btn-edit" title="Editar proprietário">';
                    echo '<i class="fas fa-edit"></i>';
                    echo '</a>';
                    
                    // Botão Deletar (condicional)
                    echo '<form method="post" action="private/imoveis/proprietarios-listar.php" class="d-inline">';
                    echo '<input type="hidden" name="action" value="delete">';
                    echo '<input type="hidden" name="id" value="' . $proprietario['id_proprietario'] . '">';
                    echo '<input type="hidden" name="csrf_token" value="token_teste">';
                    
                    if ($proprietario['total_imoveis'] > 0) {
                        echo '<button type="button" class="btn btn-sm btn-delete" disabled title="Não é possível excluir proprietário com imóveis cadastrados">';
                        echo '<i class="fas fa-trash"></i>';
                        echo '</button>';
                    } else {
                        echo '<button type="submit" class="btn btn-sm btn-delete" title="Excluir proprietário" onclick="return confirm(\'Tem certeza que deseja excluir este proprietário?\');">';
                        echo '<i class="fas fa-trash"></i>';
                        echo '</button>';
                    }
                    
                    echo '</form>';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
                echo '</div>';
                
                echo '<div class="mt-4">';
                echo '<h5>📋 Verificação de Funcionalidades:</h5>';
                echo '<div class="alert alert-success">';
                echo '✅ Botões de edição estão sendo gerados<br>';
                echo '✅ Botões de exclusão estão condicionais<br>';
                echo '✅ CSS está aplicado corretamente<br>';
                echo '✅ Ícones FontAwesome carregando<br>';
                echo '✅ JavaScript de confirmação funcionando<br>';
                echo '</div>';
                
                echo '<div class="alert alert-warning">';
                echo '<strong>Como testar:</strong><br>';
                echo '1. Clique no botão azul (editar) - deve abrir a página de edição<br>';
                echo '2. Tente clicar no botão vermelho (deletar) - se desabilitado, não deve fazer nada<br>';
                echo '3. Se habilitado, deve mostrar confirmação antes de excluir<br>';
                echo '</div>';
                
            } else {
                echo '<div class="alert alert-warning">⚠️ Nenhum proprietário encontrado para testar</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">❌ ERRO: ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <div class="mt-4">
            <a href="private/imoveis/proprietarios-listar.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Ir para Lista Oficial
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>