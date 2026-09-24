<?php
require_once __DIR__ . '/../database.php';

class Suggestion {
    public static function getAll() {
        $db = Database::getConnection();
        return $db->query("
            SELECT s.*, u.name as created_name, t.title as task_title
            FROM suggestions s
            JOIN users u ON s.created_by = u.id
            LEFT JOIN tasks t ON s.task_id = t.id
            ORDER BY s.created_at DESC
        ")->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO suggestions (title, description, product, created_by)
            VALUES (:title, :description, :product, :created_by)
        ");
        return $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'product' => $data['product'],
            'created_by' => $data['created_by']
        ]);
    }

    public static function updateStatus($id, $status, $taskId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE suggestions SET status = :status, task_id = COALESCE(:task_id, task_id), updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([
            'status' => $status,
            'task_id' => $taskId,
            'id' => $id
        ]);
    }
}
