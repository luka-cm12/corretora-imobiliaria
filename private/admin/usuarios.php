<?php
/**
 * usuarios.php
 * Gerenciamento de usuários do sistema
 * 
 * @version 2.1
 * @date 2023-11-20
 */

// Verificação de segurança
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/../includes/funcoes_seguranca.php");

// Define verificaCsrfToken se não existir
if (!function_exists('verificaCsrfToken')) {
    function verificaCsrfToken() {
        if (!isset($_POST['csrf_token']) && !isset($_GET['csrf_token'])) {
            header('Location: acesso-negado.php');
            exit;
        }
        $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : $_GET['csrf_token'];
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            header('Location: acesso-negado.php');
            exit;
        }
    }
}

// Define verificaLogin se não existir
if (!function_exists('verificaLogin')) {
    function verificaLogin() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}

// Define verificaPermissao se não existir
if (!function_exists('verificaPermissao')) {
    function verificaPermissao($perfil) {
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== $perfil) {
            header('Location: acesso-negado.php');
            exit;
        }
    }
}

// Verifica se o usuário é administrador
verificaLogin();
verificaPermissao('admin');

// Helpers de log
require_once __DIR__ . '/../includes/log_acoes.php';

// Garante que exista um token CSRF na sessão
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Variáveis para controle da interface
$pagina_atual = 'usuarios';
$titulo_pagina = 'Gerenciamento de Usuários';
$page_title = $titulo_pagina;

// Processamento de formulários
$mensagem = '';
$erro = '';

// Ação: Adicionar/Editar usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    verificaCsrfToken();
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $perfil = $_POST['perfil'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    try {
        // Validação dos dados
        if (empty($nome) || empty($email) || empty($perfil)) {
            throw new Exception("Preencha todos os campos obrigatórios!");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("E-mail inválido!");
        }
        
        // Verifica se e-mail já existe (exceto para o próprio usuário em edição)
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        
        if ($stmt->fetch()) {
            throw new Exception("Este e-mail já está cadastrado!");
        }
        
        // Se for um novo usuário ou alteração de senha
        $alterar_senha = !empty($_POST['senha']);
        
        if ($id > 0) {
            // Atualização de usuário existente
            if ($alterar_senha) {
                $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ?, senha = ?, status = ?, atualizado_em = NOW() WHERE id = ?");
                $stmt->execute([$nome, $email, $perfil, $senha_hash, $status, $id]);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ?, status = ?, atualizado_em = NOW() WHERE id = ?");
                $stmt->execute([$nome, $email, $perfil, $status, $id]);
            }
            
            $mensagem = "Usuário atualizado com sucesso!";
        } else {
            // Cadastro de novo usuário
            if (empty($_POST['senha'])) {
                throw new Exception("Para novo usuário, é necessário definir uma senha!");
            }
            
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, perfil, status, criado_em) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nome, $email, $senha_hash, $perfil, $status]);
            
            $id = $conn->lastInsertId();
            $mensagem = "Usuário cadastrado com sucesso!";
        }
        
    // Registra a ação no log
    registrarLog($conn, $_SESSION['usuario_id'], 'usuarios', ($id > 0 ? 'Atualizou' : 'Cadastrou') . " o usuário ID: $id");
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Ação: Excluir usuário
if (isset($_GET['excluir'])) {
    verificaCsrfToken();
    
    $id_excluir = (int)$_GET['excluir'];
    
    // Não permite excluir o próprio usuário
    if ($id_excluir == $_SESSION['usuario_id']) {
        $erro = "Você não pode excluir seu próprio usuário!";
    } else {
        try {
            // Verifica dinamicamente se existe alguma coluna em 'imoveis' que relacione com usuários
            $possiveisColunas = ['usuario_id', 'id_usuario', 'criado_por', 'created_by', 'owner_user_id'];
            $placeholders = implode(',', array_fill(0, count($possiveisColunas), '?'));
            $stmt = $conn->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'imoveis' AND COLUMN_NAME IN ($placeholders) LIMIT 1");
            $stmt->execute($possiveisColunas);
            $colunaUsuario = $stmt->fetchColumn();

            $bloquearExclusao = false;
            if ($colunaUsuario && in_array($colunaUsuario, $possiveisColunas, true)) {
                // Faz a checagem apenas se a coluna existir
                $q = $conn->query("SELECT COUNT(*) FROM imoveis LIMIT 1"); // força erro cedo se tabela não existe
                $stmt = $conn->prepare("SELECT COUNT(*) FROM imoveis WHERE $colunaUsuario = ?");
                $stmt->execute([$id_excluir]);
                $total_imoveis = (int)$stmt->fetchColumn();
                $bloquearExclusao = $total_imoveis > 0;
            }

            if ($bloquearExclusao) {
                $erro = "Este usuário possui imóveis cadastrados e não pode ser excluído!";
            } else {
                // Exclui o usuário
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id_excluir]);

                $mensagem = "Usuário excluído com sucesso!";
                registrarLog($conn, $_SESSION['usuario_id'], 'usuarios', "Excluiu o usuário ID: $id_excluir");
            }
        } catch (PDOException $e) {
            $erro = "Erro ao excluir usuário: " . $e->getMessage();
        }
    }
}

