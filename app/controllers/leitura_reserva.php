<?php
// Arquivo: app/controllers/leitura_reserva.php

require_once('../models/reservaModel.php');

// Define o cabeçalho como JSON para o JavaScript interpretar corretamente
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

$response = [
    'success' => false,
    'message' => 'ID da reserva não fornecido.',
    'data' => null
];

if ($id) {
    try {
        $model = new ReservaModel();
        // O método getReservaById já foi atualizado no Model para trazer os novos campos
        $dados = $model->getReservaById((int)$id);

        if ($dados) {
            $response['success'] = true;
            $response['data'] = $dados;
            $response['message'] = 'Dados carregados com sucesso.';
        } else {
            $response['message'] = 'Reserva não encontrada no banco de dados.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Erro interno: ' . $e->getMessage();
    }
}

// Retorna o objeto completo para o fetch no up_reserva.js
echo json_encode($response);