<?php
require_once __DIR__ . '/../database.php';

class ProrrogacaoTarefa {
    public static function getByTaskId($taskId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, u.name as user_name
            FROM prorrogacoes_tarefas p
            LEFT JOIN users u ON p.usuario_id = u.id
            WHERE p.tarefa_id = :tarefa_id
            ORDER BY p.data_solicitacao DESC
        ");
        $stmt->execute(['tarefa_id' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($taskId, $userId, $oldDate, $newDate, $categoriaMotivo, $justificativa) {
        $db = Database::getConnection();
        $abono = ($categoriaMotivo === 'Cliente/Planta') ? 1 : 0;
        $stmt = $db->prepare("
            INSERT INTO prorrogacoes_tarefas (tarefa_id, usuario_id, data_vencimento_anterior, nova_data_vencimento, categoria_motivo, justificativa, abono_penalidade)
            VALUES (:tarefa_id, :usuario_id, :old_date, :new_date, :categoria, :justificativa, :abono)
        ");
        return $stmt->execute([
            'tarefa_id' => $taskId,
            'usuario_id' => $userId,
            'old_date' => $oldDate,
            'new_date' => $newDate,
            'categoria' => $categoriaMotivo,
            'justificativa' => $justificativa,
            'abono' => $abono
        ]);
    }

    public static function getStatsByUserAndPeriod($userId, $startDate, $endDate) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_prorrogacoes,
                SUM(CASE WHEN abono_penalidade = 1 THEN 1 ELSE 0 END) as abonadas,
                SUM(CASE WHEN abono_penalidade = 0 THEN 1 ELSE 0 END) as penalizadas
            FROM prorrogacoes_tarefas
            WHERE usuario_id = :user_id
              AND DATE(data_solicitacao) BETWEEN :start AND :end
        ");
        $stmt->execute([
            'user_id' => $userId,
            'start' => $startDate,
            'end' => $endDate
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getStatsByPeriod($startDate, $endDate) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_prorrogacoes,
                SUM(CASE WHEN abono_penalidade = 1 THEN 1 ELSE 0 END) as abonadas,
                SUM(CASE WHEN abono_penalidade = 0 THEN 1 ELSE 0 END) as penalizadas
            FROM prorrogacoes_tarefas
            WHERE DATE(data_solicitacao) BETWEEN :start AND :end
        ");
        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_prorrogacoes' => 0, 'abonadas' => 0, 'penalizadas' => 0];
    }
}
