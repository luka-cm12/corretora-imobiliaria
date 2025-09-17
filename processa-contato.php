<?php
// filepath: processa-contato.php

require_once __DIR__ . '/private/includes/db.php';

$imovel_id     = $_POST['imovel_id'] ?? '';
$imovel_titulo = $_POST['imovel_titulo'] ?? '';
$nome          = trim($_POST['nome'] ?? '');
$email         = trim($_POST['email'] ?? '');
$telefone      = trim($_POST['telefone'] ?? '');
$interesse     = trim($_POST['assunto'] ?? '');
$mensagem      = trim($_POST['mensagem'] ?? '');

session_start();
$erros = [];

if (!$nome)      $erros[] = "Nome é obrigatório.";
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "E-mail inválido.";
if (!$telefone)  $erros[] = "Telefone é obrigatório.";

if ($erros) {
    $_SESSION['contato_erro'] = implode('<br>', $erros);
    header('Location: imovel-detalhes.php?id=' . urlencode($imovel_id));
    exit;
}

// Salva no banco de dados
$sql = "INSERT INTO contatos (imovel_id, imovel_titulo, nome, email, telefone, assunto, mensagem, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$result = db_query($sql, [$imovel_id, $imovel_titulo, $nome, $email, $telefone, $assunto, $mensagem]);

if ($result) {
    $_SESSION['contato_sucesso'] = "Mensagem enviada com sucesso!";
} else {
    $_SESSION['contato_erro'] = "Erro ao enviar mensagem. Tente novamente.";
}
header('Location: imovel-detalhes.php?id=' . urlencode($imovel_id));
exit;