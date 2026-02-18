<?php
// Arquivo: app/views/configuracoes.php
require_once __DIR__ . '/app/config/auth.php';

$pageTitle = "Configurações e Atualizações";
require_once('layout/header.php');
require_once('layout/sidebar.php');
?>

<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary fw-bold mb-0">
            <i class="fas fa-cogs me-2"></i>Configurações do Sistema
        </h2>
        <span class="badge bg-secondary">Versão 0.5 - Stable</span>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-book me-2 text-primary"></i>Guia de Utilização Rápida
                </div>
                <div class="card-body">
                    <div class="accordion accordion-flush" id="guiaSistema">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#guiaLobby">
                                    <strong>1. Dashboard (Lobby)</strong>
                                </button>
                            </h2>
                            <div id="guiaLobby" class="accordion-collapse collapse show">
                                <div class="accordion-body text-muted small">
                                    O Lobby é o coração do sistema. Aqui você monitora quartos em tempo real:
                                    <ul>
                                        <li><span class="text-success">Verde:</span> Quarto livre e limpo.</li>
                                        <li><span class="text-primary">Azul:</span> Quarto ocupado (clique no botão do hóspede para gerenciar consumo).</li>
                                        <li><span class="text-danger">Vermelho:</span> Quarto sujo (necessita limpeza via Zeladoria).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#guiaHospedagem">
                                    <strong>2. Check-in e Check-out</strong>
                                </button>
                            </h2>
                            <div id="guiaHospedagem" class="accordion-collapse collapse">
                                <div class="accordion-body text-muted small">
                                    Para abrir uma estadia, use a aba <strong>Check-in</strong>. Você pode adicionar múltiplos acompanhantes. No fechamento (Lobby > Hospedagens > Check-out), o sistema calcula automaticamente as diárias e o consumo acumulado.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fas fa-history me-2"></i>Log de Atualizações
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge bg-success">Novo</span>
                                <small class="text-muted">10/02/2026</small>
                            </div>
                            <h6 class="fw-bold mb-1">Autocorreção e Inteligência de Cadastro</h6>
                            <p class="small text-muted mb-0">Implementada busca automática de CEP (ViaCEP) e CNPJ (BrasilAPI). Adicionada lógica de autocorreção de status de quartos no Lobby.</p>
                        </li>
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge bg-primary">Update</span>
                                <small class="text-muted">08/02/2026</small>
                            </div>
                            <h6 class="fw-bold mb-1">Gestão de Consumo Individual</h6>
                            <p class="small text-muted mb-0">Agora é possível lançar itens de consumo (água, lanches) vinculados a hóspedes específicos dentro de um quarto.</p>
                        </li>
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge bg-primary">Update</span>
                                <small class="text-muted">05/02/2026</small>
                            </div>
                            <h6 class="fw-bold mb-1">Nota Fiscal em PDF</h6>
                            <p class="small text-muted mb-0">Gerador de extrato detalhado via Dompdf integrado ao fluxo de check-out.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>