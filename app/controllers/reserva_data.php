<?php
// Arquivo: app/controllers/reserva_data.php

require_once('../models/reservaModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Erro ao processar requisição.',
    'data' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $model = new ReservaModel();

        // Captura o tipo de busca (atualmente focamos em quartos)
        $type = $_GET['type'] ?? 'quartos';
        $searchTerm = $_GET['search'] ?? '';

        if ($type === 'quartos') {
            // Busca quartos disponíveis ou pelo número/tipo
            $data = $model->getQuartosForDatalist($searchTerm);

            if (!empty($data)) {
                $response['success'] = true;
                $response['data'] = $data;
                $response['message'] = 'Quartos carregados com sucesso.';
            } else {
                $response['success'] = true; // Retornamos true para não travar o JS, mas com lista vazia
                $response['message'] = 'Nenhum quarto encontrado para os critérios informados.';
            }
        } else {
            $response['message'] = 'Tipo de busca não suportado.';
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro no servidor: ' . $e->getMessage();
    }
}

echo json_encode($response);