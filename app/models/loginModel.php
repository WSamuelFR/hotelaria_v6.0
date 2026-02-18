<?php
// Inicia a sessão com segurança
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

require_once('../config/DBConnection.php');

if (file_exists('../config/PasswordUtils.php')) {
    require_once('../config/PasswordUtils.php');
}

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $email = $data['email'] ?? '';
    $senha_digitada = $data['senha'] ?? '';

    if (empty($email) || empty($senha_digitada)) {
        $response['message'] = 'E-mail e senha são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    $conn = Connect();
    if ($conn->connect_error) {
        $response['message'] = 'Falha na conexão com o banco.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT 
                c.cadastro_id, 
                c.cpf_cnpj, 
                c.full_name, 
                COALESCE(l_pf.senha, l_pj.senha) AS senha,
                COALESCE(l_pf.id, l_pj.id) AS login_id
            FROM cadastro c
            LEFT JOIN login l_pf ON c.cpf_cnpj = l_pf.cadastro AND l_pf.empresa IS NULL
            LEFT JOIN empresa e ON c.cpf_cnpj = e.cadastro
            LEFT JOIN login l_pj ON e.id_empresa = l_pj.empresa AND l_pj.cadastro IS NULL
            WHERE c.email = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Erro interno do servidor.';
        $conn->close();
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user && !empty($user['senha'])) {
        if (password_verify($senha_digitada, $user['senha'])) {
            
            // --- NOVIDADE DE SEGURANÇA: REGENERAÇÃO DE ID ---
            // Cria uma ID de sessão nova e limpa para o usuário que acabou de logar
            session_regenerate_id(true);

            $response['success'] = true;
            $response['message'] = 'Login bem-sucedido!';

            $_SESSION['user_id'] = $user['cadastro_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $user['full_name'];
            
            echo json_encode($response);
            exit; 
        } else {
            $response['message'] = 'E-mail ou senha incorretos.';
        }
    } else {
        $response['message'] = 'E-mail ou senha incorretos.';
    }
}

echo json_encode($response);
exit;