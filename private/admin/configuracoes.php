<?php
/**
 * configuracoes.php
 * Configurações do sistema da corretora
 * 
 * @version 2.2
 * @date 2023-11-20
 */

// Verificação de segurança
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/../includes/funcoes_seguranca.php");

// Inclui arquivo com a função verificaPermissao, se necessário
if (!function_exists('verificaPermissao')) {
    require_once(__DIR__ . "/../includes/funcoes_permissoes.php");
 // ajuste o nome do arquivo conforme necessário
}

// Verifica se o usuário é administrador
require_once(__DIR__ . "/../includes/funcoes_login.php");
 // ajuste o nome do arquivo conforme necessário
verificaLogin();
verificaPermissao('admin');

// Conexão com o banco de dados
require_once 'conexao.php';
require_once 'funcoes_log.php'; // ajuste o nome do arquivo conforme necessário

// Variáveis para controle da interface
$pagina_atual = 'configuracoes';
$titulo_pagina = 'Configurações do Sistema';

// Mensagens de feedback
$mensagem = '';
$erro = '';

// Tipos de configurações disponíveis
$configuracoes_grupos = [
    'geral' => 'Configurações Gerais',
    'imoveis' => 'Configurações de Imóveis',
    'corretora' => 'Dados da Corretora',
    'seo' => 'Configurações SEO',
    'email' => 'Configurações de E-mail'
];

// Carrega todas as configurações atuais
$configuracoes = [];
$stmt = $conn->query("SELECT chave, valor, grupo FROM configuracoes");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $configuracoes[$row['grupo']][$row['chave']] = $row['valor'];
}

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    verificaCsrfToken();
    
    try {
        // Inicia transação
        $conn->beginTransaction();
        
        // Processa cada grupo de configurações
        foreach ($_POST['config'] as $grupo => $configs) {
            foreach ($configs as $chave => $valor) {
                // Prepara o valor (remove espaços, trata arrays, etc.)
                $valor = is_array($valor) ? implode(',', $valor) : trim($valor);
                
                // Verifica se a configuração já existe
                $stmt = $conn->prepare("SELECT COUNT(*) FROM configuracoes WHERE chave = ? AND grupo = ?");
                $stmt->execute([$chave, $grupo]);
                $existe = $stmt->fetchColumn();
                
                if ($existe) {
                    // Atualiza configuração existente
                    $stmt = $conn->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ? AND grupo = ?");
                    $stmt->execute([$valor, $chave, $grupo]);
                } else {
                    // Insere nova configuração
                    $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor, grupo) VALUES (?, ?, ?)");
                    $stmt->execute([$chave, $valor, $grupo]);
                }
            }
        }
        
        // Confirma as alterações
        $conn->commit();
        
        // Atualiza a lista de configurações
        $configuracoes = [];
        $stmt = $conn->query("SELECT chave, valor, grupo FROM configuracoes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $configuracoes[$row['grupo']][$row['chave']] = $row['valor'];
        }
        
        $mensagem = "Configurações atualizadas com sucesso!";
        registrarLog($_SESSION['usuario_id'], 'configuracoes', 'Atualizou as configurações do sistema');
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $erro = "Erro ao salvar configurações: " . $e->getMessage();
    }
}

