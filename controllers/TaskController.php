<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class TaskController {
    public function index() {
        requireAuth();
        
        $userId = !isAdmin() ? (int)$_SESSION['user_id'] : null;

        $tasksTodo = Task::getByStatus('todo', $userId);
        $tasksInProgress = Task::getByStatus('in_progress', $userId);
        $tasksReview = Task::getByStatus('review', $userId);
        $tasksDone = Task::getByStatus('done', $userId);

        // Buscar usuários para o formulário de nova tarefa e filtro
        $db = Database::getConnection();
        if (isAdmin()) {
            $users = $db->query("SELECT id, name FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll();
        } else {
            $users = $db->query("SELECT id, name FROM users WHERE id = " . (int)$_SESSION['user_id'])->fetchAll();
        }

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/tasks/board.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_SESSION['user_id'];
            // Usuário comum cria tarefas atribuídas obrigatoriamente a si mesmo
            $assignedTo = !isAdmin() ? $userId : (!empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null);

            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'department' => $_POST['department'],
                'priority' => $_POST['priority'],
                'assigned_to' => $assignedTo,
                'due_date' => $_POST['due_date'],
                'created_by' => $userId
            ];
            Task::create($data);
            header('Location: ' . BASE_URL . '/?page=tasks');
            exit;
        }
    }

    public function update_status() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = (int)$_POST['task_id'];
            $newStatus = $_POST['status'];
            $newPosition = (int)$_POST['position'];
            $userId = (int)$_SESSION['user_id'];

            $task = Task::getById($taskId);
            if (!$task) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Tarefa não encontrada']);
                exit;
            }

            // Controle de Acesso: Apenas admin ou o próprio responsável/criador
            if (!isAdmin() && (int)$task['assigned_to'] !== $userId && (int)$task['created_by'] !== $userId) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Acesso negado. Você só tem permissão para gerenciar suas próprias tarefas.']);
                exit;
            }
            
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
            $taskId = (int)$_POST['task_id'];
            $newDate = $_POST['due_date'];
            $comment = $_POST['comment'] ?? '';
            $categoriaMotivo = $_POST['categoria_motivo'] ?? 'Cliente/Planta';
            $userId = (int)$_SESSION['user_id'];

            $task = Task::getById($taskId);
            if (!$task) {
                die("Tarefa não encontrada.");
            }

            if (!isAdmin() && (int)$task['assigned_to'] !== $userId && (int)$task['created_by'] !== $userId) {
                die("Acesso negado. Você só tem permissão para prorrogar tarefas do seu próprio usuário.");
            }
            
            Task::postpone($taskId, $newDate, $comment, $userId, $categoriaMotivo);
            header('Location: ' . BASE_URL . '/?page=timeline&id=' . $taskId);
            exit;
        }
    }

    public function delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = (int)$_POST['task_id'];
            $userId = (int)$_SESSION['user_id'];

            $task = Task::getById($taskId);
            if (!$task) {
                die("Tarefa não encontrada.");
            }

            if (!isAdmin() && (int)$task['created_by'] !== $userId) {
                die("Acesso negado. Apenas administradores ou o criador da tarefa podem excluí-la.");
            }

            Task::delete($taskId);
            header('Location: ' . BASE_URL . '/?page=tasks');
            exit;
        }
    }

    public function add_comment() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = (int)$_POST['task_id'];
            $comment = $_POST['comment'];
            $mentionedUserId = !empty($_POST['mentioned_user_id']) ? $_POST['mentioned_user_id'] : null;
            $userId = (int)$_SESSION['user_id'];

            $task = Task::getById($taskId);
            if (!$task) {
                die("Tarefa não encontrada.");
            }

            if (!isAdmin() && (int)$task['assigned_to'] !== $userId && (int)$task['created_by'] !== $userId) {
                die("Acesso negado. Você só tem permissão para interagir com tarefas do seu próprio usuário.");
            }
            
            require_once __DIR__ . '/../models/TaskHistory.php';
            TaskHistory::add($taskId, 'comment', null, null, $comment, $userId, $mentionedUserId);
            
            header('Location: ' . BASE_URL . '/?page=timeline&id=' . $taskId);
            exit;
        }
    }

    public function delete_comment() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = (int)$_POST['task_id'];
            $historyId = (int)$_POST['history_id'];
            $userId = (int)$_SESSION['user_id'];

            $task = Task::getById($taskId);
            if (!$task) {
                die("Tarefa não encontrada.");
            }

            if (!isAdmin() && (int)$task['assigned_to'] !== $userId && (int)$task['created_by'] !== $userId) {
                die("Acesso negado.");
            }
            
            require_once __DIR__ . '/../models/TaskHistory.php';
            TaskHistory::delete($historyId);
            
            header('Location: ' . BASE_URL . '/?page=timeline&id=' . $taskId);
            exit;
        }
    }
}
