<?php
// app/controllers/hospedagem_data.php
require_once('../config/DBConnection.php');
header('Content-Type: application/json');

$reserva_id = $_GET['reserva_id'] ?? null;

if ($reserva_id) {
    $conn = Connect(); 
    
    // SQL atualizado para trazer dados completos e preparar a nova estrutura financeira
    $sql = "SELECT 
                r.reserva_id, 
                r.data_checkin, 
                r.data_checkout, 
                c.full_name, 
                c.cpf_cnpj, 
                c.cadastro_id, 
                q.quarto_id,
                q.numero as numero_quarto,
                q.room_type
            FROM reserva r 
            JOIN cadastro c ON r.cadastro = c.cpf_cnpj 
            JOIN quarto q ON r.quarto = q.numero
            WHERE r.reserva_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reserva_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva = $result->fetch_assoc();

    if ($reserva) {
        // Adicionamos valores padrão para a nova estrutura de colunas
        // Isso ajuda o JavaScript a inicializar os campos 'valor_hospedagem'
        $reserva['valor_hospedagem_sugerido'] = 0.00; 
        $reserva['valor_consumo_inicial'] = 0.00;
        
        echo json_encode(["success" => true, "data" => $reserva]);
    } else {
        echo json_encode(["success" => false, "message" => "Reserva não encontrada."]);
    }
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "ID da reserva não fornecido."]);
}