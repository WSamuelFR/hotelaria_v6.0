<?php
// Arquivo: app/models/hospedagemModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class HospedagemModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Connect();
    }

    /**
     * REGISTRA NOVA HOSPEDAGEM (CHECK-IN)
     * Agora com cálculo automático de diárias por período.
     */
    public function registrarHospedagem($dados)
    {
        $this->conn->begin_transaction();
        try {
            $titular_id = null;
            $acompanhantes_ids = [];

            if (!empty($dados['hospedes']) && is_array($dados['hospedes'])) {
                foreach ($dados['hospedes'] as $h) {
                    if ($h['tipo'] === 'Titular') {
                        $titular_id = $h['id'];
                    } else {
                        $acompanhantes_ids[] = $h['id'];
                    }
                }
            }

            if (!$titular_id) throw new Exception("Hóspede titular não encontrado.");

            // CÁLCULO DE DIÁRIAS (CONTRATADO)
            $checkin = new DateTime($dados['checkin']);
            $checkout = new DateTime($dados['checkout']);
            $totalDias = $checkin->diff($checkout)->days;
            if ($totalDias <= 0) $totalDias = 1;

            $qtdPessoas = count($dados['hospedes']);
            $precoUnitario = floatval($dados['preco_unitario'] ?? 0);

            $valorHospedagem = ($precoUnitario * $qtdPessoas) * $totalDias;
            $valorConsumo = 0.00;
            $totalDispesa = $valorHospedagem;

            $sql = "INSERT INTO hospedagem (reserva, hospedes, quarto, data_checkin, data_checkout, valor_hospedagem, valor_consumo, total_dispesa, observacoes, situacao, usuario_responsavel) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativa', ?)";

            $stmt = $this->conn->prepare($sql);
            $reserva = !empty($dados['reserva_id']) ? $dados['reserva_id'] : null;
            $obs = $dados['obs'] ?? '';
            $usuario = $dados['usuario'] ?? 'Sistema';

            $stmt->bind_param("iiissdddss", $reserva, $titular_id, $dados['quarto_id'], $dados['checkin'], $dados['checkout'], $valorHospedagem, $valorConsumo, $totalDispesa, $obs, $usuario);

            if (!$stmt->execute()) throw new Exception("Erro ao salvar hospedagem.");

            $hospedagemId = $this->conn->insert_id;

            if (!empty($acompanhantes_ids)) {
                $sqlAcomp = "INSERT INTO hospedagem_acompanhantes (hospedagem_id, cadastro_id) VALUES (?, ?)";
                $stmtA = $this->conn->prepare($sqlAcomp);
                foreach ($acompanhantes_ids as $acompId) {
                    $stmtA->bind_param("ii", $hospedagemId, $acompId);
                    $stmtA->execute();
                }
            }

            $this->conn->query("UPDATE quarto SET room_status = 'ocupado' WHERE quarto_id = " . (int)$dados['quarto_id']);

            $this->conn->commit();
            return ["success" => true, "message" => "Check-in realizado!", "id" => $hospedagemId];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * ATUALIZA HOSPEDAGEM (EDIÇÃO)
     * Recalcula o valor da hospedagem se as datas ou número de pessoas mudarem.
     */
    public function updateHospedagem($dados)
    {
        $this->conn->begin_transaction();
        try {
            $sqlBusca = "SELECT quarto, valor_consumo FROM hospedagem WHERE hospedagem_id = ?";
            $stmtB = $this->conn->prepare($sqlBusca);
            $stmtB->bind_param("i", $dados['id']);
            $stmtB->execute();
            $resOld = $stmtB->get_result()->fetch_assoc();

            $quartoAntigoId = $resOld['quarto'] ?? null;
            $valorConsumo = floatval($resOld['valor_consumo'] ?? 0);

            // --- RECALCULO DE DIÁRIAS NA EDIÇÃO ---
            $checkin = new DateTime($dados['checkin']);
            $checkout = new DateTime($dados['checkout']);
            $intervalo = $checkin->diff($checkout);
            $totalDias = $intervalo->days;
            if ($totalDias <= 0) {
                $totalDias = 1;
            }

            // O valor vindo do JS é o preço unitário por pessoa
            $precoUnitario = floatval($dados['total'] ?? 0);
            $qtdPessoas = (!empty($dados['acompanhantes']) ? count($dados['acompanhantes']) : 0) + 1;

            $valorHospedagem = ($precoUnitario * $qtdPessoas) * $totalDias;
            $totalDispesa = $valorHospedagem + $valorConsumo;
            // --------------------------------------

            $sql = "UPDATE hospedagem SET hospedes = ?, quarto = ?, data_checkin = ?, data_checkout = ?, valor_hospedagem = ?, total_dispesa = ?, observacoes = ? 
                    WHERE hospedagem_id = ?";
            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param("iissddsi", $dados['titular_id'], $dados['quarto_id'], $dados['checkin'], $dados['checkout'], $valorHospedagem, $totalDispesa, $dados['observacoes'], $dados['id']);

            if (!$stmt->execute()) throw new Exception("Falha ao atualizar.");

            if ($quartoAntigoId && (int)$quartoAntigoId !== (int)$dados['quarto_id']) {
                $this->conn->query("UPDATE quarto SET room_status = 'livre', clean_status = 'sujo' WHERE quarto_id = $quartoAntigoId");
                $this->conn->query("UPDATE quarto SET room_status = 'ocupado' WHERE quarto_id = " . (int)$dados['quarto_id']);
            }

            $this->conn->query("DELETE FROM hospedagem_acompanhantes WHERE hospedagem_id = " . (int)$dados['id']);
            if (!empty($dados['acompanhantes'])) {
                $sqlIns = "INSERT INTO hospedagem_acompanhantes (hospedagem_id, cadastro_id) VALUES (?, ?)";
                $stmtI = $this->conn->prepare($sqlIns);
                foreach ($dados['acompanhantes'] as $acompId) {
                    $stmtI->bind_param("ii", $dados['id'], $acompId);
                    $stmtI->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function getHospedagemById($id)
    {
        $sql = "SELECT h.*, c.full_name as nome_titular, c.cpf_cnpj as cpf_titular, q.numero as numero_quarto, q.room_type,
                       ((SELECT COUNT(*) FROM hospedagem_acompanhantes ha WHERE ha.hospedagem_id = h.hospedagem_id) + 1) as qtd_total_hospedes
                FROM hospedagem h
                JOIN cadastro c ON h.hospedes = c.cadastro_id
                JOIN quarto q ON h.quarto = q.quarto_id
                WHERE h.hospedagem_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $hospedagem = $stmt->get_result()->fetch_assoc();

        if ($hospedagem) {
            // LÓGICA DE OVERSTAY (Hóspede que não saiu na data)
            $dataCheckin = new DateTime($hospedagem['data_checkin']);
            $dataCheckoutPrevista = new DateTime($hospedagem['data_checkout']);
            $hoje = new DateTime(date('Y-m-d'));

            // Se hoje for maior que a saída prevista, calculamos até HOJE
            $dataParaCalculo = ($hoje > $dataCheckoutPrevista && $hospedagem['situacao'] === 'ativa')
                ? $hoje
                : $dataCheckoutPrevista;

            $diasReais = $dataCheckin->diff($dataParaCalculo)->days;
            if ($diasReais <= 0) $diasReais = 1;

            // Preço unitário por pessoa/dia (armazenado no banco)
            $qtdPessoas = (int)$hospedagem['qtd_total_hospedes'];

            // Calculamos o valor unitário original para poder multiplicar pelos novos dias
            // Se o valor_hospedagem foi gravado no checkin como (preco * pessoas * dias_previstos)
            // Precisamos saber o preço por pessoa/dia.
            $diasPrevistos = $dataCheckin->diff($dataCheckoutPrevista)->days;
            if ($diasPrevistos <= 0) $diasPrevistos = 1;

            $precoUnitarioOriginal = ($hospedagem['valor_hospedagem'] / $qtdPessoas) / $diasPrevistos;

            // Novo valor de hospedagem caso tenha passado do prazo
            $hospedagem['valor_hospedagem_atualizado'] = ($precoUnitarioOriginal * $qtdPessoas) * $diasReais;
            $hospedagem['total_dispesa_atualizado'] = $hospedagem['valor_hospedagem_atualizado'] + $hospedagem['valor_consumo'];
            $hospedagem['dias_estadia'] = $diasReais;
            $hospedagem['excedeu_prazo'] = ($hoje > $dataCheckoutPrevista);

            // Busca acompanhantes
            $sqlAcomp = "SELECT c.cadastro_id, c.full_name as nome_hospede, c.cpf_cnpj as documento 
                         FROM hospedagem_acompanhantes ha
                         JOIN cadastro c ON ha.cadastro_id = c.cadastro_id
                         WHERE ha.hospedagem_id = ?";
            $stmtA = $this->conn->prepare($sqlAcomp);
            $stmtA->bind_param("i", $id);
            $stmtA->execute();
            $hospedagem['acompanhantes'] = $stmtA->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $hospedagem;
    }

    public function getConsumoPorHospede($hospedagem_id, $hospede_id)
    {
        $sql = "SELECT hc.*, p.nome as nome_produto FROM hospedagem_consumo hc
                JOIN produto p ON hc.produto_id = p.produto_id
                WHERE hc.hospedagem_id = ? AND hc.hospede_id = ?
                ORDER BY hc.data_consumo DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $hospedagem_id, $hospede_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarConsumo($dados)
    {
        $this->conn->begin_transaction();
        try {
            $sqlConsumo = "INSERT INTO hospedagem_consumo (hospedagem_id, hospede_id, produto_id, quantidade, preco_unitario_pago) VALUES (?, ?, ?, ?, ?)";
            $stmtConsumo = $this->conn->prepare($sqlConsumo);
            $stmtConsumo->bind_param("iiiid", $dados['hospedagem_id'], $dados['hospede_id'], $dados['produto_id'], $dados['quantidade'], $dados['preco_unitario']);
            $stmtConsumo->execute();

            $this->conn->query("UPDATE produto SET estoque_atual = estoque_atual - " . (int)$dados['quantidade'] . " WHERE produto_id = " . (int)$dados['produto_id']);

            $sqlSoma = "SELECT SUM(quantidade * preco_unitario_pago) as total FROM hospedagem_consumo WHERE hospedagem_id = " . (int)$dados['hospedagem_id'];
            $totalConsumo = $this->conn->query($sqlSoma)->fetch_assoc()['total'] ?? 0;

            $this->conn->query("UPDATE hospedagem SET valor_consumo = $totalConsumo, total_dispesa = valor_hospedagem + $totalConsumo WHERE hospedagem_id = " . (int)$dados['hospedagem_id']);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function finalizarHospedagem($id)
    {
        $this->conn->begin_transaction();
        try {
            $sql = "SELECT quarto, valor_hospedagem, (SELECT SUM(quantidade * preco_unitario_pago) FROM hospedagem_consumo WHERE hospedagem_id = ?) as total_consumo 
                    FROM hospedagem WHERE hospedagem_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $id, $id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();

            $totalConsumo = floatval($res['total_consumo'] ?? 0);
            $totalGeral = floatval($res['valor_hospedagem']) + $totalConsumo;

            $this->conn->query("UPDATE hospedagem SET valor_consumo = $totalConsumo, total_dispesa = $totalGeral, situacao = 'encerrada' WHERE hospedagem_id = " . (int)$id);
            $this->conn->query("UPDATE quarto SET room_status = 'livre', clean_status = 'sujo' WHERE quarto_id = " . (int)$res['quarto']);

            $this->conn->commit();
            return ["success" => true, "message" => "Checkout realizado!"];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function getConsumoTotalHospedagem($hospedagem_id)
    {
        $sql = "SELECT hc.*, p.nome as nome_produto, c.full_name as nome_cliente FROM hospedagem_consumo hc 
                JOIN produto p ON hc.produto_id = p.produto_id 
                JOIN cadastro c ON hc.hospede_id = c.cadastro_id
                WHERE hc.hospedagem_id = ? ORDER BY hc.data_consumo DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $hospedagem_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
