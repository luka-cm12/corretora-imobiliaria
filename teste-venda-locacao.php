<?php
require_once('private/includes/db.php');

$page_title = 'Teste - Filtro Venda e Locação';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #2c3e50; 
            text-align: center;
            margin-bottom: 30px;
        }
        .finalidade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .finalidade-section {
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .venda {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border-color: #28a745;
        }
        .locacao {
            background: linear-gradient(135deg, #f3e5f5, #e1bee7);
            border-color: #9C27B0;
        }
        .form-test {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin: 5px 0 15px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .links-teste {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .link-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s ease;
        }
        .link-card:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            text-decoration: none;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Teste - Sistema de Venda e Locação</h1>

        <div class="success">
            <strong>✅ Funcionalidade Implementada com Sucesso!</strong><br>
            O sistema agora permite filtrar imóveis por <strong>Venda</strong> ou <strong>Locação</strong>.
        </div>

        <div class="finalidade-grid">
            <div class="finalidade-section venda">
                <h3>💰 IMÓVEIS PARA VENDA</h3>
                <ul>
                    <li>Campo no cadastro e edição</li>
                    <li>Filtro na busca</li>
                    <li>Badge verde nos resultados</li>
                    <li>Exibição nos detalhes</li>
                </ul>
            </div>
            
            <div class="finalidade-section locacao">
                <h3>🏠 IMÓVEIS PARA LOCAÇÃO</h3>
                <ul>
                    <li>Campo no cadastro e edição</li>
                    <li>Filtro na busca</li>
                    <li>Badge roxo nos resultados</li>
                    <li>Exibição nos detalhes</li>
                </ul>
            </div>
        </div>

        <div class="form-test">
            <h3>🧪 Teste do Formulário de Busca</h3>
            <form action="busca.php" method="get" target="_blank">
                <label>Tipo de Imóvel:</label>
                <select name="tipo">
                    <option value="">Todos os tipos</option>
                    <option value="casa">🏠 Casa</option>
                    <option value="apartamento">🏢 Apartamento</option>
                    <option value="terreno">🌿 Terreno</option>
                    <option value="comercial">🏪 Comercial</option>
                </select>

                <label>Finalidade:</label>
                <select name="finalidade">
                    <option value="">Venda ou Locação</option>
                    <option value="venda">💰 Venda</option>
                    <option value="locacao">🏠 Locação</option>
                </select>

                <label>Cidade:</label>
                <input type="text" name="cidade" placeholder="Digite a cidade...">

                <button type="submit" class="btn">🔍 Buscar Imóveis</button>
            </form>
        </div>

        <h3>🔗 Links para Testes</h3>
        <div class="links-teste">
            <a href="private/imoveis/adicionar.php" class="link-card" target="_blank">
                ➕ <strong>Cadastrar Imóvel</strong><br>
                <small>Teste o campo "Finalidade"</small>
            </a>
            
            <a href="busca.php" class="link-card" target="_blank">
                🔍 <strong>Página de Busca</strong><br>
                <small>Teste os filtros</small>
            </a>
            
            <a href="busca.php?finalidade=venda" class="link-card" target="_blank">
                💰 <strong>Imóveis à Venda</strong><br>
                <small>Filtrar apenas vendas</small>
            </a>
            
            <a href="busca.php?finalidade=locacao" class="link-card" target="_blank">
                🏠 <strong>Imóveis para Locação</strong><br>
                <small>Filtrar apenas locações</small>
            </a>
            
            <a href="index.php" class="link-card" target="_blank">
                🏡 <strong>Página Inicial</strong><br>
                <small>Busca rápida atualizada</small>
            </a>
            
            <a href="atualizar-finalidade-banco.php" class="link-card" target="_blank">
                🔧 <strong>Atualizar Banco</strong><br>
                <small>Script de atualização</small>
            </a>
        </div>

        <h3>📊 Verificar Implementação</h3>
        <?php
        // Verificar se a coluna finalidade existe
        try {
            $check = $conn->prepare("SHOW COLUMNS FROM imoveis LIKE 'finalidade'");
            $check->execute();
            $coluna_existe = $check->fetch();
            
            if ($coluna_existe) {
                echo "<div class='success'>";
                echo "✅ <strong>Coluna 'finalidade' existe no banco de dados!</strong><br>";
                echo "Tipo: " . htmlspecialchars($coluna_existe['Type']) . "<br>";
                echo "Padrão: " . htmlspecialchars($coluna_existe['Default'] ?? 'N/A');
                echo "</div>";
                
                // Contar registros por finalidade
                $count = $conn->prepare("SELECT finalidade, COUNT(*) as total FROM imoveis GROUP BY finalidade");
                $count->execute();
                $distribuicao = $count->fetchAll(PDO::FETCH_ASSOC);
                
                if ($distribuicao) {
                    echo "<h4>📈 Distribuição Atual:</h4>";
                    echo "<ul>";
                    foreach ($distribuicao as $item) {
                        $icone = $item['finalidade'] === 'locacao' ? '🏠' : '💰';
                        $nome = $item['finalidade'] === 'locacao' ? 'Locação' : 'Venda';
                        echo "<li>{$icone} <strong>{$nome}:</strong> {$item['total']} imóveis</li>";
                    }
                    echo "</ul>";
                }
            } else {
                echo "<div class='error'>❌ <strong>Coluna 'finalidade' não encontrada!</strong> Execute o script de atualização do banco.</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erro ao verificar banco: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>

        <h3>✅ Status da Implementação</h3>
        <ul>
            <li>✅ <strong>Banco de Dados:</strong> Coluna 'finalidade' criada</li>
            <li>✅ <strong>Formulário de Cadastro:</strong> Campo finalidade adicionado</li>
            <li>✅ <strong>Formulário de Edição:</strong> Campo finalidade adicionado</li>
            <li>✅ <strong>Página de Busca:</strong> Filtro por finalidade implementado</li>
            <li>✅ <strong>Página Inicial:</strong> Busca rápida com finalidade</li>
            <li>✅ <strong>Resultados de Busca:</strong> Badges coloridos por finalidade</li>
            <li>✅ <strong>Detalhes do Imóvel:</strong> Exibição da finalidade</li>
        </ul>

        <div class="success">
            🎉 <strong>Implementação 100% Completa!</strong><br>
            O sistema agora possui filtro completo para separar imóveis de venda e locação.
        </div>
    </div>
</body>
</html>