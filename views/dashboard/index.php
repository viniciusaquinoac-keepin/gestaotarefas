<h1 class="h3 mb-4 text-light">Dashboard</h1>

<div class="row row-cols-1 row-cols-md-5 g-3">
    <div class="col">
        <div class="card text-bg-warning mb-3 shadow-sm border-0 text-dark" onclick="window.location.href='<?= BASE_URL ?>/?page=projects'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-black-50">Obras Ativas</h6>
                        <h2 class="mb-0 text-dark fw-bold"><?= $stats['active_projects_count'] ?></h2>
                    </div>
                    <i class="bi bi-building fs-1 text-dark opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card text-bg-primary mb-3 shadow-sm border-0" onclick="window.location.href='<?= BASE_URL ?>/?page=tasks'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50">Tarefas Ativas</h6>
                        <h2 class="mb-0"><?= $stats['total_tasks'] ?></h2>
                    </div>
                    <i class="bi bi-kanban fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card text-bg-danger mb-3 shadow-sm border-0" onclick="window.location.href='<?= BASE_URL ?>/?page=tasks'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50">Atrasadas</h6>
                        <h2 class="mb-0"><?= $stats['delayed_tasks'] ?></h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card text-bg-success mb-3 shadow-sm border-0" onclick="window.location.href='<?= BASE_URL ?>/?page=leads'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50">Leads em Aberto</h6>
                        <h2 class="mb-0"><?= $stats['leads_today'] ?></h2>
                    </div>
                    <i class="bi bi-person-lines-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card text-bg-info mb-3 shadow-sm border-0" onclick="window.location.href='<?= BASE_URL ?>/?page=meetings'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-black-50">Reuniões</h6>
                        <h2 class="mb-0 text-dark"><?= $stats['meetings_this_week'] ?></h2>
                    </div>
                    <i class="bi bi-calendar-event fs-1 text-dark opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card text-bg-warning mb-3 shadow-sm border-0" onclick="window.location.href='<?= BASE_URL ?>/?page=tasks'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-black-50" style="font-size: 0.8rem;">Prorrogadas</h6>
                        <h2 class="mb-0 text-dark"><?= $stats['postponed_tasks_count'] ?> <small class="fs-6 fw-normal">(<?= $stats['postponed_tasks_days'] ?>d)</small></h2>
                    </div>
                    <i class="bi bi-clock-history fs-1 text-dark opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card bg-dark border-secondary mb-4 h-100">
            <div class="card-header border-secondary">
                Visão Geral das Tarefas (Status)
            </div>
            <div class="card-body">
                <canvas id="tasksChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-secondary mb-4 h-100">
            <div class="card-header border-secondary">
                Atividades Recentes
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if(empty($recentActivities)): ?>
                        <li class="list-group-item bg-dark text-secondary border-secondary text-center py-4">Nenhuma atividade recente.</li>
                    <?php else: ?>
                        <?php foreach($recentActivities as $act): ?>
                            <li class="list-group-item bg-dark text-light border-secondary">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-info"><?= htmlspecialchars($act['user_name']) ?></small>
                                    <small class="text-secondary"><?= date('d/m H:i', strtotime($act['created_at'])) ?></small>
                                </div>
                                <p class="mb-1 text-truncate" title="<?= htmlspecialchars($act['task_title']) ?>">
                                    <strong><?= htmlspecialchars($act['task_title']) ?></strong>
                                </p>
                                <small class="text-muted">
                                    <?php if($act['action'] == 'status_change'): ?>
                                        Moveu de <em><?= $act['old_value'] ?></em> para <em><?= $act['new_value'] ?></em>
                                    <?php elseif($act['action'] == 'postponed'): ?>
                                        Prorrogou o prazo
                                    <?php else: ?>
                                        <?= htmlspecialchars($act['action']) ?>
                                    <?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card bg-dark border-secondary mb-4 h-100">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Visão Geral Comercial (Leads)</span>
                <span class="badge bg-secondary">Total: <?= array_sum($leadsByStatus) ?></span>
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-6 col-md">
                        <div class="p-2 border border-secondary rounded bg-secondary bg-opacity-25 h-100">
                            <h4 class="mb-0 text-light"><?= $leadsByStatus['new'] ?></h4>
                            <small class="text-secondary d-block text-truncate">Novos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-info rounded bg-info bg-opacity-10 h-100">
                            <h4 class="mb-0 text-info"><?= $leadsByStatus['contacted'] ?></h4>
                            <small class="text-info d-block text-truncate">Contatados</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-primary rounded bg-primary bg-opacity-10 h-100">
                            <h4 class="mb-0 text-primary"><?= $leadsByStatus['meeting'] ?></h4>
                            <small class="text-primary d-block text-truncate">Reunião</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-warning rounded bg-warning bg-opacity-10 h-100">
                            <h4 class="mb-0 text-warning"><?= $leadsByStatus['proposal'] ?></h4>
                            <small class="text-warning d-block text-truncate">Propostas</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-light rounded bg-dark bg-opacity-25 h-100">
                            <h4 class="mb-0 text-light"><?= $leadsByStatus['in_analysis'] ?></h4>
                            <small class="text-light d-block text-truncate">Em Análise</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-success rounded bg-success bg-opacity-10 h-100">
                            <h4 class="mb-0 text-success"><?= $leadsByStatus['closed_won'] ?></h4>
                            <small class="text-success d-block text-truncate">Ganhos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-danger rounded bg-danger bg-opacity-10 h-100">
                            <h4 class="mb-0 text-danger"><?= $leadsByStatus['closed_lost'] ?></h4>
                            <small class="text-danger d-block text-truncate">Perdidos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2 border border-warning rounded bg-warning bg-opacity-10 h-100">
                            <h4 class="mb-0 text-warning"><?= $stats['postponed_leads_count'] ?> <small class="fs-6">(<?= $stats['postponed_leads_days'] ?>d)</small></h4>
                            <small class="text-warning d-block text-truncate">Prorrogados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-secondary mb-4 h-100">
            <div class="card-header border-secondary">
                Atividades Recentes (Comercial)
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if(empty($recentLeads)): ?>
                        <li class="list-group-item bg-dark text-secondary border-secondary text-center py-4">Nenhum lead movimentado recentemente.</li>
                    <?php else: ?>
                        <?php foreach($recentLeads as $lead): ?>
                            <?php 
                                $statusLabels = [
                                    'new' => ['Novos', 'secondary'],
                                    'contacted' => ['Em Contato', 'info'],
                                    'meeting' => ['Reunião', 'primary'],
                                    'proposal' => ['Proposta', 'warning'],
                                    'in_analysis' => ['Em Análise', 'light'],
                                    'closed_won' => ['Ganho', 'success'],
                                    'closed_lost' => ['Perdido', 'danger']
                                ];
                                $sLabel = $statusLabels[$lead['status']][0];
                                $sColor = $statusLabels[$lead['status']][1];
                            ?>
                            <li class="list-group-item bg-dark text-light border-secondary">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <strong class="text-truncate"><?= htmlspecialchars($lead['name']) ?></strong>
                                    <span class="badge bg-<?= $sColor ?>"><?= $sLabel ?></span>
                                </div>
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted"><i class="bi bi-person"></i> <?= htmlspecialchars($lead['assigned_name'] ?? 'Sem Vendedor') ?></small>
                                    <small class="text-secondary"><?= date('d/m H:i', strtotime($lead['updated_at'])) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    const chartData = <?= json_encode($tasksByStatus) ?>;
</script>
<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
