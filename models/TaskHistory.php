<?php
require_once __DIR__ . '/../database.php';

class TaskHistory {
    public static function add($taskId, $action, $oldValue, $newValue, $comment, $changedBy, $mentionedUserId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO task_history (task_id, action, old_value, new_value, comment, changed_by, mentioned_user_id)
            VALUES (:task_id, :action, :old_value, :new_value, :comment, :changed_by, :mentioned_user_id)
        ");
        return $stmt->execute([
            'task_id' => $taskId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'comment' => $comment,
            'changed_by' => $changedBy,
            'mentioned_user_id' => $mentionedUserId
        ]);
    }

    public static function getByTask($taskId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT th.*, u.name as user_name, mu.name as mentioned_user_name, mu.avatar_color as mentioned_avatar_color 
            FROM task_history th
            LEFT JOIN users u ON th.changed_by = u.id
            LEFT JOIN users mu ON th.mentioned_user_id = mu.id
            WHERE th.task_id = :task_id
            ORDER BY th.created_at ASC
        ");
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }
    public static function delete($historyId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM task_history WHERE id = :id AND action = 'comment'");
        return $stmt->execute(['id' => $historyId]);
    }
}
