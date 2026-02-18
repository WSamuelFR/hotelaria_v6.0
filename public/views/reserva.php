<?php
require_once __DIR__ . '/app/config/auth.php';
$pageTitle = "Nova Reserva";
require_once('layout/header.php');
require_once('layout/sidebar.php');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0"><i class="fas fa-calendar-plus me-2"></i>Nova Reserva</h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Dados da Reserva</h5>
        </div>

        <div class="card-body">
            <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

            <form id="reservaForm" autocomplete="off">
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label for="quarto_select" class="form-label fw-bold">Selecionar Quarto</label>
                        <select class="form-select" id="quarto_select" name="quarto" required>
                            <option value="">Carregando quartos...</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="data_checkin" class="form-label fw-bold">Data de Check-in</label>
                        <input type="date" class="form-control" id="data_checkin" name="data_checkin" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="data_checkout" class="form-label fw-bold">Data de Check-out</label>
                        <input type="date" class="form-control" id="data_checkout" name="data_checkout" required>
                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user-tie me-2"></i>DADOS DO TITULAR</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" id="titular_nome" name="titular_nome" class="form-control" required placeholder="Nome do responsável">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">CPF/CNPJ</label>
                            <input type="text" id="titular_cpf" name="titular_cpf" class="form-control" required placeholder="000.000.000-00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" id="titular_phone" name="titular_phone" class="form-control" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">E-mail</label>
                            <input type="email" id="titular_email" name="titular_email" class="form-control" placeholder="email@exemplo.com">
                        </div>
                    </div>
                </div>

                <fieldset class="border p-4 mb-4 bg-light rounded shadow-sm">
                    <legend class="w-auto px-3 fs-6 text-primary fw-bold bg-white border rounded">
                        <i class="fas fa-users me-2"></i>Adicionar Acompanhantes
                    </legend>

                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <label class="small fw-bold">Nome Completo</label>
                            <input type="text" id="temp_nome" class="form-control form-control-sm" placeholder="Nome do acompanhante">
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold">CPF</label>
                            <input type="text" id="temp_cpf" class="form-control form-control-sm" placeholder="000.000.000-00">
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold">Telefone</label>
                            <input type="text" id="temp_fone" class="form-control form-control-sm" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="btnAddAcomp" class="btn btn-warning btn-sm w-100 fw-bold">
                                <i class="fas fa-plus"></i> ADD
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Conteúdo Formatado (Acompanhantes):</label>
                        <textarea id="acompanhantes_texto" name="acompanhantes" class="form-control bg-white" rows="4" readonly placeholder="Os acompanhantes aparecerão aqui formatados..."></textarea>
                        <div class="form-text text-danger">* Caso precise editar, limpe a lista e adicione novamente.</div>
                    </div>

                    <div class="text-end">
                        <button type="button" id="btnLimparAcomp" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-eraser"></i> Limpar Lista
                        </button>
                    </div>
                </fieldset>

                <div class="text-center mt-4">
                    <button type="submit" id="submitButton" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Confirmar Reserva
                    </button>
                    <a href="lobby.php" class="btn btn-secondary btn-lg ms-3">
                        Voltar ao Lobby
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>
<script src="/js/reserva.js"></script>