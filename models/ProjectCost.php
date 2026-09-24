<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/ProjectTimeline.php';

class ProjectCost {
    public static function getByProject($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pc.*, u.name as created_by_name
            FROM project_costs pc
            LEFT JOIN users u ON pc.created_by = u.id
            WHERE pc.project_id = :project_id
            ORDER BY pc.cost_date DESC, pc.id DESC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO project_costs (project_id, category, description, amount, cost_date, receipt_note, created_by)
            VALUES (:project_id, :category, :description, :amount, :cost_date, :receipt_note, :created_by)
        ");
        $success = $stmt->execute([
            'project_id' => $data['project_id'],
            'category' => $data['category'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'cost_date' => $data['cost_date'] ?: date('Y-m-d'),
            'receipt_note' => $data['receipt_note'] ?? null,
            'created_by' => $data['created_by']
        ]);

        if ($success) {
            $costId = $db->lastInsertId();
            ProjectTimeline::add(
                $data['project_id'],
                'cost_added',
                "Custo lançado: {$data['description']} (R$ " . number_format($data['amount'], 2, ',', '.') . ") na categoria {$data['category']}",
                null,
                $data['amount'],
                $data['created_by']
            );
            return $costId;
        }
        return false;
    }

    public static function delete($id, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_costs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $cost = $stmt->fetch();

        if ($cost) {
            $stmtDel = $db->prepare("DELETE FROM project_costs WHERE id = :id");
            $res = $stmtDel->execute(['id' => $id]);
            if ($res) {
                ProjectTimeline::add(
                    $cost['project_id'],
                    'cost_deleted',
                    "Custo removido: {$cost['description']} (R$ " . number_format($cost['amount'], 2, ',', '.') . ")",
                    $cost['amount'],
                    null,
                    $userId
                );
            }
            return $res;
        }
        return false;
    }

    public static function getTotalByProject($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT SUM(amount) FROM project_costs WHERE project_id = :project_id");
        $stmt->execute(['project_id' => $projectId]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public static function getCategoryBreakdown($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT category, SUM(amount) as total, COUNT(*) as count
            FROM project_costs
            WHERE project_id = :project_id
            GROUP BY category
            ORDER BY total DESC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }
}
