<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Suggestion.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class SuggestionController {
    public function index() {
        requireAuth();
        $suggestions = Suggestion::getAll();
        
        $db = Database::getConnection();
        $users = $db->query("SELECT id, name FROM users WHERE active = 1")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/suggestions/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Suggestion::create([
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'product' => $_POST['product'],
                'created_by' => $_SESSION['user_id']
            ]);
            header('Location: ' . BASE_URL . '/?page=suggestions');
            exit;
        }
    }

    public function change_status() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            if (in_array($status, ['rejected', 'postponed', 'analysis'])) {
                Suggestion::updateStatus($id, $status);
            }
            header('Location: ' . BASE_URL . '/?page=suggestions');
            exit;
        }
    }

    public function accept() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['suggestion_id'];
            
            $taskId = Task::create([
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'department' => $_POST['department'],
                'priority' => $_POST['priority'],
                'assigned_to' => $_POST['assigned_to'] ?: null,
                'created_by' => $_SESSION['user_id'],
                'due_date' => $_POST['due_date']
            ]);

            if ($taskId) {
                Suggestion::updateStatus($id, 'accepted', $taskId);
            }
            
            header('Location: ' . BASE_URL . '/?page=suggestions');
            exit;
        }
    }
}
