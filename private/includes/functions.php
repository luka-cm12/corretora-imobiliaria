<?php
/**
 * Funções úteis para o sistema
 */

/*use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;*/

/**
 * Formata o preço para exibição
 * 
 * @param float $preco Preço do imóvel
 * @return string Preço formatado
 */
function formatar_preco($preco) {
    return 'R$ ' . number_format($preco, 2, ',', '.');
}

/**
 * Redimensiona e faz upload de imagem
 * 
 * @param array $imagem Array $_FILES da imagem
 * @param string $pasta Pasta de destino relativa à raiz do projeto (ex: 'public/uploads')
 * @param int|null $largura Largura máxima opcional
 * @param int|null $altura Altura máxima opcional
 * @return string|bool URL da imagem ou false em caso de erro
 */
function upload_imagem($imagem, $pasta, $largura = null, $altura = null) {
    if ($imagem['error'] !== UPLOAD_ERR_OK) return false;

    // Detectar MIME real do arquivo
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
    $mime_real = $finfo ? finfo_file($finfo, $imagem['tmp_name']) : ($imagem['type'] ?? '');
    if ($finfo) { finfo_close($finfo); }

    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array(strtolower($mime_real), $tipos_permitidos)) return false;

    $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . '.' . strtolower($extensao);

    $caminho_pasta = __DIR__ . '/../../' . trim($pasta, '/') . '/';
    if (!is_dir($caminho_pasta)) mkdir($caminho_pasta, 0777, true);

    $caminho_completo = $caminho_pasta . $nome_arquivo;

    if (!move_uploaded_file($imagem['tmp_name'], $caminho_completo)) return false;

    // Redimensionar apenas se GD estiver ativo
    if ((function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng') || function_exists('imagecreatefromgif')) 
        && ($largura || $altura)) {
        redimensionar_imagem($caminho_completo, $largura, $altura);
    }

    // Retorna apenas o nome do arquivo (sem caminho) para consistência com o restante do sistema
    return $nome_arquivo;
}

/**
 * Redimensiona imagem mantendo proporções
 */
function redimensionar_imagem($caminho, $largura_max = null, $altura_max = null) {
    list($largura_orig, $altura_orig, $tipo) = getimagesize($caminho);
    $ratio = $largura_orig / $altura_orig;

    if ($largura_max && $altura_max) {
        if ($largura_orig > $largura_max || $altura_orig > $altura_max) {
            if ($largura_max / $altura_max > $ratio) {
                $largura_max = $altura_max * $ratio;
            } else {
                $altura_max = $largura_max / $ratio;
            }
        } else return true;
    } elseif ($largura_max) {
        $altura_max = $largura_max / $ratio;
    } elseif ($altura_max) {
        $largura_max = $altura_max * $ratio;
    } else return false;

    switch ($tipo) {
        case IMAGETYPE_JPEG: $origem = imagecreatefromjpeg($caminho); break;
        case IMAGETYPE_PNG:  $origem = imagecreatefrompng($caminho); break;
        case IMAGETYPE_GIF:  $origem = imagecreatefromgif($caminho); break;
        default: return false;
    }

    $destino = imagecreatetruecolor($largura_max, $altura_max);
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagecolortransparent($destino, imagecolorallocatealpha($destino, 0,0,0,127));
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
    }

    imagecopyresampled($destino, $origem, 0,0,0,0, $largura_max, $altura_max, $largura_orig, $altura_orig);

    switch ($tipo) {
        case IMAGETYPE_JPEG: imagejpeg($destino, $caminho, 90); break;
        case IMAGETYPE_PNG:  imagepng($destino, $caminho, 9); break;
        case IMAGETYPE_GIF:  imagegif($destino, $caminho); break;
    }

    imagedestroy($origem);
    imagedestroy($destino);

    return true;
}

/**
 * Gera breadcrumb dinâmico
 */
function breadcrumb() {
    $url = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($url, '/'));
    $breadcrumb = '<ul>';
    $breadcrumb .= '<li><a href="/">Home</a></li>';
    
    $path = '';
    foreach ($parts as $part) {
        $path .= '/' . $part;
        $page = str_replace(['.php', '-'], ['', ' '], $part);
        $page = ucfirst($page);
        
        if ($part != end($parts)) {
            $breadcrumb .= '<li><a href="' . $path . '">' . $page . '</a></li>';
        } else {
            $breadcrumb .= '<li>' . $page . '</li>';
        }
    }
    
    $breadcrumb .= '</ul>';
    return $breadcrumb;
}

/**
 * Envia email usando PHPMailer (opcional)
 */
function enviar_email($para, $assunto, $mensagem, $de_nome = 'Corretora Base', $de_email = 'contato@corretorabase.com.br') {
    require_once 'PHPMailer/PHPMailerAutoload.php';
    
    // Adiciona o namespace PHPMailer se necessário
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // Se estiver usando Composer, inclua o autoload
        if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
            require_once __DIR__ . '/../../vendor/autoload.php';
        }
    }
}

/**
 * Garante que uma coluna exista na tabela, criando-a se estiver ausente.
 * ATENÇÃO: Executa ALTER TABLE. Use com parcimônia.
 *
 * @param string $tabela Nome da tabela
 * @param string $coluna Nome da coluna a garantir
 * @param string $definicao Definição SQL da coluna (ex: 'TEXT NULL', 'CHAR(8) NULL')
 * @return bool true se a coluna existe/criada; false em falha (silenciosa em produção)
 */
function ensure_table_column($tabela, $coluna, $definicao) {
    try {
        $exists = db_query(
            "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$tabela, $coluna]
        );
        if (is_array($exists) && count($exists) > 0) return true;

        // Cria coluna
        $sql = "ALTER TABLE `{$tabela}` ADD COLUMN `{$coluna}` {$definicao}";
        $res = db_query($sql);
        return $res !== false;
    } catch (Throwable $e) {
        // Evita quebrar a aplicação caso o ambiente não permita ALTER TABLE
        if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT) {
            error_log('ensure_table_column falhou: ' . $e->getMessage());
        }
        return false;
    }
}