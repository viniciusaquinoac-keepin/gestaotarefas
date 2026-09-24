<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/User.php';

class DashboardController {
    public function index() {
        requireAuth();
        
        $db = Database::getConnection();
        $hoje = date('Y-m-d');
        
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();

        $taskDateFilter = "AND (DATE(created_at) >= '$start' OR DATE(due_date) >= '$start' OR DATE(completed_at) >= '$start')";
        if (!empty($end)) {
            $taskDateFilter = "AND ((DATE(created_at) >= '$start' AND DATE(created_at) <= '$end') OR (DATE(due_date) >= '$start' AND DATE(due_date) <= '$end') OR (DATE(completed_at) >= '$start' AND DATE(completed_at) <= '$end'))";
        }

        $leadDateFilter = "AND (DATE(created_at) >= '$start' OR DATE(next_contact_date) >= '$start')";
        if (!empty($end)) {
            $leadDateFilter = "AND ((DATE(created_at) >= '$start' AND DATE(created_at) <= '$end') OR (DATE(next_contact_date) >= '$start' AND DATE(next_contact_date) <= '$end'))";
        }

        $meetingDateFilter = "AND (DATE(created_at) >= '$start' OR DATE(date) >= '$start')";
        if (!empty($end)) {
            $meetingDateFilter = "AND ((DATE(created_at) >= '$start' AND DATE(created_at) <= '$end') OR (DATE(date) >= '$start' AND DATE(date) <= '$end'))";
        }

        $totalTasks = $db->query("SELECT COUNT(*) FROM tasks WHERE status != 'done' $taskDateFilter")->fetchColumn();
        
        $stmtDelayed = $db->prepare("SELECT COUNT(*) FROM tasks WHERE status != 'done' AND due_date < :hoje $taskDateFilter");
        $stmtDelayed->execute(['hoje' => $hoje]);
        $delayedTasks = $stmtDelayed->fetchColumn();
        
        $stmtLeads = $db->prepare("SELECT COUNT(*) FROM leads WHERE status NOT IN ('closed_won', 'closed_lost') AND next_contact_date <= :hoje AND next_contact_date IS NOT NULL $leadDateFilter");
        $stmtLeads->execute(['hoje' => $hoje]);
        $leadsToday = $stmtLeads->fetchColumn();
        
        $inicioSemana = date('Y-m-d', strtotime('monday this week'));
        $fimSemana = date('Y-m-d', strtotime('sunday this week'));
        $stmtMeetings = $db->prepare("SELECT COUNT(*) FROM meetings WHERE date BETWEEN :inicio AND :fim $meetingDateFilter");
        $stmtMeetings->execute(['inicio' => $inicioSemana, 'fim' => $fimSemana]);
        $meetingsWeek = $stmtMeetings->fetchColumn();

        // Dados para o Gráfico (Tarefas por Status)
        $tasksByStatusRaw = $db->query("SELECT status, COUNT(*) as count FROM tasks WHERE 1=1 $taskDateFilter GROUP BY status")->fetchAll();
        $tasksByStatus = ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'done' => 0];
        foreach($tasksByStatusRaw as $row) {
            $tasksByStatus[$row['status']] = $row['count'];
        }

        // Atividades Recentes de Tarefas
        $recentActivities = $db->query("
            SELECT th.*, u.name as user_name, t.title as task_title 
            FROM task_history th 
            JOIN users u ON th.changed_by = u.id 
            JOIN tasks t ON th.task_id = t.id 
            ORDER BY th.created_at DESC LIMIT 5
        ")->fetchAll();

        // Resumo de Leads (Status)
        $leadsByStatusRaw = $db->query("SELECT status, COUNT(*) as count FROM leads WHERE 1=1 $leadDateFilter GROUP BY status")->fetchAll();
        $leadsByStatus = ['new' => 0, 'contacted' => 0, 'meeting' => 0, 'proposal' => 0, 'in_analysis' => 0, 'closed_won' => 0, 'closed_lost' => 0];
        foreach($leadsByStatusRaw as $row) {
            $leadsByStatus[$row['status']] = $row['count'];
        }

        // Atividades Recentes de Leads
        $recentLeads = $db->query("
            SELECT l.*, u.name as assigned_name 
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id 
            WHERE 1=1 AND (DATE(l.created_at) >= '$start' OR DATE(l.next_contact_date) >= '$start') " . (!empty($end) ? " AND ((DATE(l.created_at) >= '$start' AND DATE(l.created_at) <= '$end') OR (DATE(l.next_contact_date) >= '$start' AND DATE(l.next_contact_date) <= '$end'))" : "") . "
            ORDER BY l.updated_at DESC LIMIT 5
        ")->fetchAll();

        $postponedTasksStats = $db->query("SELECT COUNT(*) as count, SUM(CAST(julianday(new_value) - julianday(old_value) AS INTEGER)) as days FROM task_history WHERE action = 'postponed'")->fetch();
        $postponedLeadsStats = $db->query("SELECT COUNT(*) as count, SUM(CAST(julianday(new_value) - julianday(old_value) AS INTEGER)) as days FROM lead_history WHERE action = 'postponed'")->fetch();

        // Métricas de Obras
        require_once __DIR__ . '/../models/Project.php';
        $activeProjectsCount = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'in_progress'")->fetchColumn();
        $recentProjects = Project::getAll('in_progress');

        $stats = [
            'total_tasks' => $totalTasks,
            'delayed_tasks' => $delayedTasks,
            'leads_today' => $leadsToday,
            'meetings_this_week' => $meetingsWeek,
            'postponed_tasks_count' => $postponedTasksStats['count'] ?? 0,
            'postponed_tasks_days' => $postponedTasksStats['days'] ?? 0,
            'postponed_leads_count' => $postponedLeadsStats['count'] ?? 0,
            'postponed_leads_days' => $postponedLeadsStats['days'] ?? 0,
            'active_projects_count' => $activeProjectsCount
        ];

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/dashboard/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
