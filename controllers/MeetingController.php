<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Meeting.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class MeetingController {
    public function index() {
        requireAuth();
        
        $meetings = Meeting::getAll();
        
        // Buscar tarefas atrasadas para exibir na criação da reunião
        $db = Database::getConnection();
        $delayedTasks = $db->query("
            SELECT t.*, u.name as assigned_name 
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.id 
            WHERE t.status != 'done' AND t.due_date < date('now')
            ORDER BY t.due_date ASC
        ")->fetchAll();

        $users = $db->query("SELECT id, name FROM users WHERE active = 1")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/meetings/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'date' => $_POST['date'],
                'notes' => $_POST['notes'],
                'participants' => isset($_POST['participants']) ? implode(',', $_POST['participants']) : '',
                'created_by' => $_SESSION['user_id']
            ];
            Meeting::create($data);
            header('Location: ' . BASE_URL . '/?page=meetings');
            exit;
        }
    }
}
