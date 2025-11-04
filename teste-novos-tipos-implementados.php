<?php
require_once('private/includes/db.php');

// Função para formatar tipo do imóvel (atualizada)
function formatar_tipo_imovel($tipo) {
    $tipos = [
        'casa' => '🏠 Casa',
        'casa_condominio' => '🏘️ Casa em Condomínio',
        'apartamento' => '🏢 Apartamento',
        'apartamento_mobiliado' => '🏢🛋️ Apartamento Mobiliado',
        'sobrado' => '🏘️ Sobrado',
        'chacara' => '🌾 Chácara',
        'semi_mobiliado' => '🛋️ Semi Mobiliado',
        'terreno' => '🌿 Terreno',
        'loft' => '🏙️ Loft',
        'comercial' => '🏪 Comercial',
        'pavilhao' => '🏭 Pavilhão',
        'fazenda' => '🚜 Fazenda',
        'laja_terrea' => '🏘️ Laja Térrea',
        'sala_area' => '📦 Sala Área',
        'area_terras' => '🌍 Área de Terras',
        'loteamento' => '🗺️ Loteamento',
        'condominio_fechado' => '🏛️ Condomínio Fechado'
    ];
    
    return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
}

$page_title = 'Teste - Novos Tipos de Imóveis';
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
            max-width: 800px;
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
        .tipos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .tipo-item {
            padding: 15px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        .tipo-item:hover {
            border-color: #3498db;
            background: #f0f8ff;
            transform: translateY(-2px);
        }
        .tipo-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .tipo-name {
            font-weight: bold;
            color: #2c3e50;
        }
        .novos-tipos {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border-color: #28a745;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .form-test {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Novos Tipos de Imóveis Implementados</h1>

        <div class="success">
            <strong>Sucesso!</strong> Foram adicionados 7 novos tipos de imóveis ao sistema:
            <ul>
                <li>🏭 Pavilhão</li>
                <li>🚜 Fazenda</li>
                <li>🏘️ Laja Térrea</li>
                <li>📦 Sala Área</li>
                <li>🌍 Área de Terras</li>
                <li>🗺️ Loteamento</li>
                <li>🏛️ Condomínio Fechado</li>
            </ul>
        </div>

        <h2>📋 Lista Completa de Tipos de Imóveis</h2>
        
        <div class="tipos-grid">
            <?php
            $todos_tipos = [
                'casa', 'casa_condominio', 'apartamento', 'apartamento_mobiliado',
                'sobrado', 'chacara', 'semi_mobiliado', 'terreno', 'loft', 'comercial',
                'pavilhao', 'fazenda', 'laja_terrea', 'sala_area', 'area_terras', 
                'loteamento', 'condominio_fechado'
            ];
            
            foreach ($todos_tipos as $index => $tipo):
                $is_novo = in_array($tipo, ['pavilhao', 'fazenda', 'laja_terrea', 'sala_area', 'area_terras', 'loteamento', 'condominio_fechado']);
            ?>
                <div class="tipo-item <?= $is_novo ? 'novos-tipos' : '' ?>">
                    <span class="tipo-icon"><?= explode(' ', formatar_tipo_imovel($tipo))[0] ?></span>
                    <span class="tipo-name"><?= formatar_tipo_imovel($tipo) ?></span>
                    <?php if ($is_novo): ?>
                        <span style="float: right; color: #28a745; font-weight: bold;">NOVO!</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-test">
            <h3>🧪 Teste do Formulário de Seleção</h3>
            <label for="tipo-select">Selecione um tipo de imóvel:</label>
            <select id="tipo-select" onchange="mostrarSelecao(this.value)">
                <option value="">Selecione o tipo de imóvel</option>
                <?php foreach ($todos_tipos as $tipo): ?>
                    <option value="<?= $tipo ?>"><?= formatar_tipo_imovel($tipo) ?></option>
                <?php endforeach; ?>
            </select>
            <div id="resultado" style="margin-top: 15px; padding: 10px; background: white; border-radius: 5px; min-height: 20px;"></div>
        </div>

        <h3>📊 Arquivos Atualizados</h3>
        <ul>
            <li>✅ <code>private/imoveis/adicionar.php</code> - Formulário de cadastro</li>
            <li>✅ <code>private/imoveis/editar.php</code> - Formulário de edição</li>
            <li>✅ <code>private/imoveis/listar.php</code> - Lista de imóveis</li>
            <li>✅ <code>busca.php</code> - Página de busca</li>
            <li>✅ <code>index.php</code> - Página inicial</li>
            <li>✅ <code>imovel-detalhes.php</code> - Detalhes do imóvel</li>
            <li>✅ <code>teste-tipos-imoveis.php</code> - Arquivo de teste</li>
        </ul>

        <div class="success">
            <strong>Implementação Completa!</strong> Os novos tipos de imóveis estão prontos para uso em todo o sistema.
        </div>
    </div>

    <script>
        function mostrarSelecao(valor) {
            const resultado = document.getElementById('resultado');
            if (valor) {
                const texto = document.querySelector(`option[value="${valor}"]`).textContent;
                resultado.innerHTML = `<strong>Selecionado:</strong> ${texto} <span style="color: #28a745;">(Valor: ${valor})</span>`;
                resultado.style.background = '#e8f5e8';
                resultado.style.border = '1px solid #28a745';
            } else {
                resultado.innerHTML = '';
                resultado.style.background = 'white';
                resultado.style.border = 'none';
            }
        }
    </script>
</body>
</html>