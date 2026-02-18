<?php
// Arquivo: app/controllers/reserva_process.php

require_once('../models/reservaModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lendo o corpo da requisição JSON enviada pelo reserva.js
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Capturando os novos campos individuais do Titular
    $titular_nome  = $data['titular_nome'] ?? '';
    $titular_cpf   = $data['titular_cpf'] ?? '';
    $quarto        = $data['quarto'] ?? ''; 
    $checkin       = $data['data_checkin'] ?? '';
    $checkout      = $data['data_checkout'] ?? '';
    
    // Campos opcionais
    $titular_phone = $data['titular_phone'] ?? '';
    $email         = $data['email'] ?? '';
    $acompanhantes = $data['acompanhantes'] ?? ''; // String vinda do join(', ') no JS

    // 1. Validação crítica de campos obrigatórios
    if (empty($titular_nome) || empty($titular_cpf) || empty($quarto) || empty($checkin) || empty($checkout)) {
        $response['message'] = 'Erro: Nome do Titular, CPF, Quarto e Período são obrigatórios.';
        echo json_encode($response);
        exit;
    }
    
    // 2. Validação lógica das datas
    if (strtotime($checkin) >= strtotime($checkout)) {
        $response['message'] = 'A data de saída deve ser após a data de entrada.';
        echo json_encode($response);
        exit;
    }

    // 3. Processamento via Model
    try {
        $model = new ReservaModel();
        
        // Garantimos que o ID do quarto seja inteiro
        $data['quarto'] = (int)$quarto;

        // O Model agora espera um array com as chaves: 
        // titular_nome, titular_cpf, titular_phone, email, acompanhantes, quarto, data_checkin, data_checkout
        $result = $model->insertReserva($data);

        if (is_numeric($result)) {
            $response['success'] = true;
            $response['message'] = 'Reserva #' . $result . ' para ' . $titular_nome . ' criada com sucesso!';
            $response['reserva_id'] = $result;
        } else {
            // Caso o Model retorne a string de erro da transação
            $response['message'] = $result;
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro no processamento: ' . $e->getMessage();
    }
}

echo json_encode($response);