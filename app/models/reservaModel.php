<?php
// Arquivo: app/models/reservaModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class ReservaModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Connect();
    }

    /**
     * INSERIR RESERVA
     * Blindagem: Força os tipos de dados para evitar gravação de "0"
     */
    public function insertReserva(array $data): int|string
    {
        $sql = "INSERT INTO reserva (titular_nome, titularCpf_cnpj, titular_phone, email, acompanhante, quarto, data_checkin, data_checkout, situacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";

        $stmt = $this->conn->prepare($sql);

        // Tratamento rigoroso de tipos
        $nome   = (string)($data['titular_nome'] ?? '');
        $cpf    = (string)($data['titular_cpf'] ?? '');
        $phone  = (string)($data['titular_phone'] ?? '');
        $email  = (string)($data['email'] ?? '');
        $acomp  = (string)($data['acompanhantes'] ?? ''); // Texto formatado da textarea
        $quarto = (int)($data['quarto'] ?? 0);
        $in     = (string)($data['data_checkin'] ?? '');
        $out    = (string)($data['data_checkout'] ?? '');

        // ssss = strings iniciais | i = quarto | sss = strings finais
        $stmt->bind_param("sssssiss", $nome, $cpf, $phone, $email, $acomp, $quarto, $in, $out);

        $res = $stmt->execute();
        $id = $this->conn->insert_id;
        $stmt->close();
        return $res ? $id : "Erro: " . $this->conn->error;
    }

    /**
     * ATUALIZAR RESERVA
     * Blindagem: Garante que a string do textarea sobrescreva o campo TEXT
     */
    public function updateReserva(array $data)
    {
        $sql = "UPDATE reserva SET 
                    titular_nome = ?, 
                    titularCpf_cnpj = ?, 
                    titular_phone = ?, 
                    email = ?, 
                    acompanhante = ?, 
                    quarto = ?, 
                    data_checkin = ?, 
                    data_checkout = ? 
                WHERE reserva_id = ?";

        $stmt = $this->conn->prepare($sql);

        // Tratamento rigoroso de tipos
        $nome   = (string)($data['titular_nome'] ?? '');
        $cpf    = (string)($data['titular_cpf'] ?? '');
        $phone  = (string)($data['titular_phone'] ?? '');
        $email  = (string)($data['email'] ?? '');
        $acomp  = (string)($data['acompanhantes'] ?? '');
        $quarto = (int)($data['quarto'] ?? 0);
        $in     = (string)($data['data_checkin'] ?? '');
        $out    = (string)($data['data_checkout'] ?? '');
        $id     = (int)($data['id'] ?? 0);

        // ssss = strings | i = quarto | ss = datas | i = id da reserva
        $stmt->bind_param("sssssissi", $nome, $cpf, $phone, $email, $acomp, $quarto, $in, $out, $id);

        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function getReservaById($id)
    {
        $sql = "SELECT r.*, q.numero as numero_quarto, q.room_type 
                FROM reserva r 
                JOIN quarto q ON r.quarto = q.quarto_id 
                WHERE r.reserva_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public function getQuartosForDatalist(string $searchTerm = ''): array
    {
        $searchParam = "%" . $searchTerm . "%";
        $sql = "SELECT quarto_id, numero, room_type, room_status, clean_status 
                FROM quarto WHERE numero LIKE ? OR room_type LIKE ? ORDER BY numero";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        $quartos = [];
        while ($row = $result->fetch_assoc()) {
            $status = ($row['room_status'] == 'livre' && $row['clean_status'] == 'sujo') ? 'LIMPEZA' : strtoupper($row['room_status']);
            $quartos[] = [
                'id' => $row['quarto_id'], 
                'numero' => $row['numero'], 
                'tipo' => $row['room_type'], 
                'status_display' => $status
            ];
        }
        $stmt->close();
        return $quartos;
    }

    public function excluirReserva($id)
    {
        $sql = "DELETE FROM reserva WHERE reserva_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}