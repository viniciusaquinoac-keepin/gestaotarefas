<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/LeadHistory.php';

class LeadTimelineController {
    public function index() {
        requireAuth();
        
        $leadId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT l.*, u.name as assigned_name FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE l.id = :id");
        $stmt->execute(['id' => $leadId]);
        $lead = $stmt->fetch();
        
        if (!$lead) {
            die("Lead não encontrado.");
        }

        $history = LeadHistory::getByLead($leadId);
        
        $users = $db->query("SELECT id, name FROM users WHERE active = 1")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/leads/timeline.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
