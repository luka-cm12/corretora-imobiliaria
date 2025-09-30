<?php
// filepath: processa-contato.php

require_once __DIR__ . '/private/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function log_antispam($message) {
    $line = sprintf("[%s] %s | IP: %s | UA: %s\n", date('Y-m-d H:i:s'), $message, $_SERVER['REMOTE_ADDR'] ?? '-', $_SERVER['HTTP_USER_AGENT'] ?? '-');
    $logFile = __DIR__ . '/private/logs/antispam.log';
    // Garantir diretório
    $dir = dirname($logFile);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    @file_put_contents($logFile, $line, FILE_APPEND);
}

// Permitir somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['contato_erro'] = 'Método inválido.';
    log_antispam('Tentativa com método inválido');
    $back = isset($_GET['id']) ? 'imovel-detalhes.php?id=' . urlencode($_GET['id']) : 'contato.php';
    header('Location: ' . $back);
    exit;
}

// CSRF protection
$csrf = $_POST['csrf_token'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    $_SESSION['contato_erro'] = 'Falha de segurança. Atualize a página e tente novamente.';
    log_antispam('Falha CSRF');
    $back = isset($_POST['imovel_id']) ? 'imovel-detalhes.php?id=' . urlencode($_POST['imovel_id']) : 'contato.php';
    header('Location: ' . $back);
    exit;
}

// Honeypot (campo invisível para humanos)
$hp = trim($_POST['website'] ?? '');
if (!empty($hp)) {
    $_SESSION['contato_erro'] = 'Solicitação bloqueada.';
    log_antispam('Honeypot preenchido');
    $back = isset($_POST['imovel_id']) ? 'imovel-detalhes.php?id=' . urlencode($_POST['imovel_id']) : 'contato.php';
    header('Location: ' . $back);
    exit;
}

// Rate limiting simples por sessão (intervalo mínimo de 20s)
$now = time();
if (isset($_SESSION['last_contact_submit']) && ($now - (int)$_SESSION['last_contact_submit']) < 20) {
    $_SESSION['contato_erro'] = 'Aguarde alguns segundos antes de enviar novamente.';
    log_antispam('Rate limit atingido');
    $back = isset($_POST['imovel_id']) ? 'imovel-detalhes.php?id=' . urlencode($_POST['imovel_id']) : 'contato.php';
    header('Location: ' . $back);
    exit;
}

$imovel_id     = $_POST['imovel_id'] ?? '';
$imovel_titulo = $_POST['imovel_titulo'] ?? '';
$nome          = trim($_POST['nome'] ?? '');
$email         = trim($_POST['email'] ?? '');
$telefone      = trim($_POST['telefone'] ?? '');
$assunto       = trim($_POST['assunto'] ?? '');
$mensagem      = trim($_POST['mensagem'] ?? '');

$erros = [];
if (!$nome)      $erros[] = 'Nome é obrigatório.';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if (!$mensagem)  $erros[] = 'Mensagem é obrigatória.';

if ($erros) {
    $_SESSION['contato_erro'] = implode('<br>', $erros);
    header('Location: ' . (isset($_POST['imovel_id']) ? 'imovel-detalhes.php?id=' . urlencode($imovel_id) : 'contato.php'));
    exit;
}

// Salva no banco de dados (apenas após validações)
$sql = "INSERT INTO contatos (imovel_id, imovel_titulo, nome, email, telefone, assunto, mensagem, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$result = db_query($sql, [$imovel_id, $imovel_titulo, $nome, $email, $telefone, $assunto, $mensagem]);

if ($result) {
    $_SESSION['contato_sucesso'] = 'Mensagem enviada com sucesso!';
    $_SESSION['last_contact_submit'] = $now;
} else {
    $_SESSION['contato_erro'] = 'Erro ao enviar mensagem. Tente novamente.';
}

header('Location: ' . (isset($_POST['imovel_id']) ? 'imovel-detalhes.php?id=' . urlencode($imovel_id) : 'contato.php'));
exit;