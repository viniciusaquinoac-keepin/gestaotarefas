<?php
require_once __DIR__ . '/../database.php';

class VisitaKeepin {
    public static function getAll($vendedorId = null, $startDate = null, $endDate = null) {
        $db = Database::getConnection();
        $sql = "
            SELECT v.*, u.name as vendedor_nome, u.avatar_color as vendedor_avatar
            FROM visitas_campo_keepin v
            LEFT JOIN users u ON v.vendedor_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($vendedorId) {
            $sql .= " AND v.vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendedorId;
        }
        if ($startDate && $endDate) {
            $sql .= " AND DATE(v.data_visita) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        $sql .= " ORDER BY v.data_visita DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO visitas_campo_keepin (
                vendedor_id, estabelecimento_nome, contato_abordado, telefone, segmento,
                observacao, interesse_placa, interesse_kpremote, data_visita
            ) VALUES (
                :vendedor_id, :estabelecimento_nome, :contato_abordado, :telefone, :segmento,
                :observacao, :interesse_placa, :interesse_kpremote, :data_visita
            )
        ");
        $stmt->execute([
            'vendedor_id' => $data['vendedor_id'],
            'estabelecimento_nome' => $data['estabelecimento_nome'],
            'contato_abordado' => $data['contato_abordado'] ?? '',
            'telefone' => $data['telefone'] ?? '',
            'segmento' => $data['segmento'] ?? 'Supermercado',
            'observacao' => $data['observacao'] ?? '',
            'interesse_placa' => !empty($data['interesse_placa']) ? 1 : 0,
            'interesse_kpremote' => !empty($data['interesse_kpremote']) ? 1 : 0,
            'data_visita' => !empty($data['data_visita']) ? $data['data_visita'] : date('Y-m-d H:i:s')
        ]);
        return $db->lastInsertId();
    }

    public static function countByUserAndPeriod($vendedorId, $startDate, $endDate) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM visitas_campo_keepin
            WHERE vendedor_id = :vendedor_id
              AND DATE(data_visita) BETWEEN :start_date AND :end_date
        ");
        $stmt->execute([
            'vendedor_id' => $vendedorId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return (int)$stmt->fetchColumn();
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM visitas_campo_keepin WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
