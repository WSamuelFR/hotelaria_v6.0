<?php
require_once __DIR__ . '/app/config/auth.php';
require_once('layout/header.php');
require_once('layout/sidebar.php');
$reserva_id = $_GET['id'] ?? null;
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0"><i class="fas fa-edit me-2"></i>Editar Reserva #<?php echo $reserva_id; ?></h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Dados de Ocupação e Período</h5>
        </div>
        <div class="card-body">
            <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

            <form id="upReservaForm" autocomplete="off">
                <input type="hidden" name="reserva_id" id="reserva_id" value="<?php echo $reserva_id; ?>">

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Selecionar Quarto</label>
                        <select class="form-select" name="quarto" id="quarto_select" required>
                            <option value="">Carregando quartos...</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Data de Check-in</label>
                        <input type="date" class="form-control" name="data_checkin" id="data_checkin" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Data de Check-out</label>
                        <input type="date" class="form-control" name="data_checkout" id="data_checkout" required>
                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user-tie me-2"></i>DADOS DO TITULAR (RESPONSÁVEL)</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nome Completo</label>
                            <input type="text" id="titular_nome" name="titular_nome" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">CPF/CNPJ</label>
                            <input type="text" id="titular_cpf" name="titular_cpf" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Telefone</label>
                            <input type="text" id="titular_phone" name="titular_phone" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">E-mail</label>
                            <input type="email" id="titular_email" name="titular_email" class="form-control">
                        </div>
                    </div>
                </div>

                <fieldset class="border p-4 mb-4 bg-light rounded shadow-sm">
                    <legend class="w-auto px-3 fs-6 text-primary fw-bold bg-white border rounded">
                        <i class="fas fa-users me-2"></i>Acompanhantes
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
                        <label class="small fw-bold text-muted">Lista de Acompanhantes:</label>
                        <textarea id="acompanhantes_texto" name="acompanhantes" class="form-control bg-white" rows="5" placeholder="Nenhum acompanhante registrado..." readonly></textarea>
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i> * Caso precise editar, limpe a lista e adicione novamente.
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" id="btnLimparAcomp" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-eraser"></i> Limpar Tudo
                        </button>
                    </div>
                </fieldset>

                <div class="text-center mt-4">
                    <button type="submit" id="submitButton" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Atualizar Reserva
                    </button>
                    <a href="lobby.php" class="btn btn-secondary btn-lg ms-3">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>
<script src="/js/up_reserva.js"></script>