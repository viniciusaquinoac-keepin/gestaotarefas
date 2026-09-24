<?php
require_once __DIR__ . '/../auth.php';

$requiredModels = [
    'Project.php',
    'ProjectPhase.php',
    'ProjectTask.php',
    'ProjectCost.php',
    'ProjectPurchase.php',
    'ProjectTimeline.php',
    'ProjectSetting.php',
    'User.php',
    'Lead.php'
];

foreach ($requiredModels as $modelFile) {
    $fullPath = __DIR__ . '/../models/' . $modelFile;
    if (!file_exists($fullPath)) {
        die('<div style="background:#212529; color:#f8d7da; padding:20px; border-radius:8px; font-family:sans-serif; margin:20px;">
            <h3 style="color:#dc3545; margin-top:0;">⚠️ Arquivo do Módulo de Obras Ausente no Servidor</h3>
            <p>O arquivo <strong>models/' . $modelFile . '</strong> não foi encontrado no servidor Linux em: <code>' . htmlspecialchars($fullPath) . '</code></p>
            <p><strong>Por favor, faça o upload do arquivo <code>models/' . $modelFile . '</code> para a pasta <code>models/</code> no seu servidor (respeitando as letras maiúsculas e minúsculas).</strong></p>
        </div>');
    }
    require_once $fullPath;
}

class ProjectController {

    public function index() {
        requireAuth();
        try {
            $statusFilter = $_GET['status'] ?? 'in_progress';
            $projects = Project::getAll($statusFilter);

            require_once __DIR__ . '/../views/layout/header.php';
            $viewFile = __DIR__ . '/../views/projects/list.php';
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                    <h4><i class="bi bi-exclamation-triangle me-2"></i>Pasta de Views Não Encontrada no Servidor</h4>
                    <p class="mb-0">O arquivo <code>views/projects/list.php</code> não foi encontrado. Por favor, certifique-se de fazer o upload da pasta <strong><code>views/projects/</code></strong> com todos os seus arquivos (<code>list.php</code>, <code>view.php</code>, <code>create.php</code>, <code>settings.php</code>) para o seu servidor.</p>
                </div>';
            }
            require_once __DIR__ . '/../views/layout/footer.php';
        } catch (Throwable $e) {
            if (!headers_sent()) {
                require_once __DIR__ . '/../views/layout/header.php';
            }
            echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                <h4><i class="bi bi-bug me-2"></i>Erro Interno no Módulo de Obras</h4>
                <p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . ' (Linha ' . $e->getLine() . ')</p>
                <pre class="bg-black text-danger p-3 rounded text-start small mb-0" style="max-height: 300px; overflow-y: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>
            </div>';
            if (file_exists(__DIR__ . '/../views/layout/footer.php')) {
                require_once __DIR__ . '/../views/layout/footer.php';
            }
        }
    }

    public function view() {
        requireAuth();
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                header('Location: ' . BASE_URL . '/?page=projects');
                exit;
            }

            $project = Project::getById($id);
            if (!$project) {
                header('Location: ' . BASE_URL . '/?page=projects');
                exit;
            }

            $phases = ProjectPhase::getByProject($id);
            $costs = ProjectCost::getByProject($id);
            $purchases = ProjectPurchase::getByProject($id);
            $timeline = ProjectTimeline::getByProject($id);
            $categoryBreakdown = ProjectCost::getCategoryBreakdown($id);

            $db = Database::getConnection();
            $users = $db->query("SELECT id, name, avatar_color FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll();
            $categories = ProjectSetting::getDefaultCategories();

            $tab = $_GET['tab'] ?? 'summary';

            require_once __DIR__ . '/../views/layout/header.php';
            $viewFile = __DIR__ . '/../views/projects/view.php';
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                    <h4><i class="bi bi-exclamation-triangle me-2"></i>Arquivo Não Encontrado</h4>
                    <p class="mb-0">O arquivo <code>views/projects/view.php</code> não foi encontrado no servidor.</p>
                </div>';
            }
            require_once __DIR__ . '/../views/layout/footer.php';
        } catch (Throwable $e) {
            if (!headers_sent()) {
                require_once __DIR__ . '/../views/layout/header.php';
            }
            echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                <h4><i class="bi bi-bug me-2"></i>Erro ao Carregar Obra</h4>
                <p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . ' (Linha ' . $e->getLine() . ')</p>
                <pre class="bg-black text-danger p-3 rounded text-start small mb-0" style="max-height: 300px; overflow-y: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>
            </div>';
            if (file_exists(__DIR__ . '/../views/layout/footer.php')) {
                require_once __DIR__ . '/../views/layout/footer.php';
            }
        }
    }

    public function create() {
        requireAuth();
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'client' => trim($_POST['client'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => !empty($_POST['status']) ? $_POST['status'] : 'in_progress',
                    'target_material_budget' => floatval($_POST['target_material_budget'] ?? 0),
                    'target_labor_budget' => floatval($_POST['target_labor_budget'] ?? 0),
                    'target_budget' => floatval($_POST['target_budget'] ?? 0),
                    'hourly_rate' => (isset($_POST['hourly_rate']) && $_POST['hourly_rate'] !== '') ? floatval($_POST['hourly_rate']) : null,
                    'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d'),
                    'estimated_end_date' => !empty($_POST['estimated_end_date']) ? $_POST['estimated_end_date'] : null,
                    'total_months' => intval($_POST['total_months'] ?? 0),
                    'lead_id' => !empty($_POST['lead_id']) ? intval($_POST['lead_id']) : null,
                    'created_by' => $_SESSION['user_id'] ?? 1
                ];

                $projectId = Project::create($data);
                if ($projectId) {
                    header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId);
                    exit;
                }
            }

            $defaultHourlyRate = ProjectSetting::get('default_hourly_rate', 50.00);
            $defaultPhases = ProjectSetting::getDefaultPhases();
            $leads = Lead::getAll();
            
            if (is_array($leads) && !empty($leads)) {
                usort($leads, function($a, $b) {
                    $nameA = trim(($a['company'] ? $a['company'] . ' - ' : '') . $a['name']);
                    $nameB = trim(($b['company'] ? $b['company'] . ' - ' : '') . $b['name']);
                    return strcasecmp($nameA, $nameB);
                });
            }

            require_once __DIR__ . '/../views/layout/header.php';
            $viewFile = __DIR__ . '/../views/projects/create.php';
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                    <h4><i class="bi bi-exclamation-triangle me-2"></i>Arquivo Não Encontrado</h4>
                    <p class="mb-0">O arquivo <code>views/projects/create.php</code> não foi encontrado no servidor.</p>
                </div>';
            }
            require_once __DIR__ . '/../views/layout/footer.php';
        } catch (Throwable $e) {
            if (!headers_sent()) {
                require_once __DIR__ . '/../views/layout/header.php';
            }
            echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                <h4><i class="bi bi-bug me-2"></i>Erro na Tela de Nova Obra</h4>
                <p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . ' (Linha ' . $e->getLine() . ')</p>
                <pre class="bg-black text-danger p-3 rounded text-start small mb-0" style="max-height: 300px; overflow-y: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>
            </div>';
            if (file_exists(__DIR__ . '/../views/layout/footer.php')) {
                require_once __DIR__ . '/../views/layout/footer.php';
            }
        }
    }

    public function update() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            $data = [
                'name' => trim($_POST['name']),
                'client' => trim($_POST['client']),
                'description' => trim($_POST['description'] ?? ''),
                'status' => $_POST['status'],
                'target_material_budget' => floatval($_POST['target_material_budget'] ?? 0),
                'target_labor_budget' => floatval($_POST['target_labor_budget'] ?? 0),
                'target_budget' => floatval($_POST['target_budget'] ?? 0),
                'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
                'start_date' => $_POST['start_date'] ?: null,
                'estimated_end_date' => $_POST['estimated_end_date'] ?: null,
                'actual_end_date' => $_POST['actual_end_date'] ?: null,
                'total_months' => intval($_POST['total_months'] ?? 0)
            ];

            Project::update($id, $data, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $id);
            exit;
        }
    }

    public function delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            Project::delete($id, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects');
            exit;
        }
    }

    // --- Costs ---
    public function add_cost() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = intval($_POST['project_id']);
            $data = [
                'project_id' => $projectId,
                'category' => trim($_POST['category']),
                'description' => trim($_POST['description']),
                'amount' => floatval($_POST['amount']),
                'cost_date' => $_POST['cost_date'] ?: date('Y-m-d'),
                'receipt_note' => trim($_POST['receipt_note'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];

            ProjectCost::create($data);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=financial');
            exit;
        }
    }

    public function delete_cost() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $costId = intval($_POST['cost_id']);
            $projectId = intval($_POST['project_id']);
            ProjectCost::delete($costId, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=financial');
            exit;
        }
    }

    // --- Purchases ---
    public function add_purchase() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = intval($_POST['project_id']);
            $data = [
                'project_id' => $projectId,
                'phase_id' => !empty($_POST['phase_id']) ? intval($_POST['phase_id']) : null,
                'task_id' => !empty($_POST['task_id']) ? intval($_POST['task_id']) : null,
                'item_description' => trim($_POST['item_description']),
                'supplier' => trim($_POST['supplier'] ?? ''),
                'estimated_cost' => floatval($_POST['estimated_cost'] ?? 0),
                'actual_cost' => floatval($_POST['actual_cost'] ?? 0),
                'status' => $_POST['status'] ?: 'requested',
                'delay_reason' => trim($_POST['delay_reason'] ?? ''),
                'request_date' => $_POST['request_date'] ?: date('Y-m-d'),
                'expected_date' => $_POST['expected_date'] ?: null,
                'requested_by' => $_SESSION['user_id']
            ];

            ProjectPurchase::create($data);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=purchases');
            exit;
        }
    }

    public function update_purchase() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['purchase_id']);
            $projectId = intval($_POST['project_id']);
            $status = $_POST['status'];
            $delayReason = trim($_POST['delay_reason'] ?? '');
            $actualCost = floatval($_POST['actual_cost'] ?? 0);
            $actualDate = $_POST['actual_date'] ?: null;

            ProjectPurchase::updateStatus($id, $status, $delayReason, $actualCost, $actualDate, $_SESSION['user_id']);

            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=purchases');
            exit;
        }
    }

    public function delete_purchase() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['purchase_id']);
            $projectId = intval($_POST['project_id']);
            ProjectPurchase::delete($id, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=purchases');
            exit;
        }
    }

    // --- Phases & Tasks ---
    public function add_phase() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = intval($_POST['project_id']);
            $data = [
                'project_id' => $projectId,
                'name' => trim($_POST['name']),
                'sector' => trim($_POST['sector'] ?? ''),
                'planned_start' => $_POST['planned_start'] ?: null,
                'planned_end' => $_POST['planned_end'] ?: null,
                'observations' => trim($_POST['observations'] ?? ''),
                'is_client_visible' => isset($_POST['is_client_visible']) ? 1 : 0
            ];

            ProjectPhase::create($data);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=timeline');
            exit;
        }
    }

    public function update_phase() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['phase_id']);
            $projectId = intval($_POST['project_id']);
            $data = [
                'name' => trim($_POST['name']),
                'status' => $_POST['status'],
                'sector' => trim($_POST['sector'] ?? ''),
                'planned_start' => $_POST['planned_start'] ?: null,
                'planned_end' => $_POST['planned_end'] ?: null,
                'actual_start' => $_POST['actual_start'] ?: null,
                'actual_end' => $_POST['actual_end'] ?: null,
                'delay_reason' => trim($_POST['delay_reason'] ?? ''),
                'observations' => trim($_POST['observations'] ?? ''),
                'is_client_visible' => isset($_POST['is_client_visible']) ? 1 : 0
            ];

            ProjectPhase::update($id, $data, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=timeline');
            exit;
        }
    }

    public function delete_phase() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['phase_id']);
            $projectId = intval($_POST['project_id']);
            ProjectPhase::delete($id, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=timeline');
            exit;
        }
    }

    public function add_task() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = intval($_POST['project_id']);
            $data = [
                'project_id' => $projectId,
                'phase_id' => intval($_POST['phase_id']),
                'title' => trim($_POST['title']),
                'status' => $_POST['status'] ?: 'pending',
                'assigned_to' => !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null,
                'planned_start' => $_POST['planned_start'] ?: null,
                'planned_end' => $_POST['planned_end'] ?: null,
                'hours_estimated' => floatval($_POST['hours_estimated'] ?? 0),
                'notes' => trim($_POST['notes'] ?? '')
            ];

            ProjectTask::create($data);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=timeline');
            exit;
        }
    }

    public function update_task() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['task_id']);
            $projectId = intval($_POST['project_id']);
            $data = [
                'title' => trim($_POST['title']),
                'status' => $_POST['status'],
                'assigned_to' => !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null,
                'planned_start' => $_POST['planned_start'] ?: null,
                'planned_end' => $_POST['planned_end'] ?: null,
                'actual_start' => $_POST['actual_start'] ?: null,
                'actual_end' => $_POST['actual_end'] ?: null,
                'hours_estimated' => floatval($_POST['hours_estimated'] ?? 0),
                'hours_spent' => floatval($_POST['hours_spent'] ?? 0),
                'delay_reason' => trim($_POST['delay_reason'] ?? ''),
                'notes' => trim($_POST['notes'] ?? '')
            ];

            ProjectTask::update($id, $data, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=timeline');
            exit;
        }
    }

    public function delete_task() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['task_id']);
            $projectId = intval($_POST['project_id']);
            ProjectTask::delete($id, $_SESSION['user_id']);
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=timeline');
            exit;
        }
    }

    public function add_comment() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = intval($_POST['project_id']);
            $comment = trim($_POST['comment']);
            if ($comment) {
                ProjectTimeline::add($projectId, 'comment', $comment, null, null, $_SESSION['user_id']);
            }
            header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId . '&tab=history');
            exit;
        }
    }

    public function convert_lead() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = intval($_POST['lead_id']);
            $lead = Lead::getById($leadId);
            if ($lead) {
                $data = [
                    'name' => 'Obra - ' . $lead['company'] . ' (' . $lead['name'] . ')',
                    'client' => $lead['company'] ?: $lead['name'],
                    'description' => 'Obra convertida do Lead #' . $lead['id'] . ': ' . $lead['notes'],
                    'status' => 'in_progress',
                    'target_material_budget' => floatval($lead['estimated_value'] ?? 0),
                    'target_labor_budget' => 0,
                    'target_budget' => floatval($lead['estimated_value'] ?? 0),
                    'hourly_rate' => null,
                    'start_date' => date('Y-m-d'),
                    'lead_id' => $leadId,
                    'created_by' => $_SESSION['user_id']
                ];

                $projectId = Project::create($data);
                if ($projectId) {
                    header('Location: ' . BASE_URL . '/?page=projects&action=view&id=' . $projectId);
                    exit;
                }
            }
            header('Location: ' . BASE_URL . '/?page=leads');
            exit;
        }
    }

    public function settings() {
        requireAuth();
        if (!isAdmin()) {
            header('Location: ' . BASE_URL . '/?page=projects');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['default_hourly_rate'])) {
                ProjectSetting::set('default_hourly_rate', floatval($_POST['default_hourly_rate']), 'Valor por hora padrão');
            }
            if (isset($_POST['default_phases'])) {
                $phases = array_filter(array_map('trim', explode("\n", $_POST['default_phases'])));
                ProjectSetting::set('default_phases', array_values($phases), 'Etapas padrão');
            }
            if (isset($_POST['default_cost_categories'])) {
                $categories = array_filter(array_map('trim', explode("\n", $_POST['default_cost_categories'])));
                ProjectSetting::set('default_cost_categories', array_values($categories), 'Categorias de custo');
            }

            header('Location: ' . BASE_URL . '/?page=projects&action=settings&saved=1');
            exit;
        }

        $defaultHourlyRate = ProjectSetting::get('default_hourly_rate', 50.00);
        $defaultPhases = ProjectSetting::getDefaultPhases();
        $defaultCategories = ProjectSetting::getDefaultCategories();

        require_once __DIR__ . '/../views/layout/header.php';
        $viewFile = __DIR__ . '/../views/projects/settings.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo '<div class="alert alert-danger bg-dark border-danger text-danger p-4 rounded my-4">
                <h4><i class="bi bi-exclamation-triangle me-2"></i>Arquivo Não Encontrado</h4>
                <p class="mb-0">O arquivo <code>views/projects/settings.php</code> não foi encontrado no servidor.</p>
            </div>';
        }
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
