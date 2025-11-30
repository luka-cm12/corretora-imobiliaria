<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

$errors = [];
$success = '';

// CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

function normalizar_documento(string $valor): string {
    return preg_replace('/\D+/', '', $valor);
}

function cpf_valido(string $cpf): bool {
    if (empty(trim($cpf))) return false;
    
    // Validação: aceita 11 dígitos (com ou sem pontuação)
    $cpf = normalizar_documento($cpf);
    
    // Deve ter exatamente 11 dígitos
    if (strlen($cpf) !== 11) return false;
    
    // Recusa sequência de dígitos iguais (ex: 00000000000)
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    
    // Verifica se contém apenas números
    if (!ctype_digit($cpf)) return false;
    
    return true;
}

function cnpj_valido(string $cnpj): bool {
    if (empty(trim($cnpj))) return false;
    
    // Validação: aceita 14 dígitos (com ou sem pontuação)  
    $cnpj = normalizar_documento($cnpj);
    
    // Deve ter exatamente 14 dígitos
    if (strlen($cnpj) !== 14) return false;
    
    // Recusa sequência de dígitos iguais (ex: 00000000000000)
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
    
    // Verifica se contém apenas números
    if (!ctype_digit($cnpj)) return false;
    
    return true;
}

// Verificar se o ID foi fornecido
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: proprietarios-listar.php');
    exit;
}

// Buscar proprietário
try {
    $proprietario_result = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id]);
    if (empty($proprietario_result)) {
        header('Location: proprietarios-listar.php');
        exit;
    }
    $proprietario = $proprietario_result[0];
    
    // Garantir que campos essenciais existem
    $proprietario['nome'] = $proprietario['nome'] ?? '';
    $proprietario['cpf'] = $proprietario['cpf'] ?? '';
    $proprietario['cnpj'] = $proprietario['cnpj'] ?? '';
    $proprietario['tipo_documento'] = $proprietario['tipo_documento'] ?? 'cpf';
    $proprietario['telefone'] = $proprietario['telefone'] ?? '';
    $proprietario['email'] = $proprietario['email'] ?? '';
    $proprietario['endereco'] = $proprietario['endereco'] ?? '';
    
} catch (Exception $e) {
    header('Location: proprietarios-listar.php');
    exit;
}

