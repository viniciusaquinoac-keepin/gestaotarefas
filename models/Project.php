<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/ProjectSetting.php';
require_once __DIR__ . '/ProjectPhase.php';
require_once __DIR__ . '/ProjectCost.php';
require_once __DIR__ . '/ProjectPurchase.php';
require_once __DIR__ . '/ProjectTimeline.php';

class Project {
    public static function getAll($statusFilter = null) {
        $db = Database::getConnection();
        $where = "";
        $params = [];

        if ($statusFilter && $statusFilter !== 'all') {
            $where = "WHERE p.status = :status";
            $params['status'] = $statusFilter;
        }

        $stmt = $db->prepare("
            SELECT p.*, u.name as created_by_name,
                   l.name as lead_name, l.company as lead_company
            FROM projects p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN leads l ON p.lead_id = l.id
            $where
            ORDER BY p.id DESC
        ");
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        foreach ($projects as &$project) {
            $project['summary'] = self::getSummary($project['id'], $project);
        }

        return $projects;
    }

    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, u.name as created_by_name,
                   l.name as lead_name, l.company as lead_company
            FROM projects p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN leads l ON p.lead_id = l.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();
        if ($project) {
            $project['summary'] = self::getSummary($project['id'], $project);
        }
        return $project;
    }

    public static function getSummary($id, $projectData = null) {
        if (!$projectData) {
            $projectData = self::getById($id);
        }
        $db = Database::getConnection();

        // 1. Costs (Materials/Expenses)
        $costTotal = ProjectCost::getTotalByProject($id);

        // 2. Hours spent & Labor Cost
        $stmtHours = $db->prepare("
            SELECT SUM(hours_spent) as total_hours_spent,
                   SUM(hours_estimated) as total_hours_estimated
            FROM project_tasks
            WHERE project_id = :id
        ");
        $stmtHours->execute(['id' => $id]);
        $hoursData = $stmtHours->fetch();

        $totalHoursSpent = (float)(($hoursData && isset($hoursData['total_hours_spent'])) ? $hoursData['total_hours_spent'] : 0);
        $totalHoursEstimated = (float)(($hoursData && isset($hoursData['total_hours_estimated'])) ? $hoursData['total_hours_estimated'] : 0);
        $hourlyRate = (float)($projectData['hourly_rate'] ?? 0);
        $laborCostSpent = $totalHoursSpent * $hourlyRate;

        // Total Spent
        $totalSpent = $costTotal + $laborCostSpent;

        // Targets/Budgets
        $targetMaterial = (float)($projectData['target_material_budget'] ?? 0);
        $targetLabor = (float)($projectData['target_labor_budget'] ?? 0);
        $targetTotal = (float)($projectData['target_budget'] ?? ($targetMaterial + $targetLabor));

        // Percentages
        $pctMaterial = $targetMaterial > 0 ? round(($costTotal / $targetMaterial) * 100, 1) : 0;
        $pctLabor = $targetLabor > 0 ? round(($laborCostSpent / $targetLabor) * 100, 1) : 0;
        $pctTotal = $targetTotal > 0 ? round(($totalSpent / $targetTotal) * 100, 1) : 0;

        // 3. Task Progress
        $stmtTaskStats = $db->prepare("
            SELECT COUNT(*) as total_tasks,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                   SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                   SUM(CASE WHEN status = 'delayed' THEN 1 ELSE 0 END) as delayed_tasks
            FROM project_tasks
            WHERE project_id = :id
        ");
        $stmtTaskStats->execute(['id' => $id]);
        $taskStats = $stmtTaskStats->fetch();

        $totalTasks = (int)($taskStats['total_tasks'] ?: 0);
        $completedTasks = (int)($taskStats['completed_tasks'] ?: 0);
        $progressPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // 4. Purchases Stats
        $stmtPurchases = $db->prepare("
            SELECT COUNT(*) as total_purchases,
                   SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_purchases,
                   SUM(CASE WHEN delay_reason IS NOT NULL AND delay_reason != '' THEN 1 ELSE 0 END) as delayed_purchases
            FROM project_purchases
            WHERE project_id = :id
        ");
        $stmtPurchases->execute(['id' => $id]);
        $purchasesStats = $stmtPurchases->fetch();

        return [
            'cost_total' => $costTotal,
            'hours_spent' => $totalHoursSpent,
            'hours_estimated' => $totalHoursEstimated,
            'labor_cost_spent' => $laborCostSpent,
            'total_spent' => $totalSpent,
            'target_material' => $targetMaterial,
            'target_labor' => $targetLabor,
            'target_total' => $targetTotal,
            'pct_material' => $pctMaterial,
            'pct_labor' => $pctLabor,
            'pct_total' => $pctTotal,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'progress_pct' => $progressPct,
            'purchases_total' => (int)($purchasesStats['total_purchases'] ?: 0),
            'purchases_delivered' => (int)($purchasesStats['delivered_purchases'] ?: 0),
            'purchases_delayed' => (int)($purchasesStats['delayed_purchases'] ?: 0)
        ];
    }

    public static function create($data) {
        $db = Database::getConnection();
        
        $hourlyRate = isset($data['hourly_rate']) && $data['hourly_rate'] !== '' 
            ? (float)$data['hourly_rate'] 
            : (float)ProjectSetting::get('default_hourly_rate', 50.00);

        $targetMaterial = (float)($data['target_material_budget'] ?? 0);
        $targetLabor = (float)($data['target_labor_budget'] ?? 0);
        $targetTotal = (float)($data['target_budget'] ?? ($targetMaterial + $targetLabor));

        $stmt = $db->prepare("
            INSERT INTO projects (
                name, client, description, status,
                target_material_budget, target_labor_budget, target_budget,
                hourly_rate, start_date, estimated_end_date, actual_end_date,
                total_months, lead_id, created_by
            ) VALUES (
                :name, :client, :description, :status,
                :target_material_budget, :target_labor_budget, :target_budget,
                :hourly_rate, :start_date, :estimated_end_date, :actual_end_date,
                :total_months, :lead_id, :created_by
            )
        ");

        $success = $stmt->execute([
            'name' => $data['name'],
            'client' => !empty($data['client']) ? $data['client'] : null,
            'description' => !empty($data['description']) ? $data['description'] : null,
            'status' => !empty($data['status']) ? $data['status'] : 'in_progress',
            'target_material_budget' => $targetMaterial,
            'target_labor_budget' => $targetLabor,
            'target_budget' => $targetTotal,
            'hourly_rate' => $hourlyRate,
            'start_date' => !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d'),
            'estimated_end_date' => !empty($data['estimated_end_date']) ? $data['estimated_end_date'] : null,
            'actual_end_date' => !empty($data['actual_end_date']) ? $data['actual_end_date'] : null,
            'total_months' => (int)($data['total_months'] ?? 0),
            'lead_id' => !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            'created_by' => !empty($data['created_by']) ? (int)$data['created_by'] : ($_SESSION['user_id'] ?? 1)
        ]);

        if ($success) {
            $projectId = $db->lastInsertId();

            // Populate default phases if requested or by default
            $defaultPhases = ProjectSetting::getDefaultPhases();
            $order = 1;
            foreach ($defaultPhases as $phaseName) {
                ProjectPhase::create([
                    'project_id' => $projectId,
                    'name' => $phaseName,
                    'order_num' => $order++,
                    'status' => 'not_started'
                ]);
            }

            ProjectTimeline::add(
                $projectId,
                'project_created',
                "Obra criada com Target Total de R$ " . number_format($targetTotal, 2, ',', '.'),
                null,
                $data['name'],
                $data['created_by']
            );

            return $projectId;
        }

        return false;
    }

    public static function update($id, $data, $userId = null) {
        $db = Database::getConnection();
        $project = self::getById($id);
        if (!$project) return false;

        $targetMaterial = (float)($data['target_material_budget'] ?? $project['target_material_budget']);
        $targetLabor = (float)($data['target_labor_budget'] ?? $project['target_labor_budget']);
        $targetTotal = isset($data['target_budget']) ? (float)$data['target_budget'] : ($targetMaterial + $targetLabor);

        $stmt = $db->prepare("
            UPDATE projects SET
                name = :name,
                client = :client,
                description = :description,
                status = :status,
                target_material_budget = :target_material_budget,
                target_labor_budget = :target_labor_budget,
                target_budget = :target_budget,
                hourly_rate = :hourly_rate,
                start_date = :start_date,
                estimated_end_date = :estimated_end_date,
                actual_end_date = :actual_end_date,
                total_months = :total_months,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $res = $stmt->execute([
            'name' => $data['name'] ?? $project['name'],
            'client' => $data['client'] ?? $project['client'],
            'description' => $data['description'] ?? $project['description'],
            'status' => $data['status'] ?? $project['status'],
            'target_material_budget' => $targetMaterial,
            'target_labor_budget' => $targetLabor,
            'target_budget' => $targetTotal,
            'hourly_rate' => isset($data['hourly_rate']) ? (float)$data['hourly_rate'] : $project['hourly_rate'],
            'start_date' => $data['start_date'] ?? $project['start_date'],
            'estimated_end_date' => $data['estimated_end_date'] ?? $project['estimated_end_date'],
            'actual_end_date' => $data['actual_end_date'] ?? $project['actual_end_date'],
            'total_months' => (int)($data['total_months'] ?? $project['total_months']),
            'id' => $id
        ]);

        if ($res) {
            ProjectTimeline::add(
                $id,
                'project_updated',
                "Dados da obra atualizados",
                null, null,
                $userId
            );
        }

        return $res;
    }

    public static function delete($id, $userId = null) {
        $db = Database::getConnection();
        return $db->prepare("DELETE FROM projects WHERE id = :id")->execute(['id' => $id]);
    }

    public static function getByLead($leadId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM projects WHERE lead_id = :lead_id ORDER BY id DESC");
        $stmt->execute(['lead_id' => $leadId]);
        return $stmt->fetchAll();
    }
}
