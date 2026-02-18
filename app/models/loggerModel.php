<?php
// Arquivo: app/models/loggerModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class loggerModel
{
    /**
     * Registra uma ação no banco de dados para fins de auditoria.
     */
    public static function registrar($usuario_id, $acao, $detalhes)
    {
        try {
            $conn = Connect();
            
            // AJUSTE: Se o ID for 0 (não logado), tentamos passar NULL ou manter 0 
            // conforme a regra de integridade do seu banco.
            $id_para_log = ($usuario_id > 0) ? $usuario_id : null;

            $sql = "INSERT INTO sistema_logs (cadastro_id, acao, detalhes, ip_origem) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            // "i" para integer, mas se for null o MySQL trata corretamente
            $stmt->bind_param("isss", $id_para_log, $acao, $detalhes, $ip);
            
            $stmt->execute();
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            // Silencioso para o usuário, mas loga no erro do servidor
            error_log("Falha ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Recupera a lista de logs do sistema com o nome do usuário
     */
    public static function listarTodos()
    {
        try {
            $conn = Connect();
            // O LEFT JOIN aqui já está correto, pois se o cadastro_id for NULL (auto-cadastro),
            // ele trará o log mesmo sem encontrar um nome na tabela cadastro.
            $sql = "SELECT l.*, c.full_name as nome_usuario 
                FROM sistema_logs l
                LEFT JOIN cadastro c ON l.cadastro_id = c.cadastro_id
                ORDER BY l.created_at DESC LIMIT 100";

            $result = $conn->query($sql);
            $logs = $result->fetch_all(MYSQLI_ASSOC);

            $conn->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Erro ao listar logs: " . $e->getMessage());
            return [];
        }
    }
}