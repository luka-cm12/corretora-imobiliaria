<?php
// excluir.php - Script para exclusão segura de imóveis

// Inicia a sessão para controle de acesso
session_start();

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Inclui o arquivo de conexão com o banco de dados
require_once 'db.php';

// Verifica se o método de requisição é POST (mais seguro para exclusões)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verifica se o token CSRF é válido (proteção contra ataques)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['erro'] = "Token de segurança inválido!";
        header("Location: lista.php");
        exit();
    }

    // Obtém e sanitiza o ID do imóvel
    $imovel_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($imovel_id) {
        try {
            // Inicia transação para segurança
            $conn->beginTransaction();

            // 1. Primeiro exclui as imagens associadas ao imóvel
            // a) Obtém os nomes dos arquivos de imagem
            $stmt = $conn->prepare("SELECT imagem_url FROM imovel_imagens WHERE imovel_id = ?");
            $stmt->execute([$imovel_id]);
            $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // b) Exclui fisicamente os arquivos de imagem
            foreach ($imagens as $imagem) {
                $caminho_imagem = "uploads/imoveis/" . $imagem;
                if (file_exists($caminho_imagem)) {
                    unlink($caminho_imagem);
                }
            }

            // c) Exclui os registros das imagens no banco
            $stmt = $conn->prepare("DELETE FROM imovel_imagens WHERE imovel_id = ?");
            $stmt->execute([$imovel_id]);

            // 2. Exclui os recursos do imóvel (piscina, garagem, etc.)
            $stmt = $conn->prepare("DELETE FROM imovel_recursos WHERE imovel_id = ?");
            $stmt->execute([$imovel_id]);

            // 3. Finalmente exclui o imóvel
            $stmt = $conn->prepare("DELETE FROM imoveis WHERE id = ?");
            $stmt->execute([$imovel_id]);

            // Confirma a transação
            $conn->commit();

            $_SESSION['sucesso'] = "Imóvel excluído com sucesso!";
            
        } catch (PDOException $e) {
            // Em caso de erro, desfaz todas as operações
            $conn->rollBack();
            $_SESSION['erro'] = "Erro ao excluir imóvel: " . $e->getMessage();
        }
    } else {
        $_SESSION['erro'] = "ID do imóvel inválido!";
    }
} else {
    $_SESSION['erro'] = "Método de requisição inválido!";
}

// Redireciona de volta para a lista de imóveis
header("Location: lista.php");
exit();
?>