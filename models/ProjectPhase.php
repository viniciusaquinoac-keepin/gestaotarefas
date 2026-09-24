<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/ProjectTimeline.php';
require_once __DIR__ . '/ProjectTask.php';

class ProjectPhase {
    public static function getByProject($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ph.*,
                   (SELECT COUNT(*) FROM project_tasks pt WHERE pt.phase_id = ph.id) as total_tasks,
                   (SELECT COUNT(*) FROM project_tasks pt2 WHERE pt2.phase_id = ph.id AND pt2.status = 'completed') as completed_tasks,
                   (SELECT SUM(hours_spent) FROM project_tasks pt3 WHERE pt3.phase_id = ph.id) as total_hours_spent,
                   (SELECT SUM(hours_estimated) FROM project_tasks pt4 WHERE pt4.phase_id = ph.id) as total_hours_estimated
            FROM project_phases ph
            WHERE ph.project_id = :project_id
            ORDER BY ph.order_num ASC, ph.id ASC
        ");
        $stmt->execute(['project_id' => $projectId]);
        $phases = $stmt->fetchAll();

        foreach ($phases as &$phase) {
            $phase['tasks'] = ProjectTask::getByPhase($phase['id']);
            // Recalculate status and progress dynamically if has tasks
            if ($phase['total_tasks'] > 0) {
                $phase['progress_pct'] = round(($phase['completed_tasks'] / $phase['total_tasks']) * 100);
            } else {
                $phase['progress_pct'] = ($phase['status'] === 'completed') ? 100 : (($phase['status'] === 'in_progress') ? 50 : 0);
            }

            // Check if delayed
            $phase['is_delayed'] = false;
            if ($phase['status'] !== 'completed' && !empty($phase['planned_end'])) {
                if (date('Y-m-d') > $phase['planned_end']) {
                    $phase['is_delayed'] = true;
                }
            }
        }

        return $phases;
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO project_phases (
                project_id, name, order_num, status, planned_start, planned_end,
                actual_start, actual_end, delay_reason, sector, observations, is_client_visible
            ) VALUES (
                :project_id, :name, :order_num, :status, :planned_start, :planned_end,
                :actual_start, :actual_end, :delay_reason, :sector, :observations, :is_client_visible
            )
        ");
        $success = $stmt->execute([
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'order_num' => !empty($data['order_num']) ? (int)$data['order_num'] : 0,
            'status' => !empty($data['status']) ? $data['status'] : 'not_started',
            'planned_start' => !empty($data['planned_start']) ? $data['planned_start'] : null,
            'planned_end' => !empty($data['planned_end']) ? $data['planned_end'] : null,
            'actual_start' => !empty($data['actual_start']) ? $data['actual_start'] : null,
            'actual_end' => !empty($data['actual_end']) ? $data['actual_end'] : null,
            'delay_reason' => $data['delay_reason'] ?? null,
            'sector' => $data['sector'] ?? null,
            'observations' => $data['observations'] ?? null,
            'is_client_visible' => isset($data['is_client_visible']) ? (int)$data['is_client_visible'] : 1
        ]);

        if ($success) {
            $phaseId = $db->lastInsertId();
            ProjectTimeline::add($data['project_id'], 'phase_created', "Etapa criada: {$data['name']}", null, $data['name'], $_SESSION['user_id'] ?? 1);
            return $phaseId;
        }
        return false;
    }

    public static function update($id, $data, $userId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_phases WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $phase = $stmt->fetch();
        if (!$phase) return false;

        $stmtUp = $db->prepare("
            UPDATE project_phases SET
                name = :name,
                status = :status,
                planned_start = :planned_start,
                planned_end = :planned_end,
                actual_start = :actual_start,
                actual_end = :actual_end,
                delay_reason = :delay_reason,
                sector = :sector,
                observations = :observations,
                is_client_visible = :is_client_visible,
                order_num = :order_num
            WHERE id = :id
        ");

        $res = $stmtUp->execute([
            'name' => $data['name'] ?? $phase['name'],
            'status' => $data['status'] ?? $phase['status'],
            'planned_start' => $data['planned_start'] ?? $phase['planned_start'],
            'planned_end' => $data['planned_end'] ?? $phase['planned_end'],
            'actual_start' => $data['actual_start'] ?? $phase['actual_start'],
            'actual_end' => $data['actual_end'] ?? $phase['actual_end'],
            'delay_reason' => $data['delay_reason'] ?? $phase['delay_reason'],
            'sector' => $data['sector'] ?? $phase['sector'],
            'observations' => $data['observations'] ?? $phase['observations'],
            'is_client_visible' => isset($data['is_client_visible']) ? (int)$data['is_client_visible'] : $phase['is_client_visible'],
            'order_num' => $data['order_num'] ?? $phase['order_num'],
            'id' => $id
        ]);

        if ($res && isset($data['status']) && $data['status'] !== $phase['status']) {
            ProjectTimeline::add(
                $phase['project_id'],
                'phase_status_changed',
                "Etapa '{$phase['name']}' alterada para {$data['status']}",
                $phase['status'],
                $data['status'],
                $userId
            );
        }

        return $res;
    }

    public static function delete($id, $userId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_phases WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $phase = $stmt->fetch();
        if ($phase) {
            $res = $db->prepare("DELETE FROM project_phases WHERE id = :id")->execute(['id' => $id]);
            if ($res) {
                ProjectTimeline::add($phase['project_id'], 'phase_deleted', "Etapa excluída: {$phase['name']}", null, null, $userId);
            }
            return $res;
        }
        return false;
    }
}
