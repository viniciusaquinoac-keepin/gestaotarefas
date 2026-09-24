<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Lead.php';

class ReportController {
    public function pdf() {
        requireAuth();
        
        $db = Database::getConnection();
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();

        // Construir label do período
        $periodoLabel = date('d/m/Y', strtotime($start)) . " até " . (!empty($end) ? date('d/m/Y', strtotime($end)) : "o futuro");

        // 1. Obter Tarefas do Período
        $taskDateFilter = "AND (DATE(t.created_at) >= '$start' OR DATE(t.due_date) >= '$start' OR DATE(t.completed_at) >= '$start')";
        if (!empty($end)) {
            $taskDateFilter = "AND ((DATE(t.created_at) >= '$start' AND DATE(t.created_at) <= '$end') OR (DATE(t.due_date) >= '$start' AND DATE(t.due_date) <= '$end') OR (DATE(t.completed_at) >= '$start' AND DATE(t.completed_at) <= '$end'))";
        }
        $tasks = $db->query("
            SELECT t.*, u.name as assigned_name 
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.id 
            WHERE 1=1 $taskDateFilter
            ORDER BY u.name ASC, t.due_date ASC
        ")->fetchAll();

        // Obter histórico de cada tarefa
        foreach ($tasks as &$task) {
            $stmt = $db->prepare("SELECT th.*, u.name as user_name FROM task_history th JOIN users u ON th.changed_by = u.id WHERE th.task_id = :task_id ORDER BY th.created_at ASC");
            $stmt->execute(['task_id' => $task['id']]);
            $task['history'] = $stmt->fetchAll();
        }

        // 2. Obter Leads do Período
        $leadDateFilter = "AND (DATE(l.created_at) >= '$start' OR DATE(l.next_contact_date) >= '$start')";
        if (!empty($end)) {
            $leadDateFilter = "AND ((DATE(l.created_at) >= '$start' AND DATE(l.created_at) <= '$end') OR (DATE(l.next_contact_date) >= '$start' AND DATE(l.next_contact_date) <= '$end'))";
        }
        $leads = $db->query("
            SELECT l.*, u.name as assigned_name 
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id 
            WHERE 1=1 $leadDateFilter
            ORDER BY u.name ASC, l.next_contact_date ASC
        ")->fetchAll();

        // Passar dados para a view HTML focada em impressão
        require_once __DIR__ . '/../views/report/pdf.php';
    }
}
