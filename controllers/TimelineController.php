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

        // Restrição de acesso para usuário de nível comum
        if (!isAdmin() && (int)$task['assigned_to'] !== (int)$_SESSION['user_id'] && (int)$task['created_by'] !== (int)$_SESSION['user_id']) {
            die("Acesso restrito. Você só tem permissão para visualizar tarefas do seu próprio usuário.");
        }

        $history = TaskHistory::getByTask($taskId);
        
        if (isAdmin()) {
            $users = $db->query("SELECT id, name FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll();
        } else {
            $users = $db->query("SELECT id, name FROM users WHERE id = " . (int)$_SESSION['user_id'])->fetchAll();
        }

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/tasks/timeline.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
