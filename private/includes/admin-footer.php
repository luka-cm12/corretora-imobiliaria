<?php
/**
 * admin-footer.php
 * Rodapé da área administrativa
 * 
 * @version 1.1
 * @date 2023-11-20
 */
?>

<footer class="admin-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <h5><?php echo NOME_CORRETORA; ?></h5>
                <p><?php echo ENDERECO_CORRETORA; ?></p>
                <p>Telefone: <?php echo TELEFONE_CORRETORA; ?></p>
                <p>Email: <?php echo EMAIL_CORRETORA; ?></p>
            </div>
            
            <div class="col-md-4">
                <h5>Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/lista_imoveis.php">Gerenciar Imóveis</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/lista_clientes.php">Gerenciar Clientes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/relatorios.php">Relatórios</a></li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h5>Suporte</h5>
                <p>Em caso de problemas, entre em contato com o administrador do sistema.</p>
                <p>Versão do Sistema: <?php echo VERSAO_SISTEMA; ?></p>
                <p>&copy; <?php echo date('Y'); ?> <?php echo NOME_CORRETORA; ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts essenciais -->
<script src="<?php echo BASE_URL; ?>assets/js/jquery-3.6.0.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/admin-scripts.js"></script>

<!-- Modal de logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja sair do sistema?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="<?php echo BASE_URL; ?>admin/logout.php" class="btn btn-primary">Sair</a>
            </div>
        </div>
    </div>
</div>

<?php
// Exibe mensagens de sessão (sucesso/erro)
if (isset($_SESSION['mensagem'])) {
    $tipo = isset($_SESSION['tipo_mensagem']) ? $_SESSION['tipo_mensagem'] : 'info';
    echo "<script>
            $(document).ready(function() {
                toastr.".$tipo."('".addslashes($_SESSION['mensagem'])."');
            });
          </script>";
    
    // Limpa a mensagem após exibir
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

</body>
</html>