<?php
/**
 * Funções úteis para o sistema
 */

use PHPMailer\PHPMailer\PHPMailer;

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
 * @param string $pasta Pasta de destino
 * @param int $largura Largura máxima (opcional)
 * @param int $altura Altura máxima (opcional)
 * @return string|bool Nome do arquivo ou false em caso de erro
 */
function upload_imagem($imagem, $pasta, $largura = null, $altura = null) {
    // Verificar erros no upload
    if ($imagem['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Verificar tipo de arquivo
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($imagem['type'], $tipos_permitidos)) {
        return false;
    }
    
    // Criar nome único para o arquivo
    $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . '.' . strtolower($extensao);
    $caminho_completo = $pasta . $nome_arquivo;
    
    // Mover arquivo temporário
    if (!move_uploaded_file($imagem['tmp_name'], $caminho_completo)) {
        return false;
    }
    
    // Redimensionar imagem se necessário
    if ($largura || $altura) {
        redimensionar_imagem($caminho_completo, $largura, $altura);
    }
    
    return $nome_arquivo;
}

/**
 * Redimensiona uma imagem mantendo proporções
 */
function redimensionar_imagem($caminho, $largura_max = null, $altura_max = null) {
    // Obter informações da imagem
    list($largura_orig, $altura_orig, $tipo) = getimagesize($caminho);
    
    // Calcular novas dimensões mantendo proporção
    $ratio = $largura_orig / $altura_orig;
    
    if ($largura_max && $altura_max) {
        if ($largura_orig > $largura_max || $altura_orig > $altura_max) {
            if ($largura_max / $altura_max > $ratio) {
                $largura_max = $altura_max * $ratio;
            } else {
                $altura_max = $largura_max / $ratio;
            }
        } else {
            // Não redimensionar se já for menor
            return true;
        }
    } elseif ($largura_max) {
        $altura_max = $largura_max / $ratio;
    } elseif ($altura_max) {
        $largura_max = $altura_max * $ratio;
    } else {
        return false;
    }
    
    // Criar imagem temporária
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $origem = imagecreatefromjpeg($caminho);
            break;
        case IMAGETYPE_PNG:
            $origem = imagecreatefrompng($caminho);
            break;
        case IMAGETYPE_GIF:
            $origem = imagecreatefromgif($caminho);
            break;
        default:
            return false;
    }
    
    $destino = imagecreatetruecolor($largura_max, $altura_max);
    
    // Preservar transparência PNG/GIF
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagecolortransparent($destino, imagecolorallocatealpha($destino, 0, 0, 0, 127));
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
    }
    
    // Redimensionar
    imagecopyresampled($destino, $origem, 0, 0, 0, 0, $largura_max, $altura_max, $largura_orig, $altura_orig);
    
    // Salvar imagem
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($destino, $caminho, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destino, $caminho, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destino, $caminho);
            break;
    }
    
    // Liberar memória
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

    // Usa o namespace PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.seuservidor.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email@corretorabase.com.br';
    $mail->Password = 'sua_senha';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom($de_email, $de_nome);
    $mail->addAddress($para);
    $mail->isHTML(true);
    
    $mail->Subject = $assunto;
    $mail->Body    = $mensagem;
    
    return $mail->send();
}