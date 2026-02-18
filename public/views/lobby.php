<?php
// Arquivo: app/Views/lobby.php
require_once __DIR__ . '/app/config/auth.php';
require_once('layout/header.php');
require_once('layout/sidebar.php');
?>


<div class="container-fluid mt-4">
    <ul class="nav nav-tabs" id="mainTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="quartos-tab" data-bs-toggle="tab" data-bs-target="#quartos" type="button" role="tab"><i class="fas fa-bed"></i> Quartos</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="hospedagens-tab" data-bs-toggle="tab" data-bs-target="#hospedagens" type="button" role="tab"><i class="fas fa-concierge-bell"></i> Hospedagens</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab"><i class="fas fa-calendar-alt"></i> Reservas</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="cadastros-tab" data-bs-toggle="tab" data-bs-target="#cadastros" type="button" role="tab"><i class="fas fa-users"></i> Cadastros</button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 shadow-sm" id="mainTabContent">

        <div class="tab-pane fade show active" id="quartos" role="tabpanel">
            <h3 class="mb-4 text-primary">Monitor de Quartos & Zeladoria</h3>
            <h6 class="fw-bold text-dark ms-1">Após o check-in, sempre limpar o quarto ocupado anteriormente.</h6>
            <h6 class="fw-bold text-dark ms-1">Sem checkout manual, o sistema cobrará automaticamente a diária do dia seguinte.</h6>
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active filter-quarto" data-filter="todos">Todos</button>
                    <button type="button" class="btn btn-outline-success filter-quarto" data-filter="livre">Limpos</button>
                    <button type="button" class="btn btn-outline-primary filter-quarto" data-filter="ocupado">Ocupados</button>
                    <button type="button" class="btn btn-outline-danger filter-quarto" data-filter="sujo">Sujos</button>
                </div>
                <div class="input-group w-50">
                    <input type="text" class="form-control" id="quartoSearchInput" placeholder="Número, Status ou Tipo...">
                    <button class="btn btn-primary" type="button" id="quartoSearchButton"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <div class="row g-4" id="quartoCardsContainer"></div>
        </div>


        <div class="tab-pane fade" id="hospedagens" role="tabpanel">
            <h3 class="mb-4 text-primary">Gestão de Hospedagens</h3>
            <h6 class="fw-bold text-dark ms-1">Após o check-in, sempre limpar o quarto ocupado anteriormente.</h6>
            <h6 class="fw-bold text-dark ms-1">Sem checkout manual, o sistema cobrará automaticamente a diária do dia seguinte.</h6>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active filter-hosp" data-status="ativa">Ativas</button>
                    <button type="button" class="btn btn-outline-primary filter-hosp" data-status="encerrada">Encerradas</button>
                    <button type="button" class="btn btn-outline-primary filter-hosp" data-status="todas">Todas</button>
                </div>
                <div class="input-group w-50">
                    <input type="text" class="form-control" id="hospedagemSearchInput" placeholder="Buscar Hóspede ou Número do Quarto...">
                    <button class="btn btn-primary" type="button" id="hospedagemSearchButton"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Hóspede</th>
                            <th>Quarto</th>
                            <th>Período</th>
                            <th>Total Acumulado</th>
                            <th>Situação</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="hospedagemTableBody">
                    </tbody>
                </table>
            </div>
        </div>


        <div class="tab-pane fade" id="reservas" role="tabpanel">
            <h3 class="mb-4 text-primary">Controle de Reservas</h3>
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div class="input-group w-50">
                    <input type="text" class="form-control" id="reservaSearchInput" placeholder="Buscar Hóspedes ou Nº Reserva...">
                    <button class="btn btn-primary" type="button" id="reservaSearchButton"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th><i class="fas fa-user-friends me-1"></i> Hóspedes / CPF/CNPJ</th>
                            <th>Situação</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="reservaTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="cadastros" role="tabpanel">
            <h3 class="mb-4 text-primary">Gestão de cadastros (Hóspedes & Empresas)</h3>
            <div class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" id="cadastroSearchInput" placeholder="Buscar por Nome, CPF ou CNPJ...">
                    <button class="btn btn-primary" type="button" id="cadastroSearchButton"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Nome / Razão social</th>
                            <th>CPF / CNPJ</th>
                            <th>Tipo</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="cadastroTableBody"></tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalCheckout" tabindex="-1" aria-labelledby="modalCheckoutLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalCheckoutLabel"><i class="fas fa-receipt me-2"></i>Resumo Financeiro - Check-out</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Hóspede Titular:</strong> <span id="checkoutNome">...</span></p>
                                <p><strong>Quarto:</strong> <span id="checkoutQuarto">...</span></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p><strong>Período:</strong> <span id="checkoutPeriodo">...</span></p>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-shopping-basket me-2"></i>Consumo Detalhado</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Qtd</th>
                                        <th>Unitário</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="checkoutListaConsumo"></tbody>
                            </table>
                        </div>

                        <div class="card bg-light mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span>Total em Diárias:</span>
                                    <span id="checkoutTotalDiarias">R$ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total em Consumo:</span>
                                    <span id="checkoutTotalConsumo">R$ 0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold fs-4 text-danger">
                                    <span>VALOR TOTAL A PAGAR:</span>
                                    <span id="checkoutTotalGeral">R$ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btnConfirmarCheckout">
                            <i class="fas fa-money-bill-wave me-2"></i>Confirmar Pagamento e Finalizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/lobby.js"></script>

    <?php require_once('layout/footer.php'); ?>