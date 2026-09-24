<?php
require_once __DIR__ . '/../database.php';

class Task {
    public static function getAll() {
        $db = Database::getConnection();
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();
        
        $dateFilter = "WHERE (DATE(t.created_at) >= :start OR DATE(t.due_date) >= :start OR DATE(t.completed_at) >= :start)";
        if (!empty($end)) {
            $dateFilter = "WHERE ((DATE(t.created_at) >= :start AND DATE(t.created_at) <= :end) OR (DATE(t.due_date) >= :start AND DATE(t.due_date) <= :end) OR (DATE(t.completed_at) >= :start AND DATE(t.completed_at) <= :end))";
        }

        $stmt = $db->prepare("
            SELECT t.*, u.name as assigned_name, u.avatar_color,
                   (SELECT GROUP_CONCAT(DISTINCT mu.id || '::' || mu.name || '::' || mu.avatar_color) FROM task_history th JOIN users mu ON th.mentioned_user_id = mu.id WHERE th.task_id = t.id AND th.mentioned_user_id IS NOT NULL) as mentioned_users_data,
                   (SELECT COUNT(*) FROM task_history th2 WHERE th2.task_id = t.id AND th2.action = 'postponed') as postpone_count
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.id 
            $dateFilter
            ORDER BY t.position ASC, t.id DESC
        ");
        
        $params = ['start' => $start];
        if (!empty($end)) $params['end'] = $end;
        
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        return self::processMentions($tasks);
    }

    public static function getByStatus($status) {
        $db = Database::getConnection();
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();
        
        $dateFilter = "AND (DATE(t.created_at) >= :start OR DATE(t.due_date) >= :start OR DATE(t.completed_at) >= :start)";
        if (!empty($end)) {
            $dateFilter = "AND ((DATE(t.created_at) >= :start AND DATE(t.created_at) <= :end) OR (DATE(t.due_date) >= :start AND DATE(t.due_date) <= :end) OR (DATE(t.completed_at) >= :start AND DATE(t.completed_at) <= :end))";
        }

        $stmt = $db->prepare("
            SELECT t.*, u.name as assigned_name, u.avatar_color,
                   (SELECT GROUP_CONCAT(DISTINCT mu.id || '::' || mu.name || '::' || mu.avatar_color) FROM task_history th JOIN users mu ON th.mentioned_user_id = mu.id WHERE th.task_id = t.id AND th.mentioned_user_id IS NOT NULL) as mentioned_users_data,
                   (SELECT COUNT(*) FROM task_history th2 WHERE th2.task_id = t.id AND th2.action = 'postponed') as postpone_count
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.id 
            WHERE t.status = :status 
            $dateFilter
            ORDER BY t.position ASC, t.id DESC
        ");
        
        $params = ['status' => $status, 'start' => $start];
        if (!empty($end)) $params['end'] = $end;
        
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        return self::processMentions($tasks);
    }
    
    private static function processMentions($tasks) {
        foreach ($tasks as &$task) {
            $task['mentions'] = [];
            $task['mentioned_user_id'] = '';
            if (!empty($task['mentioned_users_data'])) {
                $mentionsStr = explode(',', $task['mentioned_users_data']);
                $ids = [];
                foreach ($mentionsStr as $mStr) {
                    $parts = explode('::', $mStr);
                    if (count($parts) === 3) {
                        $task['mentions'][] = [
                            'id' => $parts[0],
                            'name' => $parts[1],
                            'avatar_color' => $parts[2]
                        ];
                        $ids[] = $parts[0];
                    }
                }
                $task['mentioned_user_id'] = implode(',', $ids);
            }
        }
        return $tasks;
    }

    public static function updateStatus($taskId, $newStatus, $newPosition, $userId = null) {
        $db = Database::getConnection();
        
        // Obter status antigo para histórico
        $stmt = $db->prepare("SELECT status FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $taskId]);
        $oldStatus = $stmt->fetchColumn();

        $completedSet = "";
        if ($newStatus === 'done' && $oldStatus !== 'done') {
            $completedSet = ", completed_at = CURRENT_TIMESTAMP";
        } elseif ($newStatus !== 'done' && $oldStatus === 'done') {
            $completedSet = ", completed_at = NULL";
        }

        $stmt = $db->prepare("UPDATE tasks SET status = :status, position = :position, updated_at = CURRENT_TIMESTAMP $completedSet WHERE id = :id");
        $success = $stmt->execute([
            'status' => $newStatus,
            'position' => $newPosition,
            'id' => $taskId
        ]);

        if ($success && $oldStatus !== $newStatus && $userId) {
            require_once __DIR__ . '/TaskHistory.php';
            TaskHistory::add($taskId, 'status_change', $oldStatus, $newStatus, 'Movido no Kanban', $userId);
        }

        return $success;
    }
    
    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO tasks (title, description, department, priority, assigned_to, created_by, due_date, original_due_date, status)
            VALUES (:title, :description, :department, :priority, :assigned_to, :created_by, :due_date, :due_date, 'todo')
        ");
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'department' => $data['department'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'created_by' => $data['created_by'],
            'due_date' => $data['due_date']
        ]);
        return $db->lastInsertId();
    }

    public static function postpone($taskId, $newDate, $comment, $userId, $categoriaMotivo = 'Cliente/Planta') {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT due_date FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $taskId]);
        $oldDate = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE tasks SET due_date = :due_date, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute([
            'due_date' => $newDate,
            'id' => $taskId
        ]);

        if ($success) {
            $abono = ($categoriaMotivo === 'Cliente/Planta') ? 1 : 0;
            $abonoLabel = $abono ? " [Abono SLA Concedido - Motivo: Cliente/Planta]" : " [Impacta SLA - Motivo: {$categoriaMotivo}]";
            $fullComment = $comment . $abonoLabel;

            require_once __DIR__ . '/TaskHistory.php';
            TaskHistory::add($taskId, 'postponed', $oldDate, $newDate, $fullComment, $userId);

            require_once __DIR__ . '/ProrrogacaoTarefa.php';
            ProrrogacaoTarefa::add($taskId, $userId, $oldDate, $newDate, $categoriaMotivo, $comment);
        }

        return $success;
    }

    public static function delete($taskId) {
        $db = Database::getConnection();
        $db->prepare("DELETE FROM task_history WHERE task_id = :id")->execute(['id' => $taskId]);
        return $db->prepare("DELETE FROM tasks WHERE id = :id")->execute(['id' => $taskId]);
    }
}
