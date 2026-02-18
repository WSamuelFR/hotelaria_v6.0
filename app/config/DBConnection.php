<?php

/**
 * Função simples para carregar variáveis de um arquivo .env para o ambiente PHP.
 * @param string $path Caminho completo para o arquivo .env.
 * @return bool Retorna true em sucesso, false em falha.
 */
function loadEnv(string $path): bool
{
    if (!file_exists($path)) {
        // Loga um erro se o arquivo não for encontrado (IMPORTANTE)
        error_log(".env file not found at: " . $path);
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Divide a linha em chave e valor
        list($name, $value) = explode('=', $line, 2);

        // Limpa espaços e aspas
        $name = trim($name);
        $value = trim($value, " \n\r\t\v\x00\"'");

        // Define a variável de ambiente. Use $_ENV, $_SERVER ou putenv().
        if (!array_key_exists($name, $_SERVER)) {
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

/**
 * Função de Conexão com o Banco de Dados, utilizando variáveis de ambiente.
 * @return mysqli Uma instância de conexão mysqli.
 */
function Connect(): mysqli {
    $env_file_path = __DIR__ . '/.env'; 
    loadEnv($env_file_path);

    // No InfinityFree, se $_ENV falhar, tentamos pegar de $_SERVER ou usar valores padrão
    $servername = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'];
    $username   = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'];
    $password   = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'];
    $dbname     = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'];
    $port       = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 3306;

    // Criar a conexão
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    // Verificar erro de forma detalhada
    if ($conn->connect_error) {
        die("Falha na conexão: (" . $conn->connect_errno . ") " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// Exemplo de como usar em outros arquivos:
// require_once('caminho/para/DBConnection.php');
// $db = Connect();
// if ($db) { echo "Conexão bem-sucedida!"; }
