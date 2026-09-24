<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/ProjectTimeline.php';

class ProjectPurchase {
    public static function getByProject($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pp.*, u.name as requested_by_name,
                   ph.name as phase_name, pt.title as task_title
            FROM project_purchases pp
            LEFT JOIN users u ON pp.requested_by = u.id
            LEFT JOIN project_phases ph ON pp.phase_id = ph.id
            LEFT JOIN project_tasks pt ON pp.task_id = pt.id
            WHERE pp.project_id = :project_id
            ORDER BY pp.id DESC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO project_purchases (
                project_id, phase_id, task_id, item_description, supplier,
                estimated_cost, actual_cost, status, delay_reason,
                request_date, expected_date, actual_date, requested_by
            ) VALUES (
                :project_id, :phase_id, :task_id, :item_description, :supplier,
                :estimated_cost, :actual_cost, :status, :delay_reason,
                :request_date, :expected_date, :actual_date, :requested_by
            )
        ");
        $success = $stmt->execute([
            'project_id' => $data['project_id'],
            'phase_id' => $data['phase_id'] ?: null,
            'task_id' => $data['task_id'] ?: null,
            'item_description' => $data['item_description'],
            'supplier' => $data['supplier'] ?? null,
            'estimated_cost' => $data['estimated_cost'] ?: 0,
            'actual_cost' => $data['actual_cost'] ?: 0,
            'status' => $data['status'] ?: 'requested',
            'delay_reason' => $data['delay_reason'] ?? null,
            'request_date' => $data['request_date'] ?: date('Y-m-d'),
            'expected_date' => $data['expected_date'] ?: null,
            'actual_date' => $data['actual_date'] ?: null,
            'requested_by' => $data['requested_by']
        ]);

        if ($success) {
            $purchaseId = $db->lastInsertId();
            ProjectTimeline::add(
                $data['project_id'],
                'purchase_added',
                "Compra solicitada: {$data['item_description']}",
                null,
                $data['status'],
                $data['requested_by']
            );
            return $purchaseId;
        }
        return false;
    }

    public static function updateStatus($id, $newStatus, $delayReason = null, $actualCost = null, $actualDate = null, $userId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_purchases WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $purchase = $stmt->fetch();

        if (!$purchase) return false;

        $updates = ["status = :status"];
        $params = ['status' => $newStatus, 'id' => $id];

        if ($delayReason !== null) {
            $updates[] = "delay_reason = :delay_reason";
            $params['delay_reason'] = $delayReason;
        }
        if ($actualCost !== null && $actualCost > 0) {
            $updates[] = "actual_cost = :actual_cost";
            $params['actual_cost'] = $actualCost;
        }
        if ($newStatus === 'delivered') {
            $updates[] = "actual_date = :actual_date";
            $params['actual_date'] = $actualDate ?: date('Y-m-d');
        }

        $sql = "UPDATE project_purchases SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmtUp = $db->prepare($sql);
        $res = $stmtUp->execute($params);

        if ($res) {
            ProjectTimeline::add(
                $purchase['project_id'],
                'purchase_status_changed',
                "Status da compra '{$purchase['item_description']}' alterado para " . self::getStatusLabel($newStatus) . ($delayReason ? " (Motivo do atraso: {$delayReason})" : ""),
                $purchase['status'],
                $newStatus,
                $userId
            );
        }
        return $res;
    }

    public static function delete($id, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_purchases WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $purchase = $stmt->fetch();

        if ($purchase) {
            $res = $db->prepare("DELETE FROM project_purchases WHERE id = :id")->execute(['id' => $id]);
            if ($res) {
                ProjectTimeline::add(
                    $purchase['project_id'],
                    'purchase_deleted',
                    "Compra removida: {$purchase['item_description']}",
                    null, null, $userId
                );
            }
            return $res;
        }
        return false;
    }

    public static function getStatusLabel($status) {
        $labels = [
            'requested' => 'Solicitada',
            'quoted' => 'Cotada',
            'purchased' => 'Comprada',
            'delivered' => 'Entregue'
        ];
        return $labels[$status] ?? $status;
    }
}
