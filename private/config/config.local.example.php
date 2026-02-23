<?php
/**
 * Exemplo de override local de configuração
 *
 * Como usar:
 * 1) Copie este arquivo para o mesmo diretório com o nome: config.local.php
 * 2) Ajuste os valores abaixo para o ambiente local (PC do cliente ou máquina de desenvolvimento)
 * 3) O sistema carregará este arquivo automaticamente (se existir) antes de definir os padrões
 *
 * Observação: Não faça commit de config.local.php (arquivo específico do ambiente)
 */

// Banco de dados - ajuste conforme necessário
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'corretora_base');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Se quiser forçar exibição de erros no ambiente local, descomente:
// ini_set('display_errors', '1');
// error_reporting(E_ALL);

// Você também pode definir BASE_URL manualmente, se necessário (normalmente desnecessário):
// if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/corretora-imobiliaria/');