// Inclui o cabeçalho
include 'private/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                <i class="fas fa-cog"></i> <?php echo $titulo_pagina; ?>
            </h1>
            
            <!-- Mensagens de feedback -->
            <?php if ($mensagem): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <!-- Abas de navegação -->
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <?php foreach ($configuracoes_grupos as $grupo => $titulo): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $grupo === 'geral' ? 'active' : ''; ?>" 
                                id="<?php echo $grupo; ?>-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#<?php echo $grupo; ?>" 
                                type="button" 
                                role="tab">
                            <?php echo $titulo; ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Formulário de configurações -->
            <form method="post" id="formConfiguracoes">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="tab-content card p-4" id="configTabsContent">
                    <!-- Tab Geral -->
                    <div class="tab-pane fade show active" id="geral" role="tabpanel">
                        <h4><i class="fas fa-sliders-h"></i> Configurações Gerais</h4>
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_geral_site_titulo" class="form-label">Título do Site</label>
                                    <input type="text" class="form-control" id="config_geral_site_titulo" 
                                           name="config[geral][site_titulo]" 
                                           value="<?php echo htmlspecialchars($configuracoes['geral']['site_titulo'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_geral_site_status" class="form-label">Status do Site</label>
                                    <select class="form-select" id="config_geral_site_status" name="config[geral][site_status]">
                                        <option value="online" <?php echo ($configuracoes['geral']['site_status'] ?? '') == 'online' ? 'selected' : ''; ?>>Online</option>
                                        <option value="manutencao" <?php echo ($configuracoes['geral']['site_status'] ?? '') == 'manutencao' ? 'selected' : ''; ?>>Em Manutenção</option>
                                        <option value="offline" <?php echo ($configuracoes['geral']['site_status'] ?? '') == 'offline' ? 'selected' : ''; ?>>Offline</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_geral_paginacao" class="form-label">Itens por Página</label>
                                    <input type="number" class="form-control" id="config_geral_paginacao" 
                                           name="config[geral][paginacao]" 
                                           value="<?php echo htmlspecialchars($configuracoes['geral']['paginacao'] ?? '10'); ?>" 
                                           min="5" max="100">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="config_geral_manutencao_login" 
                                           name="config[geral][manutencao_login]" 
                                           value="1" <?php echo ($configuracoes['geral']['manutencao_login'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="config_geral_manutencao_login">Permitir login durante manutenção</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Imóveis -->
                    <div class="tab-pane fade" id="imoveis" role="tabpanel">
                        <h4><i class="fas fa-home"></i> Configurações de Imóveis</h4>
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_imoveis_destaque_dias" class="form-label">Dias em Destaque</label>
                                    <input type="number" class="form-control" id="config_imoveis_destaque_dias" 
                                           name="config[imoveis][destaque_dias]" 
                                           value="<?php echo htmlspecialchars($configuracoes['imoveis']['destaque_dias'] ?? '7'); ?>" 
                                           min="1" max="30">
                                    <small class="text-muted">Número de dias que um imóvel fica em destaque</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_imoveis_fotos_max" class="form-label">Máximo de Fotos</label>
                                    <input type="number" class="form-control" id="config_imoveis_fotos_max" 
                                           name="config[imoveis][fotos_max]" 
                                           value="<?php echo htmlspecialchars($configuracoes['imoveis']['fotos_max'] ?? '20'); ?>" 
                                           min="1" max="50">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Recursos Disponíveis</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="config_imoveis_recurso_piscina" 
                                               name="config[imoveis][recursos][]" 
                                               value="piscina" <?php echo in_array('piscina', explode(',', $configuracoes['imoveis']['recursos'] ?? '')) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="config_imoveis_recurso_piscina">Piscina</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="config_imoveis_recurso_garagem" 
                                               name="config[imoveis][recursos][]" 
                                               value="garagem" <?php echo in_array('garagem', explode(',', $configuracoes['imoveis']['recursos'] ?? '')) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="config_imoveis_recurso_garagem">Garagem</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="config_imoveis_recurso_academia" 
                                               name="config[imoveis][recursos][]" 
                                               value="academia" <?php echo in_array('academia', explode(',', $configuracoes['imoveis']['recursos'] ?? '')) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="config_imoveis_recurso_academia">Academia</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Corretora -->
                    <div class="tab-pane fade" id="corretora" role="tabpanel">
                        <h4><i class="fas fa-building"></i> Dados da Corretora</h4>
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_corretora_nome" class="form-label">Nome da Corretora</label>
                                    <input type="text" class="form-control" id="config_corretora_nome" 
                                           name="config[corretora][nome]" 
                                           value="<?php echo htmlspecialchars($configuracoes['corretora']['nome'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_corretora_endereco" class="form-label">Endereço</label>
                                    <input type="text" class="form-control" id="config_corretora_endereco" 
                                           name="config[corretora][endereco]" 
                                           value="<?php echo htmlspecialchars($configuracoes['corretora']['endereco'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_corretora_telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="config_corretora_telefone" 
                                           name="config[corretora][telefone]" 
                                           value="<?php echo htmlspecialchars($configuracoes['corretora']['telefone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_corretora_email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="config_corretora_email" 
                                           name="config[corretora][email]" 
                                           value="<?php echo htmlspecialchars($configuracoes['corretora']['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_corretora_cnpj" class="form-label">CNPJ</label>
                                    <input type="text" class="form-control" id="config_corretora_cnpj" 
                                           name="config[corretora][cnpj]" 
                                           value="<?php echo htmlspecialchars($configuracoes['corretora']['cnpj'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_corretora_creci" class="form-label">CRECI</label>
                                    <input type="text" class="form-control" id="config_corretora_creci" 
                                           name="config[corretora][creci]" 
                                           value="<?php echo htmlspecialchars($configuracoes['corretora']['creci'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab SEO -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <h4><i class="fas fa-search"></i> Configurações SEO</h4>
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_seo_meta_desc" class="form-label">Meta Descrição</label>
                                    <textarea class="form-control" id="config_seo_meta_desc" 
                                              name="config[seo][meta_desc]" 
                                              rows="3"><?php echo htmlspecialchars($configuracoes['seo']['meta_desc'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_seo_keywords" class="form-label">Palavras-chave</label>
                                    <input type="text" class="form-control" id="config_seo_keywords" 
                                           name="config[seo][keywords]" 
                                           value="<?php echo htmlspecialchars($configuracoes['seo']['keywords'] ?? ''); ?>">
                                    <small class="text-muted">Separadas por vírgula</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_seo_google_analytics" class="form-label">Google Analytics</label>
                                    <textarea class="form-control" id="config_seo_google_analytics" 
                                              name="config[seo][google_analytics]" 
                                              rows="3"><?php echo htmlspecialchars($configuracoes['seo']['google_analytics'] ?? ''); ?></textarea>
                                    <small class="text-muted">Código de acompanhamento (incluindo as tags &lt;script&gt;)</small>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="config_seo_sitemap" 
                                           name="config[seo][sitemap]" 
                                           value="1" <?php echo ($configuracoes['seo']['sitemap'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="config_seo_sitemap">Gerar sitemap automaticamente</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab E-mail -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <h4><i class="fas fa-envelope"></i> Configurações de E-mail</h4>
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_email_smtp_host" class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="config_email_smtp_host" 
                                           name="config[email][smtp_host]" 
                                           value="<?php echo htmlspecialchars($configuracoes['email']['smtp_host'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_email_smtp_porta" class="form-label">SMTP Porta</label>
                                    <input type="number" class="form-control" id="config_email_smtp_porta" 
                                           name="config[email][smtp_porta]" 
                                           value="<?php echo htmlspecialchars($configuracoes['email']['smtp_porta'] ?? '587'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_email_smtp_usuario" class="form-label">SMTP Usuário</label>
                                    <input type="text" class="form-control" id="config_email_smtp_usuario" 
                                           name="config[email][smtp_usuario]" 
                                           value="<?php echo htmlspecialchars($configuracoes['email']['smtp_usuario'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="config_email_smtp_senha" class="form-label">SMTP Senha</label>
                                    <input type="password" class="form-control" id="config_email_smtp_senha" 
                                           name="config[email][smtp_senha]" 
                                           value="<?php echo htmlspecialchars($configuracoes['email']['smtp_senha'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="config_email_remetente" class="form-label">E-mail Remetente</label>
                                    <input type="email" class="form-control" id="config_email_remetente" 
                                           name="config[email][remetente]" 
                                           value="<?php echo htmlspecialchars($configuracoes['email']['remetente'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="config_email_smtp_ssl" 
                                           name="config[email][smtp_ssl]" 
                                           value="1" <?php echo ($configuracoes['email']['smtp_ssl'] ?? '') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="config_email_smtp_ssl">Usar SSL/TLS</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'private/includes/admin-footer.php'; ?>

<!-- Scripts específicos para esta página -->
<script>
$(document).ready(function() {
    // Validação do formulário
    $('#formConfiguracoes').submit(function() {
        // Validações adicionais podem ser adicionadas aqui
        return true;
    });
    
    // Máscaras para campos específicos
    $('#config_corretora_telefone').mask('(00) 00000-0000');
    $('#config_corretora_cnpj').mask('00.000.000/0000-00');
});
</script>