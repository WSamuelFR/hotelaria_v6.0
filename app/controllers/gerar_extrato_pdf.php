<?php
// Arquivo: app/controllers/gerar_extrato_pdf.php

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../models/hospedagemModel.php');

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$id = $_GET['id'] ?? null;
if (!$id) die("Erro: ID da hospedagem não fornecido.");

$model = new HospedagemModel();
$h = $model->getHospedagemById((int)$id);

if (!$h) die("Erro: Hospedagem não encontrada.");

// Busca o consumo consolidado
$consumosBrutos = $model->getConsumoTotalHospedagem((int)$id);

// --- LÓGICA DE AGRUPAMENTO PARA O PDF ---
$consumosAgrupados = [];
$totalConsumo = 0;

if ($consumosBrutos) {
    foreach ($consumosBrutos as $c) {
        $chave = $c['nome_cliente'] . '_' . $c['nome_produto'];
        if (!isset($consumosAgrupados[$chave])) {
            $consumosAgrupados[$chave] = [
                'hospede' => $c['nome_cliente'],
                'produto' => $c['nome_produto'],
                'qtd'     => 0,
                'unitario'=> floatval($c['preco_unitario_pago']),
                'subtotal'=> 0
            ];
        }
        $consumosAgrupados[$chave]['qtd'] += (int)$c['quantidade'];
        $consumosAgrupados[$chave]['subtotal'] += ($c['quantidade'] * $c['preco_unitario_pago']);
        $totalConsumo += ($c['quantidade'] * $c['preco_unitario_pago']);
    }
}

$valorDiarias = floatval($h['valor_hospedagem'] ?? 0);
$valorGeral = $valorDiarias + $totalConsumo;

$nomesAcompanhantes = (isset($h['acompanhantes']) && !empty($h['acompanhantes']))
    ? implode(', ', array_column($h['acompanhantes'], 'nome_hospede'))
    : 'Nenhum acompanhante registrado.';

$html = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; margin-bottom: 20px; }
        .hotel-name { font-size: 18px; font-weight: bold; color: #2c3e50; }
        .info-box { background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 20px; }
        .section-title { background: #2c3e50; color: #fff; padding: 5px 10px; font-weight: bold; margin-top: 15px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #ececec; border: 1px solid #ccc; padding: 8px; text-align: left; }
        td { border: 1px solid #eee; padding: 8px; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #777; }
        .signature { margin-top: 40px; border-top: 1px solid #333; width: 250px; margin-left: auto; margin-right: auto; padding-top: 5px; }
        .total-highlight { font-size: 14px; font-weight: bold; color: #c0392b; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="hotel-name">POUSADA DA SERRA LTDA</div>
                <div>CNPJ: 10.642.423/0001-69</div>
                <div>Teixeira - Paraíba</div>
            </td>
            <td class="text-right">
                <div style="font-size: 14px; font-weight: bold;">EXTRATO DE CONTA #' . str_pad($id, 5, "0", STR_PAD_LEFT) . '</div>
                <div>Emissão: ' . date("d/m/Y H:i") . '</div>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <table style="border:none; margin:0;">
            <tr style="border:none;">
                <td style="border:none; width: 60%;">
                    <strong>HÓSPEDE TITULAR:</strong> ' . htmlspecialchars($h['nome_titular']) . '<br>
                    <strong>ACOMPANHANTES:</strong> ' . htmlspecialchars($nomesAcompanhantes) . '
                </td>
                <td style="border:none; width: 40%;" class="text-right">
                    <strong>QUARTO:</strong> ' . $h['numero_quarto'] . '<br>
                    <strong>PERÍODO:</strong> ' . date("d/m/Y", strtotime($h['data_checkin'])) . ' a ' . date("d/m/Y", strtotime($h['data_checkout'])) . '
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Serviços de Hospedagem</div>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-right">Total das Diárias</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Hospedagem completa para ' . $h['qtd_total_hospedes'] . ' pessoa(s)</td>
                <td class="text-right">R$ ' . number_format($valorDiarias, 2, ",", ".") . '</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Consumo Interno (Agrupado)</div>
    <table>
        <thead>
            <tr>
                <th>Hóspede</th>
                <th>Produto</th>
                <th class="text-right">Qtd</th>
                <th class="text-right">Unitário</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>';

if (empty($consumosAgrupados)) {
    $html .= '<tr><td colspan="5" class="text-center">Nenhum consumo registrado.</td></tr>';
} else {
    foreach ($consumosAgrupados as $item) {
        $html .= '<tr>
            <td>' . htmlspecialchars($item['hospede']) . '</td>
            <td>' . htmlspecialchars($item['produto']) . '</td>
            <td class="text-right">' . $item['qtd'] . '</td>
            <td class="text-right">R$ ' . number_format($item['unitario'], 2, ",", ".") . '</td>
            <td class="text-right">R$ ' . number_format($item['subtotal'], 2, ",", ".") . '</td>
        </tr>';
    }
}

$html .= '
        </tbody>
    </table>

    <table style="width: 250px; margin-left: auto; margin-top: 20px;">
        <tr>
            <td>Subtotal Hospedagem:</td>
            <td class="text-right">R$ ' . number_format($valorDiarias, 2, ",", ".") . '</td>
        </tr>
        <tr>
            <td>Subtotal Consumo:</td>
            <td class="text-right">R$ ' . number_format($totalConsumo, 2, ",", ".") . '</td>
        </tr>
        <tr class="total-highlight">
            <td>TOTAL GERAL:</td>
            <td class="text-right">R$ ' . number_format($valorGeral, 2, ",", ".") . '</td>
        </tr>
    </table>

    <div class="footer">
        <div class="signature">Assinatura do Hóspede: ' . htmlspecialchars($h['nome_titular']) . '</div>
        <p>Este documento não possui valor de nota fiscal eletrônica. Operador: ' . htmlspecialchars($h['usuario_responsavel'] ?? 'Sistema') . '</p>
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Extrato_Hospedagem_{$id}.pdf", ["Attachment" => false]);