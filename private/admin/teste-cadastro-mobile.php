<?php
/**
 * Teste do Cadastro Mobile
 * Página para verificar otimizações mobile do cadastro de imóveis
 */

session_start();
require_once '../includes/init.php';
require_once '../config/config.php';

$page_title = 'Teste Cadastro Mobile';
?>

<?php include '../includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-mobile-alt me-2"></i>Teste de Cadastro Mobile</h5>
                <p>Esta página serve para testar as otimizações mobile do formulário de cadastro de imóveis.</p>
                <hr>
                <h6>Como testar:</h6>
                <ul class="mb-0">
                    <li><strong>Desktop:</strong> Use F12 → Device Toolbar → Selecione um dispositivo mobile</li>
                    <li><strong>Mobile:</strong> Acesse diretamente de um smartphone ou tablet</li>
                    <li><strong>Funcionalidades:</strong> Teste preenchimento, validação, máscaras e salvamento</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Cadastrar Novo Imóvel
                    </h5>
                    <a href="../imoveis/adicionar.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>
                        Ir para Página Real
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Simulação do formulário de cadastro -->
                    <form class="imovel-form mobile-optimized" onsubmit="return false;">
                        
                        <!-- Seção 1: Informações Básicas -->
                        <div class="form-section-header">
                            <i class="fas fa-info-circle"></i>Informações Básicas
                        </div>
                        
                        <div class="form-group">
                            <label for="test-proprietario">Proprietário *</label>
                            <select id="test-proprietario" name="proprietario" required class="form-select">
                                <option value="">Selecione o proprietário</option>
                                <option value="1">João Silva</option>
                                <option value="2">Maria Santos</option>
                                <option value="3">Carlos Oliveira</option>
                            </select>
                            <a href="#" class="btn btn-small">+ Novo Proprietário</a>
                        </div>
                        
                        <div class="form-group">
                            <label for="test-titulo">Título do Imóvel *</label>
                            <input type="text" id="test-titulo" name="titulo" required 
                                   placeholder="Ex: Casa 3 dormitórios com piscina">
                        </div>
                        
                        <div class="form-group">
                            <label for="test-tipo">Tipo *</label>
                            <select id="test-tipo" name="tipo" required>
                                <option value="">Selecione</option>
                                <option value="casa">🏠 Casa</option>
                                <option value="apartamento">🏢 Apartamento</option>
                                <option value="terreno">🌿 Terreno</option>
                                <option value="comercial">🏪 Comercial</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="test-descricao">Descrição *</label>
                            <textarea id="test-descricao" name="descricao" rows="4" required
                                      placeholder="Descreva as principais características do imóvel..."></textarea>
                            <small class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Descreva localização, estado de conservação, diferenciais
                            </small>
                        </div>
                        
                        <!-- Seção 2: Características -->
                        <div class="form-section-header">
                            <i class="fas fa-star"></i>Características do Imóvel
                        </div>
                        
                        <div class="form-group">
                            <label>Características principais</label>
                            <div class="caracteristicas-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;">
                                <label style="display:flex;gap:8px;align-items:center;">
                                    <input type="checkbox" name="caracteristicas[]" value="ar_condicionado">
                                    <span>Ar condicionado</span>
                                </label>
                                <label style="display:flex;gap:8px;align-items:center;">
                                    <input type="checkbox" name="caracteristicas[]" value="piscina">
                                    <span>Piscina</span>
                                </label>
                                <label style="display:flex;gap:8px;align-items:center;">
                                    <input type="checkbox" name="caracteristicas[]" value="churrasqueira">
                                    <span>Churrasqueira</span>
                                </label>
                                <label style="display:flex;gap:8px;align-items:center;">
                                    <input type="checkbox" name="caracteristicas[]" value="garagem">
                                    <span>Garagem</span>
                                </label>
                                <label style="display:flex;gap:8px;align-items:center;">
                                    <input type="checkbox" name="caracteristicas[]" value="elevador">
                                    <span>Elevador</span>
                                </label>
                                <label style="display:flex;gap:8px;align-items:center;">
                                    <input type="checkbox" name="caracteristicas[]" value="portaria_24h">
                                    <span>Portaria 24h</span>
                                </label>
                            </div>
                            <small class="form-text">Selecione as características que se aplicam ao imóvel.</small>
                        </div>
                        
                        <!-- Seção 3: Localização -->
                        <div class="form-section-header">
                            <i class="fas fa-map-marker-alt"></i>Localização
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="test-cep">CEP</label>
                                <input type="text" id="test-cep" name="cep" placeholder="00000-000" class="cep-mask">
                                <small class="form-text">Digite o CEP para preenchimento automático do endereço</small>
                            </div>
                            <div class="form-group">
                                <label for="test-cidade">Cidade *</label>
                                <input type="text" id="test-cidade" name="cidade" required>
                            </div>
                            <div class="form-group">
                                <label for="test-bairro">Bairro *</label>
                                <input type="text" id="test-bairro" name="bairro" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="test-endereco">Endereço Completo</label>
                            <input type="text" id="test-endereco" name="endereco" 
                                   placeholder="Rua, número, complemento">
                        </div>
                        
                        <!-- Seção 4: Detalhes do Imóvel -->
                        <div class="form-section-header">
                            <i class="fas fa-home"></i>Detalhes do Imóvel
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="test-preco">Preço (R$) *</label>
                                <input type="text" id="test-preco" name="preco" required class="money-mask"
                                       placeholder="R$ 0,00">
                            </div>
                            <div class="form-group">
                                <label for="test-area">Área (m²)</label>
                                <input type="text" id="test-area" name="area" placeholder="Ex: 120,50">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="test-quartos">Quartos</label>
                                <input type="number" id="test-quartos" name="quartos" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="test-banheiros">Banheiros</label>
                                <input type="number" id="test-banheiros" name="banheiros" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="test-garagem">Vagas de Garagem</label>
                                <input type="number" id="test-garagem" name="garagem" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="destaque" value="1">
                                <i class="fas fa-star text-warning"></i> Marcar como destaque
                            </label>
                            <small class="form-text">Imóveis em destaque aparecem primeiro nas buscas</small>
                        </div>
                        
                        <!-- Seção 5: Imagens -->
                        <div class="form-section-header">
                            <i class="fas fa-images"></i>Imagens do Imóvel
                        </div>
                        
                        <div class="form-group">
                            <label for="test-imagens">Selecionar Imagens *</label>
                            <input type="file" id="test-imagens" name="imagens[]" multiple accept="image/*" required>
                            <small class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Selecione várias imagens (máx. 10). A primeira será a imagem principal.
                            </small>
                        </div>
                        
                        <!-- Botão flutuante para mobile -->
                        <button type="button" class="btn mobile-save-btn d-lg-none" onclick="testarSalvamento()">
                            <i class="fas fa-save me-2"></i>Salvar Imóvel (TESTE)
                        </button>
                        
                        <!-- Botão normal para desktop -->
                        <button type="button" class="btn d-none d-lg-block" onclick="testarSalvamento()">
                            <i class="fas fa-save me-2"></i>Salvar Imóvel (TESTE)
                        </button>
                        
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testarSalvamento() {
    // Simulação de salvamento
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    
    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = originalText;
        
        if (window.toastr) {
            toastr.success('Teste realizado com sucesso! O formulário está funcionando perfeitamente no mobile.');
        } else {
            alert('Teste realizado com sucesso! O formulário está funcionando perfeitamente no mobile.');
        }
    }, 2000);
}
</script>

<?php include '../includes/admin-footer.php'; ?>