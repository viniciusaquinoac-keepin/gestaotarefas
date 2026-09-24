<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/User.php';

class LeadController {
    public function index() {
        requireAuth();
        
        $leadsNew = Lead::getByStatus('new');
        $leadsContacted = Lead::getByStatus('contacted');
        $leadsMeeting = Lead::getByStatus('meeting');
        $leadsProposal = Lead::getByStatus('proposal');
        $leadsInAnalysis = Lead::getByStatus('in_analysis');
        $leadsWon = Lead::getByStatus('closed_won');
        $leadsLost = Lead::getByStatus('closed_lost');
        $leadsLimbo = Lead::getByStatus('limbo');

        $db = Database::getConnection();
        $users = $db->query("SELECT id, name FROM users WHERE active = 1 AND department = 'COMERCIAL'")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/leads/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'company' => $_POST['company'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'source' => $_POST['source'],
                'estimated_value' => $_POST['estimated_value'],
                'next_contact_date' => $_POST['next_contact_date'],
                'notes' => $_POST['notes'],
                'assigned_to' => $_POST['assigned_to'] ?: null,
                'created_by' => $_SESSION['user_id']
            ];
            Lead::create($data);
            header('Location: ' . BASE_URL . '/?page=leads');
            exit;
        }
    }

    public function update_status() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = $_POST['lead_id'];
            $newStatus = $_POST['status'];
            
            Lead::updateStatus($leadId, $newStatus);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }
    public function update() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = $_POST['lead_id'];
            $data = [
                'name' => $_POST['name'],
                'company' => $_POST['company'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'source' => $_POST['source'],
                'estimated_value' => $_POST['estimated_value'],
                'next_contact_date' => $_POST['next_contact_date'],
                'notes' => $_POST['notes'],
                'assigned_to' => $_POST['assigned_to'] ?: null
            ];
            Lead::update($leadId, $data);
            header('Location: ' . BASE_URL . '/?page=leads');
            exit;
        }
    }

    public function delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = $_POST['lead_id'];
            Lead::delete($leadId);
            header('Location: ' . BASE_URL . '/?page=leads');
            exit;
        }
    }

    public function comment() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = $_POST['lead_id'];
            $comment = $_POST['comment'];
            $mentionedUserId = !empty($_POST['mentioned_user_id']) ? $_POST['mentioned_user_id'] : null;
            
            require_once __DIR__ . '/../models/LeadHistory.php';
            LeadHistory::add($leadId, 'comment', null, null, $comment, $_SESSION['user_id'], $mentionedUserId);
            
            header('Location: ' . BASE_URL . '/?page=lead_timeline&id=' . $leadId);
            exit;
        }
    }

    public function postpone() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = $_POST['lead_id'];
            $newDate = $_POST['new_date'];
            $comment = $_POST['comment'];
            
            Lead::postpone($leadId, $newDate, $comment, $_SESSION['user_id']);
            
            header('Location: ' . BASE_URL . '/?page=lead_timeline&id=' . $leadId);
            exit;
        }
    }

    public function history_delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $historyId = $_POST['history_id'];
            $leadId = $_POST['lead_id'];
            
            require_once __DIR__ . '/../models/LeadHistory.php';
            LeadHistory::delete($historyId);
            
            header('Location: ' . BASE_URL . '/?page=lead_timeline&id=' . $leadId);
            exit;
        }
    }
}
