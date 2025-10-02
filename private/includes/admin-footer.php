<?php
/**
 * admin-footer.php
 * Rodapé da área administrativa
 * 
 * @version 1.1
 * @date 2023-11-20
 */

// Inclui o arquivo de configuração onde BASE_URL é definido
require_once __DIR__ . '/../config/config.php';
?>

        </div><!-- /.content -->
    </div><!-- /.main-content -->
</div><!-- /.admin-container -->

<footer class="admin-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <h5><?php echo NOME_CORRETORA; ?></h5>
                <p>
                    <?php 
                        echo (defined('ENDERECO_CORRETORA') ? constant('ENDERECO_CORRETORA') : 'Endereço não definido'); 
                    ?>
                </p>
                <p>Telefone: <?php echo isset($TELEFONE_CORRETORA) ? $TELEFONE_CORRETORA : (defined('TELEFONE_CORRETORA') ? constant('TELEFONE_CORRETORA') : 'Não definido'); ?></p>
                <p>Email: <?php echo (defined('EMAIL_CORRETORA') ? constant('EMAIL_CORRETORA') : (isset($EMAIL_CORRETORA) ? $EMAIL_CORRETORA : 'Email não definido')); ?></p>
            </div>
            
            <div class="col-md-4">
                <h5>Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>private/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>private/imoveis/listar.php">Gerenciar Imóveis</a></li>
                    <li><a href="<?php echo BASE_URL; ?>private/admin/lista_clientes.php">Gerenciar Clientes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>private/admin/configuracoes.php">Configurações</a></li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h5>Suporte</h5>
                <p>Em caso de problemas, entre em contato com o administrador do sistema.</p>
                <p>Versão do Sistema: <?php echo (defined('VERSAO_SISTEMA') ? constant('VERSAO_SISTEMA') : 'Versão não definida'); ?></p>
                <p>&copy; <?php echo date('Y'); ?> <?php echo NOME_CORRETORA; ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts essenciais -->
<script src="<?php echo BASE_URL; ?>public/assets/js/jquery-3.6.0.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin-scripts.js"></script>

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