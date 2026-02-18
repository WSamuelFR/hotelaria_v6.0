<?php
// Arquivo: app/models/lobbyModel.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once('../config/DBConnection.php');

$response = ['success' => false, 'message' => '', 'cadastros' => [], 'quartos' => [], 'reservas' => [], 'hospedagens' => []];

try {
    $conn = Connect();
    if ($conn->connect_error) throw new Exception("Falha na conexão com o DB.");

    $searchTerm = $_GET['search'] ?? '';
    $statusFilter = $_GET['status'] ?? 'todas';
    $reservaStatus = $_GET['reservaStatus'] ?? 'pendente';
    $searchParam = !empty($searchTerm) ? "%" . $searchTerm . "%" : null;

    // 1. CONSULTA DE CADASTROS
    $sqlCadastros = "SELECT cadastro_id, full_name as nome_cliente, cpf_cnpj,
                    CASE WHEN CHAR_LENGTH(cpf_cnpj) = 14 THEN 'Empresa' ELSE 'Hóspede' END as tipo
                    FROM cadastro";
    if ($searchParam) {
        $sqlCadastros .= " WHERE full_name LIKE ? OR cpf_cnpj LIKE ?";
        $stmtC = $conn->prepare($sqlCadastros);
        $stmtC->bind_param("ss", $searchParam, $searchParam);
        $stmtC->execute();
        $response['cadastros'] = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $response['cadastros'] = $conn->query($sqlCadastros . " LIMIT 50")->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------
    // 2. CONSULTA DE QUARTOS (CORREÇÃO DE CHAVE PARA JS)
    // ------------------------------------------
    $sqlQuartos = "
        SELECT 
            q.quarto_id AS id,
            q.numero,
            q.room_type AS tipo,
            q.bed_quantity AS camas,
            q.room_status AS status_principal,
            q.clean_status, 
            c.full_name AS cliente_atual,
            h.hospedagem_id AS hospedagem_ativa_id
        FROM quarto q
        LEFT JOIN hospedagem h ON q.quarto_id = h.quarto AND h.situacao = 'ativa'
        LEFT JOIN cadastro c ON h.hospedes = c.cadastro_id
    ";

    if ($searchParam) {
        $sqlQuartos .= " WHERE q.numero LIKE ? OR q.room_type LIKE ?";
    }
    $sqlQuartos .= " ORDER BY q.numero";

    $stmtQuartos = $conn->prepare($sqlQuartos);
    if ($searchParam) {
        $stmtQuartos->bind_param("ss", $searchParam, $searchParam);
    }
    $stmtQuartos->execute();

    $resultQuartos = $stmtQuartos->get_result();
    while ($row = $resultQuartos->fetch_assoc()) {
        // Sincroniza o status_display com a lógica de limpeza para o badge visual
        $row['status_display'] = ($row['status_principal'] == 'livre' && $row['clean_status'] == 'sujo') ? 'limpeza' : $row['status_principal'];
        $row['cliente_atual'] = $row['cliente_atual'] ?? 'Vazio';
        $response['quartos'][] = $row;
    }
    $stmtQuartos->close();

    // --- 3. CONSULTA DE RESERVAS (ATUALIZADA) ---
    // Note que agora buscamos as colunas titular_nome e titularCpf_cnpj diretamente da tabela reserva
    $sqlReservas = "SELECT r.reserva_id, r.titular_nome, r.titularCpf_cnpj, 
                       r.data_checkin, r.data_checkout, r.situacao,
                       q.numero as numero_quarto
                FROM reserva r
                LEFT JOIN quarto q ON r.quarto = q.quarto_id
                WHERE 1=1";

    if ($reservaStatus !== 'todas') {
        $sqlReservas .= " AND r.situacao = '$reservaStatus'";
    }

    if ($searchParam) {
        // Busca pelo nome do titular ou pelo CPF do titular
        $sqlReservas .= " AND (r.titular_nome LIKE ? OR r.titularCpf_cnpj LIKE ? OR r.reserva_id LIKE ?)";
        $stmtR = $conn->prepare($sqlReservas);
        $stmtR->bind_param("sss", $searchParam, $searchParam, $searchParam);
        $stmtR->execute();
        $response['reservas'] = $stmtR->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $response['reservas'] = $conn->query($sqlReservas . " ORDER BY r.reserva_id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
    }

    // ------------------------------------------
    // 4. CONSULTA DE HOSPEDAGENS (MANTIDA)
    // ------------------------------------------
    $sqlHosp = "
        SELECT 
            h.hospedagem_id, 
            h.data_checkin, 
            h.data_checkout, 
            h.valor_hospedagem,
            h.valor_consumo,
            h.total_dispesa, -- Substituído 'total' por 'total_dispesa'
            h.situacao,
            c.full_name AS nome_hospede, 
            q.numero AS numero_quarto
        FROM hospedagem h
        JOIN cadastro c ON h.hospedes = c.cadastro_id
        JOIN quarto q ON h.quarto = q.quarto_id
    ";

    $whereHosp = [];
    $paramsHosp = [];
    $typesHosp = "";

    if ($statusFilter !== 'todas') {
        $whereHosp[] = "h.situacao = ?";
        $paramsHosp[] = $statusFilter;
        $typesHosp .= "s";
    }

    if ($searchParam) {
        $searchQuery = "%" . $searchParam . "%"; // Garante que o LIKE funcione
        $whereHosp[] = "(c.full_name LIKE ? OR q.numero LIKE ?)";
        $paramsHosp[] = $searchQuery;
        $paramsHosp[] = $searchQuery;
        $typesHosp .= "ss";
    }

    if ($whereHosp) {
        $sqlHosp .= " WHERE " . implode(" AND ", $whereHosp);
    }
    $sqlHosp .= " ORDER BY h.hospedagem_id DESC";

    $stmtHosp = $conn->prepare($sqlHosp);
    if ($paramsHosp) {
        $stmtHosp->bind_param($typesHosp, ...$paramsHosp);
    }

    if (!$stmtHosp->execute()) {
        echo json_encode(['success' => false, 'message' => 'Erro SQL: ' . $stmtHosp->error]);
        exit;
    }

    $response['hospedagens'] = $stmtHosp->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtHosp->close();

    $response['success'] = true;
    $response['message'] = 'Dados carregados com sucesso.';

    $response['success'] = true;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
