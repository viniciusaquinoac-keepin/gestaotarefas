<?php
$user = getCurrentUser();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
    <?php if ($page == 'projects'): ?>
    <link href="<?= BASE_URL ?>/assets/css/projects.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php if (isLoggedIn()): ?>
        <!-- Sidebar -->
        <div class="bg-dark border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 text-white fs-5 fw-bold">
                <i class="bi bi-layers-fill text-primary"></i> Keepin Gestão
            </div>
            
            <div class="px-3 pb-3 border-bottom border-secondary mb-2">
                <form method="GET" action="<?= BASE_URL ?>/">
                    <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                    <label class="form-label text-white small mb-1">Filtrar Período</label>
                    <input type="date" name="filter_start" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" value="<?= htmlspecialchars(getGlobalFilterStart()) ?>">
                    <input type="date" name="filter_end" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" value="<?= htmlspecialchars(getGlobalFilterEnd()) ?>">
                    <button class="btn btn-sm btn-outline-primary w-100 mb-2" type="submit">Aplicar Filtro</button>
                </form>
                <a href="<?= BASE_URL ?>/?page=report&action=pdf" target="_blank" class="btn btn-sm btn-outline-danger w-100">
                    <i class="bi bi-file-earmark-pdf"></i> Gerar PDF
                </a>
            </div>

            <div class="list-group list-group-flush">
                <a href="<?= BASE_URL ?>/?page=dashboard" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="<?= BASE_URL ?>/?page=projects" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'projects' ? 'active' : '' ?>">
                    <i class="bi bi-building me-2"></i> Gestão de Obras
                </a>
                <a href="<?= BASE_URL ?>/?page=tasks" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'tasks' ? 'active' : '' ?>">
                    <i class="bi bi-kanban me-2"></i> Tarefas (Kanban)
                </a>
                <a href="<?= BASE_URL ?>/?page=agenda" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'agenda' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-week me-2"></i> Agenda da Semana
                </a>
                <a href="<?= BASE_URL ?>/?page=prospeccao" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'prospeccao' ? 'active' : '' ?>">
                    <i class="bi bi-crosshair me-2"></i> Prospecção (SPIN)
                </a>
                <a href="<?= BASE_URL ?>/?page=kpis" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'kpis' ? 'active' : '' ?>">
                    <i class="bi bi-trophy me-2"></i> KPIs & Ranking
                </a>
                <a href="<?= BASE_URL ?>/?page=playbook" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'playbook' ? 'active' : '' ?>">
                    <i class="bi bi-journal-bookmark me-2"></i> Playbooks
                </a>
                <a href="<?= BASE_URL ?>/?page=leads" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'leads' ? 'active' : '' ?>">
                    <i class="bi bi-funnel me-2"></i> Comercial (Leads Antigos)
                </a>
                <a href="<?= BASE_URL ?>/?page=meetings" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'meetings' ? 'active' : '' ?>">
                    <i class="bi bi-calendar2-check me-2"></i> Reuniões
                </a>
                <a href="<?= BASE_URL ?>/?page=suggestions" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'suggestions' ? 'active' : '' ?>">
                    <i class="bi bi-lightbulb me-2"></i> Sugestões
                </a>
                <?php if (isAdmin()): ?>
                <a href="<?= BASE_URL ?>/?page=users" class="list-group-item list-group-item-action bg-dark text-white <?= $page == 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people me-2"></i> Usuários
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100 bg-dark text-light">
            <?php if (isLoggedIn()): ?>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom px-3">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary btn-sm" id="menu-toggle"><i class="bi bi-list"></i></button>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="badge bg-secondary me-2"><?= htmlspecialchars($user['department']) ?></span>
                                <?= htmlspecialchars($user['name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=auth&action=logout"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            <?php endif; ?>
            
            <div class="container-fluid p-4">