// Listagem de usuários
$query = "SELECT id, nome, email, perfil, status, DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') as criado_em 
          FROM usuarios 
          ORDER BY nome ASC";
$usuarios = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Inclui o cabeçalho
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                <i class="fas fa-user-shield"></i> <?php echo $titulo_pagina; ?>
                <button class="btn btn-primary pull-right" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="fas fa-plus"></i> Novo Usuário
                </button>
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
            
            <!-- Tabela de usuários -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabelaUsuarios">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Cadastrado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario['perfil'] == 'admin' ? 'primary' : 'info'; ?>">
                                                <?php echo ucfirst($usuario['perfil']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario['status'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $usuario['status'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $usuario['criado_em']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning btn-editar" 
                                                    data-id="<?php echo $usuario['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($usuario['nome']); ?>"
                                                    data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                                    data-perfil="<?php echo $usuario['perfil']; ?>"
                                                    data-status="<?php echo $usuario['status']; ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            
                                            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                                <a href="?excluir=<?php echo $usuario['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                                   class="btn btn-sm btn-danger btn-excluir">
                                                    <i class="fas fa-trash-alt"></i> Excluir
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para adicionar/editar usuário -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formUsuario">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioTitulo">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="usuarioId" value="0">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil *</label>
                        <select class="form-select" id="perfil" name="perfil" required>
                            <option value="admin">Administrador</option>
                            <option value="corretor">Corretor</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha <span id="senhaObrigatoria">*</span></label>
                        <input type="password" class="form-control" id="senha" name="senha">
                        <small class="text-muted">Deixe em branco para manter a senha atual (em edição)</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="status" name="status" value="1" checked>
                        <label class="form-check-label" for="status">Usuário ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

<!-- Scripts específicos para esta página -->
<script>
$(document).ready(function() {
    // DataTable para a tabela de usuários
    $('#tabelaUsuarios').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
        },
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });
    
    // Edição de usuário
    $('.btn-editar').click(function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        var email = $(this).data('email');
        var perfil = $(this).data('perfil');
        var status = $(this).data('status');
        
        $('#usuarioId').val(id);
        $('#nome').val(nome);
        $('#email').val(email);
        $('#perfil').val(perfil);
        $('#status').prop('checked', status == 1);
        
        $('#modalUsuarioTitulo').text('Editar Usuário');
        $('#senhaObrigatoria').hide();
        
        $('#modalUsuario').modal('show');
    });
    
    // Novo usuário
    $('[data-bs-target="#modalUsuario"]').click(function() {
        $('#formUsuario')[0].reset();
        $('#usuarioId').val(0);
        $('#modalUsuarioTitulo').text('Novo Usuário');
        $('#senhaObrigatoria').show();
    });
    
    // Confirmação de exclusão
    $('.btn-excluir').click(function(e) {
        if (!confirm('Tem certeza que deseja excluir este usuário?')) {
            e.preventDefault();
        }
    });
});
</script>