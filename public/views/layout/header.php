<?php
// Arquivo: app/Views/Layout/header.php
// Inclui o início do HTML, cabeçalho e a Navbar.

// PASSO 2: Segurança do Header
// Garante que o arquivo de autenticação seja carregado para proteger esta View

// Recupera os dados reais da sessão definidos no loginModel.php
$userName = $_SESSION['user_name'] ?? "Usuário";
$userId = $_SESSION['user_id'] ?? ""; 
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'WS-HOTELARIA'; ?> | Gerenciamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/../public/css/global.css">
</head>

<body class="bg-light">

    <header class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand d-flex align-items-center" href="lobby.php">
                <i class="fas fa-hotel me-2"></i>
                <span class="fw-bold">WS-HOTELARIA</span>
            </a>

            <div class="ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span id="userName"><?php echo htmlspecialchars($userName); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="up_cadastro.php?id=<?php echo $userId; ?>">
                                <i class="fas fa-address-card me-2"></i> Meu Cadastro
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="../models/logoutModel.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main id="mainContent" class="p-0"></main>