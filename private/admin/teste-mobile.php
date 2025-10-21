<?php
/**
 * Teste Mobile - Página para testar funcionalidades mobile
 */

session_start();
require_once '../includes/init.php';
require_once '../config/config.php';

$page_title = 'Teste Mobile';
?>

<?php include '../includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-mobile-alt me-2"></i>
                        Teste de Responsividade Mobile
                    </h5>
                </div>
                <div class="card-body">
                    <p class="lead">Esta página serve para testar as funcionalidades mobile do painel administrativo.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Funcionalidades Testadas:</h6>
                            <ul>
                                <li>Menu lateral responsivo</li>
                                <li>Botão de toggle do sidebar</li>
                                <li>Overlay para fechar menu</li>
                                <li>Formulários otimizados para touch</li>
                                <li>Tabelas responsivas</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Teste de Formulário Mobile:</h6>
                            <form class="mobile-form">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título do Imóvel</label>
                                    <input type="text" class="form-control" id="titulo" placeholder="Ex: Casa 3 dormitórios">
                                </div>
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo</label>
                                    <select class="form-select" id="tipo">
                                        <option value="">Selecione o tipo</option>
                                        <option value="casa">Casa</option>
                                        <option value="apartamento">Apartamento</option>
                                        <option value="terreno">Terreno</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="valor" class="form-label">Valor</label>
                                    <input type="text" class="form-control money-mask" id="valor" placeholder="R$ 0,00">
                                </div>
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="descricao" rows="3" placeholder="Descrição do imóvel..."></textarea>
                                </div>
                                <button type="button" class="btn btn-primary btn-mobile">
                                    <i class="fas fa-save me-2"></i>
                                    Salvar
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6>Teste de Tabela Responsiva:</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Casa 3 dormitórios</td>
                                    <td>Casa</td>
                                    <td>R$ 450.000,00</td>
                                    <td><span class="badge bg-success">Ativo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Apartamento Centro</td>
                                    <td>Apartamento</td>
                                    <td>R$ 320.000,00</td>
                                    <td><span class="badge bg-warning">Vendido</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Instruções de Teste:</h6>
                            <ul class="mb-0">
                                <li><strong>Desktop:</strong> Redimensione a janela para menos de 992px para ver o menu mobile</li>
                                <li><strong>Mobile/Tablet:</strong> Use as ferramentas de desenvolvedor do navegador ou acesse de um dispositivo móvel</li>
                                <li><strong>Funcionalidades:</strong> Teste o menu lateral, preenchimento de formulários e visualização de tabelas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>