<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class TaskController {
    public function index() {
        requireAuth();
        
        $tasksTodo = Task::getByStatus('todo');
        $tasksInProgress = Task::getByStatus('in_progress');
        $tasksReview = Task::getByStatus('review');
        $tasksDone = Task::getByStatus('done');

        // Buscar usuários para o formulário de nova tarefa
        $db = Database::getConnection();
        $users = $db->query("SELECT id, name FROM users WHERE active = 1")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/tasks/board.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'department' => $_POST['department'],
                'priority' => $_POST['priority'],
                'assigned_to' => $_POST['assigned_to'] ?: null,
                'due_date' => $_POST['due_date'],
                'created_by' => $_SESSION['user_id']
            ];
            Task::create($data);
            header('Location: ' . BASE_URL . '/?page=tasks');
            exit;
        }
    }

    public function update_status() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'];
            $newStatus = $_POST['status'];
            $newPosition = (int)$_POST['position'];
            $userId = $_SESSION['user_id'];
            
            Task::updateStatus($taskId, $newStatus, $newPosition, $userId);
            
            // Retornar JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }

    public function postpone() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'];
            $newDate = $_POST['due_date'];
            $comment = $_POST['comment'];
            $userId = $_SESSION['user_id'];
            
            Task::postpone($taskId, $newDate, $comment, $userId);
            header('Location: ' . BASE_URL . '/?page=timeline&id=' . $taskId);
            exit;
        }
    }

    public function delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'];
            Task::delete($taskId);
            header('Location: ' . BASE_URL . '/?page=tasks');
            exit;
        }
    }

    public function add_comment() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'];
            $comment = $_POST['comment'];
            $mentionedUserId = !empty($_POST['mentioned_user_id']) ? $_POST['mentioned_user_id'] : null;
            $userId = $_SESSION['user_id'];
            
            require_once __DIR__ . '/../models/TaskHistory.php';
            TaskHistory::add($taskId, 'comment', null, null, $comment, $userId, $mentionedUserId);
            
            header('Location: ' . BASE_URL . '/?page=timeline&id=' . $taskId);
            exit;
        }
    }
    public function delete_comment() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'];
            $historyId = $_POST['history_id'];
            
            require_once __DIR__ . '/../models/TaskHistory.php';
            TaskHistory::delete($historyId);
            
            header('Location: ' . BASE_URL . '/?page=timeline&id=' . $taskId);
            exit;
        }
    }
}
