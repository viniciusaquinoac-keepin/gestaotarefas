<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Roteador simples
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Mapeamento de controllers
$routes = [
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'tasks' => 'TaskController',
    'leads' => 'LeadController',
    'meetings' => 'MeetingController',
    'users' => 'UserController',
    'timeline' => 'TimelineController',
    'suggestions' => 'SuggestionController',
    'report' => 'ReportController',
    'lead_timeline' => 'LeadTimelineController',
    'projects' => 'ProjectController',
    'prospeccao' => 'ProspeccaoController',
    'agenda' => 'AgendaController',
    'kpis' => 'KpiController',
    'playbook' => 'PlaybookController'
];

if (!array_key_exists($page, $routes)) {
    die("Página não encontrada.");
}

$controllerName = $routes[$page];
$controllerFile = __DIR__ . "/controllers/{$controllerName}.php";

if (!file_exists($controllerFile)) {
    die("Controller não encontrado: {$controllerName}");
}

require_once $controllerFile;

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die("Ação não encontrada no controller.");
}

// Executar ação
$controller->$action();
