<?php
// app/models/logoutModel.php

// Inicia a sessão para poder manipulá-la
session_start();

// 1. Limpa todas as variáveis de sessão
$_SESSION = array();

// 2. Se desejar destruir completamente a sessão, apague também o cookie de sessão.
// Isso ajuda a prevenir ataques de fixação de sessão no futuro.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destrói a sessão no servidor
session_destroy();

// 4. Redireciona para o login com uma mensagem de sucesso (opcional)
header("Location: /public/index.php?msg=logout_sucesso");
exit();