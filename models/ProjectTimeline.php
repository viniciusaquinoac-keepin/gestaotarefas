<?php
require_once __DIR__ . '/../database.php';

class ProjectTimeline {
    public static function add($projectId, $action, $details = null, $oldValue = null, $newValue = null, $userId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO project_timeline (project_id, action, details, old_value, new_value, changed_by)
            VALUES (:project_id, :action, :details, :old_value, :new_value, :changed_by)
        ");
        return $stmt->execute([
            'project_id' => $projectId,
            'action' => $action,
            'details' => $details,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $userId ?: ($_SESSION['user_id'] ?? 1)
        ]);
    }

    public static function getByProject($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pt.*, u.name as user_name, u.avatar_color
            FROM project_timeline pt
            LEFT JOIN users u ON pt.changed_by = u.id
            WHERE pt.project_id = :project_id
            ORDER BY pt.id DESC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }
}
