<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/TaskHistory.php';

class TimelineController {
    public function index() {
        requireAuth();
        
        $taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT t.*, u.name as assigned_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.id = :id");
        $stmt->execute(['id' => $taskId]);
        $task = $stmt->fetch();
        
        if (!$task) {
            die("Tarefa não encontrada.");
        }

        $history = TaskHistory::getByTask($taskId);
        
        $users = $db->query("SELECT id, name FROM users WHERE active = 1")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/tasks/timeline.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