// Buscar imóveis do proprietário
try {
    $imoveis = db_query("SELECT id_imovel, titulo, tipo, cidade, preco FROM imoveis WHERE id_proprietario = ? ORDER BY titulo", [$id]);
} catch (Exception $e) {
    $imoveis = []; // Se falhar, inicializa como array vazio
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $errors[] = 'Sessão expirada ou token inválido. Atualize a página e tente novamente.';
    }

    $nome = trim($_POST['nome'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento'] ?? 'cpf');
    $documento = trim($_POST['documento'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if ($nome === '') {
        $errors[] = 'O nome é obrigatório.';
    }

    // Validação do documento baseado no tipo escolhido
    if ($documento === '') {
        if ($tipo_documento === 'cpf') {
            $errors[] = 'O CPF é obrigatório.';
        } else {
            $errors[] = 'O CNPJ é obrigatório.';
        }
    } else {
        // Documento foi informado, validar formato
        if ($tipo_documento === 'cpf') {
            if (!cpf_valido($documento)) {
                $errors[] = 'Informe um CPF válido com 11 dígitos. Exemplo: 123.456.789-01';
            }
        } elseif ($tipo_documento === 'cnpj') {
            if (!cnpj_valido($documento)) {
                $errors[] = 'Informe um CNPJ válido com 14 dígitos. Exemplo: 12.345.678/0001-95';
            }
        }
    }

    // Validar email se informado
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }

    if (empty($errors)) {
        try {
            // Detecta a estrutura da tabela
            $columns_result = db_query("SHOW COLUMNS FROM proprietarios");
            $columns = [];
            foreach ($columns_result as $col) {
                $columns[] = $col['Field'];
            }
            
            $tem_tipo_documento = in_array('tipo_documento', $columns);
            $tem_cnpj = in_array('cnpj', $columns);
            
            // Verificar documento duplicado
            if ($documento !== '') {
                $doc_norm = normalizar_documento($documento);
                
                if ($tem_tipo_documento && $tem_cnpj) {
                    // Nova estrutura - verifica nas colunas corretas
                    if ($tipo_documento === 'cpf') {
                        $doc_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE cpf = ? AND id_proprietario != ? LIMIT 1", [$doc_norm, $id]);
                    } else {
                        $doc_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE cnpj = ? AND id_proprietario != ? LIMIT 1", [$doc_norm, $id]);
                    }
                } else {
                    // Estrutura antiga - verifica na coluna cpf unificada
                    $doc_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE cpf = ? AND id_proprietario != ? LIMIT 1", [$doc_norm, $id]);
                }
                
                if (!empty($doc_dup)) {
                    $tipo_doc_nome = $tipo_documento === 'cpf' ? 'CPF' : 'CNPJ';
                    $errors[] = "Já existe outro proprietário cadastrado com este {$tipo_doc_nome}.";
                }
            }

            // Verificar email duplicado
            if ($email !== '' && empty($errors)) {
                $email_dup = db_query("SELECT id_proprietario FROM proprietarios WHERE LOWER(email) = LOWER(?) AND id_proprietario != ? LIMIT 1", [$email, $id]);
                if (!empty($email_dup)) {
                    $errors[] = 'Este e-mail já está cadastrado para outro proprietário.';
                }
            }

            // Salva os dados se não há erros
            if (empty($errors)) {
                $doc_limpo = $documento ? normalizar_documento($documento) : null;
                
                if ($tem_tipo_documento && $tem_cnpj) {
                    // Nova estrutura com colunas separadas
                    if ($tipo_documento === 'cpf') {
                        $sql = "UPDATE proprietarios SET nome = ?, tipo_documento = 'cpf', cpf = ?, cnpj = NULL, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
                        $params = [$nome, $doc_limpo, $telefone ?: null, $email ?: null, $endereco ?: null, $id];
                    } else {
                        $sql = "UPDATE proprietarios SET nome = ?, tipo_documento = 'cnpj', cpf = NULL, cnpj = ?, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
                        $params = [$nome, $doc_limpo, $telefone ?: null, $email ?: null, $endereco ?: null, $id];
                    }
                } else {
                    // Estrutura antiga - usa coluna cpf para ambos
                    $sql = "UPDATE proprietarios SET nome = ?, cpf = ?, telefone = ?, email = ?, endereco = ? WHERE id_proprietario = ?";
                    $params = [$nome, $doc_limpo, $telefone ?: null, $email ?: null, $endereco ?: null, $id];
                }

                $result = db_query($sql, $params);

                if ($result !== false) {
                    $tipo_msg = ($tipo_documento === 'cpf') ? 'CPF' : 'CNPJ';
                    $doc_info = $documento ? " ({$tipo_msg}: {$documento})" : '';
                    $success = "✅ Proprietário '{$nome}' atualizado com sucesso!{$doc_info}";
                    
                    // Recarrega dados atualizados
                    $proprietario = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$id])[0];
                } else {
                    $errors[] = 'Erro ao atualizar proprietário.';
                }
            }
            
        } catch (Exception $e) {
            $errors[] = 'Erro no sistema: ' . $e->getMessage();
        }
    }
}

$page_title = 'Editar Proprietário | Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.proprietario-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.proprietario-info {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.info-item h4 {
    margin: 0 0 8px 0;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
}

