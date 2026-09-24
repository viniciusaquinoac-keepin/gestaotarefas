<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/ProjectTimeline.php';

class ProjectTask {
    public static function getByProject($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pt.*, u.name as assigned_name, u.avatar_color,
                   ph.name as phase_name
            FROM project_tasks pt
            LEFT JOIN users u ON pt.assigned_to = u.id
            LEFT JOIN project_phases ph ON pt.phase_id = ph.id
            WHERE pt.project_id = :project_id
            ORDER BY pt.phase_id ASC, pt.order_num ASC, pt.id ASC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function getByPhase($phaseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pt.*, u.name as assigned_name, u.avatar_color
            FROM project_tasks pt
            LEFT JOIN users u ON pt.assigned_to = u.id
            WHERE pt.phase_id = :phase_id
            ORDER BY pt.order_num ASC, pt.id ASC
        ");
        $stmt->execute(['phase_id' => $phaseId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        
        // Calculate planned_days if dates provided
        $plannedDays = 0;
        if (!empty($data['planned_start']) && !empty($data['planned_end'])) {
            $dStart = new DateTime($data['planned_start']);
            $dEnd = new DateTime($data['planned_end']);
            $plannedDays = $dStart->diff($dEnd)->days + 1;
        }

        $stmt = $db->prepare("
            INSERT INTO project_tasks (
                phase_id, project_id, title, status, assigned_to,
                planned_start, planned_end, actual_start, actual_end,
                planned_days, actual_days, hours_estimated, hours_spent,
                delay_reason, notes, order_num
            ) VALUES (
                :phase_id, :project_id, :title, :status, :assigned_to,
                :planned_start, :planned_end, :actual_start, :actual_end,
                :planned_days, :actual_days, :hours_estimated, :hours_spent,
                :delay_reason, :notes, :order_num
            )
        ");
        
        $success = $stmt->execute([
            'phase_id' => $data['phase_id'],
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'status' => $data['status'] ?: 'pending',
            'assigned_to' => $data['assigned_to'] ?: null,
            'planned_start' => $data['planned_start'] ?: null,
            'planned_end' => $data['planned_end'] ?: null,
            'actual_start' => $data['actual_start'] ?: null,
            'actual_end' => $data['actual_end'] ?: null,
            'planned_days' => $plannedDays,
            'actual_days' => $data['actual_days'] ?: 0,
            'hours_estimated' => $data['hours_estimated'] ?: 0,
            'hours_spent' => $data['hours_spent'] ?: 0,
            'delay_reason' => $data['delay_reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'order_num' => $data['order_num'] ?: 0
        ]);

        if ($success) {
            $taskId = $db->lastInsertId();
            ProjectTimeline::add(
                $data['project_id'],
                'task_created',
                "Tarefa '{$data['title']}' criada na etapa",
                null,
                $data['title'],
                $_SESSION['user_id'] ?? 1
            );
            return $taskId;
        }
        return false;
    }

    public static function update($id, $data, $userId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();
        if (!$task) return false;

        $actualDays = $task['actual_days'];
        if (!empty($data['actual_start']) && !empty($data['actual_end'])) {
            $dStart = new DateTime($data['actual_start']);
            $dEnd = new DateTime($data['actual_end']);
            $actualDays = $dStart->diff($dEnd)->days + 1;
        }

        $plannedDays = $task['planned_days'];
        $pStart = $data['planned_start'] ?? $task['planned_start'];
        $pEnd = $data['planned_end'] ?? $task['planned_end'];
        if (!empty($pStart) && !empty($pEnd)) {
            $dStart = new DateTime($pStart);
            $dEnd = new DateTime($pEnd);
            $plannedDays = $dStart->diff($dEnd)->days + 1;
        }

        $stmtUp = $db->prepare("
            UPDATE project_tasks SET
                title = :title,
                status = :status,
                assigned_to = :assigned_to,
                planned_start = :planned_start,
                planned_end = :planned_end,
                actual_start = :actual_start,
                actual_end = :actual_end,
                planned_days = :planned_days,
                actual_days = :actual_days,
                hours_estimated = :hours_estimated,
                hours_spent = :hours_spent,
                delay_reason = :delay_reason,
                notes = :notes
            WHERE id = :id
        ");

        $res = $stmtUp->execute([
            'title' => $data['title'] ?? $task['title'],
            'status' => $data['status'] ?? $task['status'],
            'assigned_to' => isset($data['assigned_to']) ? ($data['assigned_to'] ?: null) : $task['assigned_to'],
            'planned_start' => $data['planned_start'] ?? $task['planned_start'],
            'planned_end' => $data['planned_end'] ?? $task['planned_end'],
            'actual_start' => $data['actual_start'] ?? $task['actual_start'],
            'actual_end' => $data['actual_end'] ?? $task['actual_end'],
            'planned_days' => $plannedDays,
            'actual_days' => $actualDays,
            'hours_estimated' => $data['hours_estimated'] ?? $task['hours_estimated'],
            'hours_spent' => $data['hours_spent'] ?? $task['hours_spent'],
            'delay_reason' => $data['delay_reason'] ?? $task['delay_reason'],
            'notes' => $data['notes'] ?? $task['notes'],
            'id' => $id
        ]);

        if ($res && isset($data['status']) && $data['status'] !== $task['status']) {
            ProjectTimeline::add(
                $task['project_id'],
                'task_status_changed',
                "Status da tarefa '{$task['title']}' alterado para {$data['status']}" . (!empty($data['delay_reason']) ? " (Atraso: {$data['delay_reason']})" : ""),
                $task['status'],
                $data['status'],
                $userId
            );
        }

        return $res;
    }

    public static function delete($id, $userId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM project_tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();
        if ($task) {
            $res = $db->prepare("DELETE FROM project_tasks WHERE id = :id")->execute(['id' => $id]);
            if ($res) {
                ProjectTimeline::add($task['project_id'], 'task_deleted', "Tarefa excluída: {$task['title']}", null, null, $userId);
            }
            return $res;
        }
        return false;
    }
}
