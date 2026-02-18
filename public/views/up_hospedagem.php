<?php
require_once __DIR__ . '/app/config/auth.php';
// app/views/up_hospedagem.php
$pageTitle = "Editar Hospedagem";
require_once('layout/header.php');
require_once('layout/sidebar.php');
require_once('app/models/hospedagemModel.php');
require_once('app/models/quartoModel.php');
require_once('app/models/cadastroModel.php');

$hospedagem_id = $_GET['id'] ?? null;

$hModel = new HospedagemModel();
$qModel = new QuartoModel();
$cadModel = new CadastroModel();

$dadosHospedagem = $hModel->getHospedagemById($hospedagem_id);
$quartoAtualId = $dadosHospedagem['quarto'] ?? null;
$situacao = $dadosHospedagem['situacao'] ?? 'ativa';

$quartos = $qModel->listarTodos($quartoAtualId);
$clientes = $cadModel->listarTodos();
?>

<style>
    /* Estilos para o modo leitura quando encerrada */
    .modo-leitura .btn-danger,
    .modo-leitura #busca_acompanhante,
    .modo-leitura .btn-success {
        display: none !important;
    }

    .modo-leitura input,
    .modo-leitura select,
    .modo-leitura textarea {
        pointer-events: none;
        background-color: #f8f9fa;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<div class="container-fluid mt-4">
    <h2 class="text-primary mb-4 px-3">
        <i class="fas fa-edit me-2"></i>Editar Hospedagem #<?= str_pad($hospedagem_id, 5, "0", STR_PAD_LEFT) ?>
        <?php if ($situacao === 'encerrada'): ?>
            <span class="badge bg-secondary ms-2">ENCERRADA</span>
        <?php else: ?>
            <span class="badge bg-success ms-2">ATIVA</span>
        <?php endif; ?>
        <h6 class="fw-bold text-danger ms-4">Sem checkout manual, o sistema cobrará automaticamente a diária do dia seguinte.</h6>
    </h2>

    <form id="upHospedagemForm" autocomplete="off" class="px-3 <?= ($situacao === 'encerrada') ? 'modo-leitura' : '' ?>">
        <input type="hidden" id="hospedagem_id" value="<?= htmlspecialchars($hospedagem_id) ?>">
        <input type="hidden" id="titular_id">
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark fw-bold">Configuração da Estadia</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="fw-bold">Titular da Conta</label>
                        <input type="text" list="listaClientes" id="busca_titular" class="form-control" placeholder="Busque por nome...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Quarto Atual</label>
                        <select id="quarto_id" class="form-select" required>
                            <?php foreach ($quartos as $q): ?>
                                <option value="<?= $q['quarto_id'] ?>" <?= ($q['quarto_id'] == $quartoAtualId) ? 'selected' : '' ?>>
                                    Quarto <?= htmlspecialchars($q['numero']) ?> (<?= htmlspecialchars($q['room_type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-success">Diária por Pessoa (R$)</label>
                        <input type="number" id="preco_unitario" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold">Data Entrada</label>
                        <input type="date" id="checkin" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold">Data Saída (Prevista)</label>
                        <input type="date" id="checkout" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Notas Internas</label>
                        <input type="text" id="obs" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4 h-100">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2"></i>Ocupantes do Quarto</span>
                        <?php if ($situacao === 'ativa'): ?>
                            <input type="text" list="listaClientes" id="busca_acompanhante" class="form-control form-control-sm w-50" placeholder="Adicionar hóspede...">
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive" style="min-height: 250px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hóspede</th>
                                    <th>Documento</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="gridHospedesEdicao" class="cursor-pointer"></tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <h4 class="mb-0 text-primary">Subtotal Estadia: R$ <span id="labelTotal">0.00</span></h4>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm mb-4 h-100 border-primary">
                    <div class="card-header bg-primary text-white p-0">
                        <ul class="nav nav-tabs border-bottom-0" id="consumoTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold text-dark" id="consumo-aba" data-bs-toggle="tab" data-bs-target="#aba-consumo" type="button" role="tab">
                                    <i class="fas fa-receipt me-2"></i>Consumo: <span id="nomeHospedeAtivo">...</span>
                                </button>
                            </li>
                            <?php if ($situacao === 'ativa'): ?>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-dark" id="loja-aba" data-bs-toggle="tab" data-bs-target="#aba-loja" type="button" role="tab">
                                        <i class="fas fa-cart-plus me-2"></i>Loja / Vendas
                                    </button>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active p-3" id="aba-consumo" role="tabpanel">
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Produto / Data</th>
                                            <th class="text-center">Qtd</th>
                                            <th>Preço Unit.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaConsumoHospede"></tbody>
                                </table>
                            </div>
                        </div>

                        <?php if ($situacao === 'ativa'): ?>
                            <div class="tab-pane fade p-3" id="aba-loja" role="tabpanel">
                                <div class="table-responsive" style="max-height: 250px;">
                                    <table class="table table-sm align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produto</th>
                                                <th>Preço</th>
                                                <th width="80">Qtd</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="catalogoLoja"></tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3 mb-5">
            <?php if ($situacao === 'ativa'): ?>
                <button type="submit" class="btn btn-success btn-lg flex-grow-1 shadow fw-bold">
                    <i class="fas fa-save me-2"></i>ATUALIZAR HOSPEDAGEM
                </button>
            <?php else: ?>
                <div class="alert alert-secondary flex-grow-1 m-0 d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i> Esta hospedagem está encerrada. Alterações não são permitidas.
                </div>
            <?php endif; ?>

            <button type="button" onclick="gerarPDF(<?= $hospedagem_id ?>)" class="btn btn-primary btn-lg shadow fw-bold" title="GERAR EXTRATO PDF">
                <i class="fas fa-file-pdf me-2"></i>GERAR EXTRATO PDF
            </button>

            <a href="lobby.php" class="btn btn-outline-secondary btn-lg">Sair</a>
        </div>
    </form>
</div>

<datalist id="listaClientes">
    <?php foreach ($clientes as $c): ?>
        <option value="<?= htmlspecialchars($c['full_name']) ?> | <?= htmlspecialchars($c['cpf_cnpj']) ?>"
            data-id="<?= $c['cadastro_id'] ?>"
            data-nome="<?= htmlspecialchars($c['full_name']) ?>"
            data-doc="<?= htmlspecialchars($c['cpf_cnpj']) ?>">
        </option>
    <?php endforeach; ?>
</datalist>

<?php require_once('layout/footer.php'); ?>
<script src="/js/up_hospedagem.js"></script>