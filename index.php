<?php
// Arquivo: public/index.php

// Inicia a sessão para verificar se o usuário já está logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona direto para o Lobby
if (isset($_SESSION['user_id'])) {
    header("Location: /public/views/lobby.php");
    exit();
}

// Captura mensagens vindas da URL (via GET)
$error = $_GET['error'] ?? null;
$msg = $_GET['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WS-HOTELARIA | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-container { width: 100%; height: 100vh; }
        .carousel-item img { height: 100vh; object-fit: cover; filter: brightness(0.6); }
        .login-card { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .btn-primary { background-color: #0056b3; border-color: #0056b3; }
    </style>
</head>
<body>

    <div class="main-container d-flex flex-row">
        <div class="d-none d-md-block col-md-7">
            <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
                <div class="carousel-inner h-100">
                    <div class="carousel-item active">
                        <img src="https://placehold.co/1920x1080/0056b3/fff?text=Hotel+Da+Serra" class="d-block w-100">
                        <div class="carousel-caption d-none d-md-block">
                            <h5 class="fw-bold fs-3">Bem-Vindo ao Sistema Hotelaria</h5>
                            <p>Gerencie reservas, clientes e quartos de forma eficiente.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://placehold.co/1920x1080/ffcc00/000?text=Gerenciamento+de+Reservas" class="d-block w-100">
                        <div class="carousel-caption d-none d-md-block">
                            <h5 class="fw-bold fs-3">Controle de Hospedagens</h5>
                            <p>Check-in e Check-out rápidos e detalhados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-5 d-flex align-items-center justify-content-center py-5">
            <div class="login-card p-4 p-md-5 w-100 mx-3" style="max-width: 400px;">
                <h2 class="text-center mb-4 text-primary fw-bold">Acesso ao Sistema</h2>

                <?php if ($error === 'acesso_negado'): ?>
                    <div class="alert alert-danger p-2 small text-center">Sessão expirada. Faça login novamente.</div>
                <?php endif; ?>

                <?php if ($msg === 'logout_sucesso'): ?>
                    <div class="alert alert-info p-2 small text-center">Você saiu com segurança. até logo!</div>
                <?php endif; ?>

                <div id="message-box" class="d-none p-2 mb-3 text-center small" role="alert"></div>

                <form id="loginForm" autocomplete="off" method="POST" >
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>

                    <button type="submit" id="loginButton" class="btn btn-primary w-100 mb-3 shadow">
                        Entrar
                    </button>
                </form>

                <div class="text-center small">
                    <a href="/app/views/new_cadastro.php"><p class="mb-1">Crie seu cadastro</p></a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/js/login.js"></script>
</body>
</html>