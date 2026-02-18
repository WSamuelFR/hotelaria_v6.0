<?php
// app/config/auth.php

if (session_status() === PHP_SESSION_NONE) {
    // Configurações de segurança para a sessão antes de iniciar
    ini_set('session.cookie_httponly', 1); // Impede que o JS acesse o cookie de sessão
    ini_set('session.use_only_cookies', 1); // Garante que a sessão só use cookies (evita fixação via URL)
    session_start();
}

// O "Porteiro": Verifica se a chave de acesso (user_id) existe
if (!isset($_SESSION['user_id'])) {
    // Se não existir, destrói qualquer resquício de sessão e manda para o login
    session_unset();
    session_destroy();
    header("Location: /public/index.php?error=acesso_negado");
    exit();
}