.info-item p {
    margin: 0;
    color: #6c757d;
    font-size: 16px;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.imoveis-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.imoveis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.imovel-card {
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

.imovel-card h4 {
    margin: 0 0 10px 0;
    color: #495057;
}

.imovel-card p {
    margin: 5px 0;
    color: #6c757d;
    font-size: 14px;
}

.form-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-back {
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
}

.btn-back:hover {
    background: #5a6268;
    text-decoration: none;
    color: white;
}
</style>

<div class="breadcrumb">
    <a href="../admin/dashboard.php">Dashboard</a> / 
    <a href="proprietarios-listar.php">Proprietários</a> / 
    <span>Editar</span>
</div>

<div class="proprietario-header">
    <h1>Editar Proprietário</h1>
    <a href="proprietarios-listar.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Voltar para Lista
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul style="margin-left: 18px; margin-bottom: 0;">
            <?php foreach ($errors as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Informações atuais do proprietário -->
<div class="proprietario-info">
    <h3><i class="fas fa-user"></i> Informações Atuais</h3>
    
    <div class="info-grid">
        <div class="info-item">
            <h4>Nome Completo</h4>
            <p><?= htmlspecialchars($proprietario['nome']) ?></p>
        </div>
        
        <div class="info-item">
            <h4>Documento</h4>
            <p>
                <?php if (!empty($proprietario['cpf'])): ?>
                    <strong>CPF:</strong> <?= htmlspecialchars($proprietario['cpf']) ?>
                <?php elseif (!empty($proprietario['cnpj'])): ?>
                    <strong>CNPJ:</strong> <?= htmlspecialchars($proprietario['cnpj']) ?>
                <?php else: ?>
                    <span class="badge badge-warning">Não informado</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="info-item">
            <h4>Telefone</h4>
            <p>
                <?php if (!empty($proprietario['telefone'])): ?>
                    <?= htmlspecialchars($proprietario['telefone']) ?>
                <?php else: ?>
                    <span class="badge badge-warning">Não informado</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="info-item">
            <h4>Email</h4>
            <p>
                <?php if (!empty($proprietario['email'])): ?>
                    <?= htmlspecialchars($proprietario['email']) ?>
                <?php else: ?>
                    <span class="badge badge-warning">Não informado</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <?php if (!empty($proprietario['endereco'])): ?>
        <div class="info-item" style="margin-top: 20px;">
            <h4>Endereço</h4>
            <p><?= htmlspecialchars($proprietario['endereco']) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- Imóveis do proprietário -->
<div class="imoveis-section">
    <h3><i class="fas fa-home"></i> Imóveis Cadastrados (<?= count($imoveis) ?>)</h3>
    
    <?php if (empty($imoveis)): ?>
        <p style="color: #6c757d; text-align: center; margin: 20px 0;">
            Este proprietário ainda não possui imóveis cadastrados.
        </p>
    <?php else: ?>
        <div class="imoveis-grid">
            <?php foreach ($imoveis as $imovel): ?>
                <div class="imovel-card">
                    <h4><?= htmlspecialchars($imovel['titulo']) ?></h4>
                    <p><strong>Tipo:</strong> <?= ucfirst($imovel['tipo']) ?></p>
                    <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['cidade']) ?></p>
                    <p><strong>Preço:</strong> R$ <?= number_format($imovel['preco'], 2, ',', '.') ?></p>
                    <a href="editar.php?id=<?= $imovel['id_imovel'] ?>" style="color: #007bff; text-decoration: none;">
                        <i class="fas fa-edit"></i> Editar imóvel
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Formulário de edição -->
<div class="form-section">
    <h3><i class="fas fa-edit"></i> Editar Dados</h3>
    
    <form method="POST" class="imovel-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" 
                       name="nome" 
                       value="<?= htmlspecialchars($proprietario['nome']) ?>" 
                       required 
                       maxlength="150" 
                       autocomplete="name">
            </div>
            <div class="form-group">
                <label>Tipo de Documento</label>
                <?php
                // Detectar tipo de documento baseado na coluna tipo_documento ou documento existente
                $tipo_atual = $proprietario['tipo_documento'] ?? 'cpf';
                
                // Se não tem tipo_documento salvo, detectar pelo conteúdo
                if (empty($tipo_atual)) {
                    if (!empty($proprietario['cnpj'])) {
                        $tipo_atual = 'cnpj';
                    } elseif (!empty($proprietario['cpf'])) {
                        $doc_limpo = preg_replace('/\D/', '', $proprietario['cpf']);
                        $tipo_atual = (strlen($doc_limpo) >= 14) ? 'cnpj' : 'cpf';
                    } else {
                        $tipo_atual = 'cpf';
                    }
                }
                ?>
                <select name="tipo_documento" id="tipo_documento" onchange="alterarTipoDocumento()">
                    <option value="cpf" <?= $tipo_atual === 'cpf' ? 'selected' : '' ?>>CPF (Pessoa Física)</option>
                    <option value="cnpj" <?= $tipo_atual === 'cnpj' ? 'selected' : '' ?>>CNPJ (Pessoa Jurídica)</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label id="label_documento">Documento</label>
                <?php
                // Pegar o documento correto baseado no tipo
                $doc_atual = '';
                if ($tipo_atual === 'cnpj' && !empty($proprietario['cnpj'])) {
                    $doc_atual = $proprietario['cnpj'];
                } elseif ($tipo_atual === 'cpf' && !empty($proprietario['cpf'])) {
                    $doc_atual = $proprietario['cpf'];
                } elseif (!empty($proprietario['cpf'])) {
                    // Fallback para dados antigos
                    $doc_atual = $proprietario['cpf'];
                }
                ?>
                <input type="text" 
                       name="documento" 
                       id="documento"
                       value="<?= htmlspecialchars($doc_atual) ?>" 
                       placeholder="000.000.000-00" 
                       maxlength="18" 
                       required
                       title="Informe o documento válido">
                <div class="form-text" id="texto_documento">Somente números ou no formato apropriado (obrigatório)</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" 
                       name="telefone" 
                       value="<?= htmlspecialchars($proprietario['telefone']) ?>" 
                       placeholder="(99) 99999-9999" 
                       maxlength="20" 
                       autocomplete="tel">
            </div>
            <div class="form-group">
                <label>Email (opcional)</label>
                <input type="email" 
                       name="email" 
                       value="<?= htmlspecialchars($proprietario['email']) ?>" 
                       placeholder="email@exemplo.com" 
                       maxlength="150" 
                       autocomplete="email">
                <div class="form-text">Campo opcional - deixe em branco se não tiver email</div>
            </div>
        </div>

        <div class="form-group">
            <label>Endereço (opcional)</label>
            <textarea name="endereco" 
                      rows="3" 
                      maxlength="255" 
                      placeholder="Endereço completo do proprietário"><?= htmlspecialchars($proprietario['endereco']) ?></textarea>
            <div class="form-text">Campo opcional - informe apenas se necessário</div>
        </div>

        <div class="form-actions" style="display:flex; gap:15px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
            <a href="proprietarios-listar.php" class="btn-cancel">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
        
        <div class="form-text">Campos marcados com * são obrigatórios.</div>
    </form>
</div>

<script>
function alterarTipoDocumento() {
  const tipoSelect = document.getElementById('tipo_documento');
  const documentoInput = document.getElementById('documento');
  const labelDocumento = document.getElementById('label_documento');
  const textoDocumento = document.getElementById('texto_documento');
  
  if (tipoSelect.value === 'cpf') {
    labelDocumento.textContent = 'CPF *';
    documentoInput.placeholder = '000.000.000-00';
    documentoInput.maxLength = 14;
    textoDocumento.textContent = 'Somente números ou no formato 000.000.000-00 (obrigatório)';
  } else {
    labelDocumento.textContent = 'CNPJ *';
    documentoInput.placeholder = '00.000.000/0000-00';
    documentoInput.maxLength = 18;
    textoDocumento.textContent = 'Digite 14 números ou no formato 00.000.000/0000-00 (obrigatório)';
  }
}

// Executa quando a página carrega para definir o estado inicial
document.addEventListener('DOMContentLoaded', function() {
  alterarTipoDocumento();
});

// Máscaras para CPF e CNPJ
document.getElementById('documento').addEventListener('input', function(e) {
  const tipoSelect = document.getElementById('tipo_documento');
  let valor = e.target.value.replace(/\D/g, '');
  
  if (tipoSelect.value === 'cpf') {
    // Máscara CPF: 000.000.000-00
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  } else {
    // Máscara CNPJ: 00.000.000/0000-00
    valor = valor.replace(/(\d{2})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1/$2');
    valor = valor.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
  }
  
  e.target.value = valor;
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>