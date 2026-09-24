<?php
$s = is_array($project['summary'] ?? null) ? $project['summary'] : [
    'cost_total' => 0, 'hours_spent' => 0, 'hours_estimated' => 0, 'labor_cost_spent' => 0,
    'total_spent' => 0, 'target_material' => 0, 'target_labor' => 0, 'target_total' => 0,
    'pct_material' => 0, 'pct_labor' => 0, 'pct_total' => 0, 'total_tasks' => 0,
    'completed_tasks' => 0, 'progress_pct' => 0, 'purchases_total' => 0,
    'purchases_delivered' => 0, 'purchases_delayed' => 0
];

$getBarColorClass = function($pct) {
    if ($pct > 100) return 'bg-danger';
    if ($pct > 85) return 'bg-warning text-dark';
    return 'bg-success';
};
?>

<!-- Header da Obra -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="<?= BASE_URL ?>/?page=projects" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
            <h2 class="fw-bold text-white mb-0"><?= htmlspecialchars($project['name']) ?></h2>
            <span class="badge bg-primary fs-6"><?= ucfirst(htmlspecialchars($project['status'])) ?></span>
        </div>
        <p class="text-secondary small mb-0">
            <i class="bi bi-building me-1"></i> Cliente: <strong class="text-light me-3"><?= htmlspecialchars($project['client'] ?: 'Não informado') ?></strong>
            <i class="bi bi-calendar3 me-1"></i> Início: <?= $project['start_date'] ? date('d/m/Y', strtotime($project['start_date'])) : '-' ?>
            <?php if ($project['estimated_end_date']): ?>
            <span class="ms-3"><i class="bi bi-flag me-1"></i> Previsão Fim: <?= date('d/m/Y', strtotime($project['estimated_end_date'])) ?></span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProjectModal">
            <i class="bi bi-pencil me-1"></i> Editar Dados
        </button>
        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
            <i class="bi bi-trash me-1"></i> Excluir Obra
        </button>
    </div>
</div>

<!-- Cards KPI — Divididos em Materiais, Mão de Obra, Total Acumulado e Prazo -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
    <!-- KPI 1: Materiais -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase"><i class="bi bi-box-seam text-warning me-1"></i> Materiais</span>
                <span class="badge bg-dark border border-warning text-warning"><?= $s['pct_material'] ?>% do Target</span>
            </div>
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h4 class="fw-bold text-white mb-0">R$ <?= number_format($s['cost_total'], 2, ',', '.') ?></h4>
                <small class="text-secondary">Target: R$ <?= number_format($s['target_material'], 2, ',', '.') ?></small>
            </div>
            <div class="progress bg-secondary" style="height: 8px;">
                <div class="progress-bar <?= $getBarColorClass($s['pct_material']) ?>" role="progressbar" style="width: <?= min(100, $s['pct_material']) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Mão de Obra (Horas) -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase"><i class="bi bi-clock-history text-info me-1"></i> Mão de Obra</span>
                <span class="badge bg-dark border border-info text-info"><?= $s['pct_labor'] ?>% do Target</span>
            </div>
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h4 class="fw-bold text-white mb-0">R$ <?= number_format($s['labor_cost_spent'], 2, ',', '.') ?></h4>
                <small class="text-secondary">Target: R$ <?= number_format($s['target_labor'], 2, ',', '.') ?></small>
            </div>
            <div class="progress bg-secondary mb-1" style="height: 8px;">
                <div class="progress-bar <?= $getBarColorClass($s['pct_labor']) ?>" role="progressbar" style="width: <?= min(100, $s['pct_labor']) ?>%"></div>
            </div>
            <div class="d-flex justify-content-between small text-secondary">
                <span>Horas: <?= number_format($s['hours_spent'], 1, ',', '.') ?>h</span>
                <span>Taxa: R$ <?= number_format($project['hourly_rate'], 2, ',', '.') ?>/h</span>
            </div>
        </div>
    </div>

    <!-- KPI 3: Total Acumulado -->
    <div class="col">
        <div class="kpi-card h-100 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-primary small fw-bold text-uppercase"><i class="bi bi-calculator me-1"></i> Total Acumulado</span>
                <span class="badge bg-primary text-white"><?= $s['pct_total'] ?>% Gasto</span>
            </div>
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h4 class="fw-bold text-primary mb-0">R$ <?= number_format($s['total_spent'], 2, ',', '.') ?></h4>
                <small class="text-secondary">Target: R$ <?= number_format($s['target_total'], 2, ',', '.') ?></small>
            </div>
            <div class="progress bg-secondary" style="height: 10px;">
                <div class="progress-bar <?= $getBarColorClass($s['pct_total']) ?>" role="progressbar" style="width: <?= min(100, $s['pct_total']) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Progresso Físico -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase"><i class="bi bi-check2-square text-success me-1"></i> Progresso Físico</span>
                <span class="badge bg-success text-white"><?= $s['progress_pct'] ?>% Concluído</span>
            </div>
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h4 class="fw-bold text-white mb-0"><?= $s['completed_tasks'] ?> <small class="fs-6 text-secondary">/ <?= $s['total_tasks'] ?> tarefas</small></h4>
                <small class="text-secondary"><?= $project['total_months'] ? $project['total_months'] . ' meses' : '' ?></small>
            </div>
            <div class="progress bg-secondary" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $s['progress_pct'] ?>%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Abas de Navegação -->
<ul class="nav nav-tabs border-secondary mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'summary' ? 'active bg-dark text-white border-secondary' : 'text-secondary' ?>" href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $project['id'] ?>&tab=summary">
            <i class="bi bi-pie-chart me-1"></i> Resumo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'timeline' ? 'active bg-dark text-white border-secondary' : 'text-secondary' ?>" href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $project['id'] ?>&tab=timeline">
            <i class="bi bi-diagram-3 me-1"></i> Cronograma & Tarefas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'financial' ? 'active bg-dark text-white border-secondary' : 'text-secondary' ?>" href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $project['id'] ?>&tab=financial">
            <i class="bi bi-cash-stack me-1"></i> Financeiro & Custos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'purchases' ? 'active bg-dark text-white border-secondary' : 'text-secondary' ?>" href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $project['id'] ?>&tab=purchases">
            <i class="bi bi-cart3 me-1"></i> Compras (Pipeline)
            <?php if ($s['purchases_delayed'] > 0): ?>
            <span class="badge bg-danger ms-1"><?= $s['purchases_delayed'] ?> atrasada(s)</span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'history' ? 'active bg-dark text-white border-secondary' : 'text-secondary' ?>" href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $project['id'] ?>&tab=history">
            <i class="bi bi-clock-history me-1"></i> Histórico
        </a>
    </li>
</ul>

<!-- Conteúdo da Aba Selecionada -->
<?php if ($tab === 'summary'): ?>
    <!-- ABA RESUMO -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header bg-dark border-secondary text-white fw-bold">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Comparativo: Gasto Real vs Target Orçado
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Realizado</th>
                                    <th class="text-end">Target (Proposta)</th>
                                    <th class="text-center">% Utilizado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="bi bi-box-seam text-warning me-2"></i>Materiais / Custos</td>
                                    <td class="text-end fw-bold">R$ <?= number_format($s['cost_total'], 2, ',', '.') ?></td>
                                    <td class="text-end text-secondary">R$ <?= number_format($s['target_material'], 2, ',', '.') ?></td>
                                    <td class="text-center"><span class="badge <?= $getBarColorClass($s['pct_material']) ?>"><?= $s['pct_material'] ?>%</span></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-clock-history text-info me-2"></i>Mão de Obra (Horas)</td>
                                    <td class="text-end fw-bold">R$ <?= number_format($s['labor_cost_spent'], 2, ',', '.') ?></td>
                                    <td class="text-end text-secondary">R$ <?= number_format($s['target_labor'], 2, ',', '.') ?></td>
                                    <td class="text-center"><span class="badge <?= $getBarColorClass($s['pct_labor']) ?>"><?= $s['pct_labor'] ?>%</span></td>
                                </tr>
                                <tr class="table-active border-top border-primary fw-bold">
                                    <td><i class="bi bi-calculator text-primary me-2"></i>TOTAL ACUMULADO</td>
                                    <td class="text-end text-primary">R$ <?= number_format($s['total_spent'], 2, ',', '.') ?></td>
                                    <td class="text-end text-secondary">R$ <?= number_format($s['target_total'], 2, ',', '.') ?></td>
                                    <td class="text-center"><span class="badge <?= $getBarColorClass($s['pct_total']) ?>"><?= $s['pct_total'] ?>%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header bg-dark border-secondary text-white fw-bold">
                    <i class="bi bi-pie-chart-fill text-warning me-2"></i>Distribuição de Custos de Materiais
                </div>
                <div class="card-body">
                    <?php if (empty($categoryBreakdown)): ?>
                    <p class="text-secondary text-center py-4 mb-0">Nenhum custo lançado até o momento.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush bg-dark">
                        <?php foreach ($categoryBreakdown as $cat):
                            $pctCat = $s['cost_total'] > 0 ? round(($cat['total'] / $s['cost_total']) * 100, 1) : 0;
                        ?>
                        <div class="list-group-item bg-dark text-white border-secondary px-0">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold"><?= htmlspecialchars($cat['category']) ?></span>
                                <span>R$ <?= number_format($cat['total'], 2, ',', '.') ?> (<?= $pctCat ?>%)</span>
                            </div>
                            <div class="progress bg-secondary" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pctCat ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($tab === 'timeline'): ?>
    <!-- ABA CRONOGRAMA & TAREFAS -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-white mb-0"><i class="bi bi-diagram-3 me-2"></i>Cronograma de Execução por Etapas</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPhaseModal">
            <i class="bi bi-plus-lg me-1"></i> Adicionar Etapa
        </button>
    </div>

    <?php if (empty($phases)): ?>
    <div class="card bg-dark border-secondary text-center py-4">
        <p class="text-secondary mb-0">Nenhuma etapa cadastrada nesta obra.</p>
    </div>
    <?php else: ?>
    <div class="accordion" id="phasesAccordion">
        <?php foreach ($phases as $phase): ?>
        <div class="accordion-item bg-dark border-secondary mb-3 rounded overflow-hidden">
            <h2 class="accordion-header" id="headingPhase<?= $phase['id'] ?>">
                <button class="accordion-button bg-dark text-white border-bottom border-secondary py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePhase<?= $phase['id'] ?>" aria-expanded="true">
                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100 me-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">Etapa <?= $phase['order_num'] ?></span>
                            <span class="fw-bold text-white fs-6"><?= htmlspecialchars($phase['name']) ?></span>
                            <?php if ($phase['sector']): ?>
                            <span class="badge bg-dark border border-secondary text-secondary"><?= htmlspecialchars($phase['sector']) ?></span>
                            <?php endif; ?>
                            <?php if ($phase['is_delayed']): ?>
                            <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Atrasada</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="small text-secondary">
                                <?= $phase['planned_start'] ? date('d/m/Y', strtotime($phase['planned_start'])) : '' ?>
                                <?= $phase['planned_end'] ? ' à ' . date('d/m/Y', strtotime($phase['planned_end'])) : '' ?>
                            </span>
                            <div class="d-flex align-items-center gap-2" style="width: 150px;">
                                <div class="progress bg-secondary flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: <?= $phase['progress_pct'] ?>%"></div>
                                </div>
                                <span class="small fw-bold text-info"><?= $phase['progress_pct'] ?>%</span>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapsePhase<?= $phase['id'] ?>" class="accordion-collapse collapse show" data-bs-parent="#phasesAccordion">
                <div class="accordion-body bg-dark text-light p-3">
                    
                    <?php if (!empty($phase['delay_reason'])): ?>
                    <div class="alert alert-danger bg-dark border-danger text-danger py-2 small mb-3">
                        <strong><i class="bi bi-exclamation-triangle me-1"></i> Motivo do Atraso:</strong> <?= htmlspecialchars($phase['delay_reason']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Ações da Etapa -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-secondary fw-bold">TAREFAS DESTA ETAPA</span>
                        <button class="btn btn-outline-primary btn-sm py-1" onclick="openAddTaskModal(<?= $phase['id'] ?>, '<?= htmlspecialchars($phase['name'], ENT_QUOTES) ?>')">
                            <i class="bi bi-plus-lg me-1"></i> Nova Tarefa
                        </button>
                    </div>

                    <!-- Lista de Tarefas -->
                    <?php if (empty($phase['tasks'])): ?>
                    <p class="text-secondary small fst-italic mb-0">Nenhuma tarefa nesta etapa.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-secondary small">
                                    <th>Tarefa</th>
                                    <th>Responsável</th>
                                    <th>Status</th>
                                    <th>Dias Plan.</th>
                                    <th>Horas Plan.</th>
                                    <th>Horas Real.</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($phase['tasks'] as $t):
                                    $taskBadges = [
                                        'completed' => '<span class="badge bg-success">Concluída</span>',
                                        'in_progress' => '<span class="badge bg-primary">Em Andamento</span>',
                                        'delayed' => '<span class="badge bg-danger">Atrasada</span>'
                                    ];
                                    $tStatusBadge = $taskBadges[$t['status']] ?? '<span class="badge bg-secondary">Pendente</span>';
                                ?>
                                <tr>
                                    <td class="fw-bold text-white"><?= htmlspecialchars($t['title']) ?></td>
                                    <td>
                                        <?php if ($t['assigned_name']): ?>
                                        <span class="badge" style="background-color: <?= $t['avatar_color'] ?: '#6c757d' ?>"><?= htmlspecialchars($t['assigned_name']) ?></span>
                                        <?php else: ?>
                                        <span class="text-secondary small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $tStatusBadge ?></td>
                                    <td class="small text-secondary"><?= $t['planned_days'] ?>d</td>
                                    <td class="small text-secondary"><?= $t['hours_estimated'] ?>h</td>
                                    <td class="small text-info fw-bold"><?= $t['hours_spent'] ?>h</td>
                                    <td class="text-end">
                                        <button class="btn btn-link text-warning p-0 me-2 btn-sm" onclick='openEditTaskModal(<?= json_encode($t) ?>)' title="Editar"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=delete_task" class="d-inline" onsubmit="return confirm('Excluir esta tarefa?')">
                                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                            <button type="submit" class="btn btn-link text-danger p-0 btn-sm" title="Excluir"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php elseif ($tab === 'financial'): ?>
    <!-- ABA FINANCEIRO & CUSTOS -->
    
    <!-- Formulário Rápido Inline de Lançamento de Custo -->
    <div class="card bg-dark border-primary mb-4">
        <div class="card-header bg-dark border-primary text-primary fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Lançar Custo / Despesa de Obra (Rápido)
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=add_cost" class="row g-3">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                
                <div class="col-md-5">
                    <label class="form-label text-white small">Descrição da Despesa <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Ex: Compra de disjuntores e bornes" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small">Categoria <span class="text-danger">*</span></label>
                    <select name="category" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-white small">Valor (R$) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="0,00" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check-lg me-1"></i> Lançar Custo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Custos -->
    <div class="card bg-dark border-secondary">
        <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0"><i class="bi bi-receipt me-2"></i>Histórico de Custos Lançados</h5>
            <span class="badge bg-primary fs-6">Total: R$ <?= number_format($s['cost_total'], 2, ',', '.') ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($costs)): ?>
            <p class="text-secondary text-center py-4 mb-0">Nenhum custo lançado para esta obra.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small">
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Lançado por</th>
                            <th class="text-end">Valor</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($costs as $cost): ?>
                        <tr>
                            <td class="small text-secondary"><?= date('d/m/Y', strtotime($cost['cost_date'])) ?></td>
                            <td><span class="badge bg-dark border border-secondary text-warning"><?= htmlspecialchars($cost['category']) ?></span></td>
                            <td class="fw-bold text-white"><?= htmlspecialchars($cost['description']) ?></td>
                            <td class="small text-secondary"><?= htmlspecialchars($cost['created_by_name']) ?></td>
                            <td class="text-end fw-bold text-success">R$ <?= number_format($cost['amount'], 2, ',', '.') ?></td>
                            <td class="text-end">
                                <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=delete_cost" class="d-inline" onsubmit="return confirm('Excluir este lançamento de custo?')">
                                    <input type="hidden" name="cost_id" value="<?= $cost['id'] ?>">
                                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="btn btn-link text-danger p-0 btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($tab === 'purchases'): ?>
    <!-- ABA COMPRAS (PIPELINE KANBAN) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-white mb-0"><i class="bi bi-cart3 me-2"></i>Pipeline de Compras da Obra</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
            <i class="bi bi-plus-lg me-1"></i> Solicitada Nova Compra
        </button>
    </div>

    <div class="row row-cols-1 row-cols-md-4 g-3">
        <?php
        $columns = [
            'requested' => ['title' => 'Solicitadas', 'color' => 'secondary'],
            'quoted' => ['title' => 'Em Cotação', 'color' => 'info'],
            'purchased' => ['title' => 'Compradas', 'color' => 'primary'],
            'delivered' => ['title' => 'Entregues', 'color' => 'success']
        ];
        foreach ($columns as $statusKey => $col):
            $colPurchases = array_filter($purchases, function($p) use ($statusKey) { return $p['status'] === $statusKey; });
        ?>
        <div class="col">
            <div class="kanban-col border border-secondary">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-<?= $col['color'] ?> mb-0"><?= $col['title'] ?></h6>
                    <span class="badge bg-<?= $col['color'] ?>"><?= count($colPurchases) ?></span>
                </div>

                <?php foreach ($colPurchases as $p):
                    $isDelayed = !empty($p['delay_reason']);
                ?>
                <div class="purchase-card <?= $isDelayed ? 'delayed' : '' ?>">
                    <div class="fw-bold text-white mb-1"><?= htmlspecialchars($p['item_description']) ?></div>
                    <?php if ($p['supplier']): ?>
                    <div class="small text-secondary mb-1"><i class="bi bi-shop me-1"></i><?= htmlspecialchars($p['supplier']) ?></div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between small text-light mb-2">
                        <span>Est: R$ <?= number_format($p['estimated_cost'], 2, ',', '.') ?></span>
                        <?php if ($p['actual_cost'] > 0): ?>
                        <span class="fw-bold text-success">Real: R$ <?= number_format($p['actual_cost'], 2, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($isDelayed): ?>
                    <div class="alert alert-danger bg-dark border-danger text-danger p-1 small mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i> Atraso: <?= htmlspecialchars($p['delay_reason']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Mover Status Select -->
                    <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=update_purchase" class="d-flex gap-1">
                        <input type="hidden" name="purchase_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                        <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary small py-0" onchange="this.form.submit()">
                            <option value="requested" <?= $p['status'] === 'requested' ? 'selected' : '' ?>>Solicitada</option>
                            <option value="quoted" <?= $p['status'] === 'quoted' ? 'selected' : '' ?>>Em Cotação</option>
                            <option value="purchased" <?= $p['status'] === 'purchased' ? 'selected' : '' ?>>Comprada</option>
                            <option value="delivered" <?= $p['status'] === 'delivered' ? 'selected' : '' ?>>Entregue</option>
                        </select>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

<?php elseif ($tab === 'history'): ?>
    <!-- ABA HISTÓRICO / TIMELINE -->
    <div class="card bg-dark border-secondary">
        <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0"><i class="bi bi-clock-history me-2"></i>Histórico & Timeline de Ações</h5>
        </div>
        <div class="card-body">
            <!-- Adicionar Comentário na Timeline -->
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=add_comment" class="mb-4">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <div class="input-group">
                    <input type="text" name="comment" class="form-control bg-dark text-white border-secondary" placeholder="Escreva uma observação / comentário sobre a obra..." required>
                    <button class="btn btn-primary" type="submit"><i class="bi bg-send me-1"></i> Comentar</button>
                </div>
            </form>

            <div class="timeline-container px-2">
                <?php foreach ($timeline as $t): ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                        <strong class="text-light me-2"><?= htmlspecialchars($t['user_name']) ?></strong>
                        <small class="text-secondary"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></small>
                    </div>
                    <p class="text-white small mb-0"><?= htmlspecialchars($t['details']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- MODAIS DO MÓDULO -->

<!-- Modal Adicionar Etapa -->
<div class="modal fade" id="addPhaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Adicionar Nova Etapa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=add_phase">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome da Etapa <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required placeholder="Ex: Montagem dos Painéis">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Setor Responsável</label>
                        <input type="text" name="sector" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Elétrica / Mecânica">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Início Planejado</label>
                            <input type="date" name="planned_start" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Término Planejado</label>
                            <input type="date" name="planned_end" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Etapa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Adicionar Tarefa -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Nova Tarefa para <span id="modalPhaseNameTitle" class="text-primary"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=add_task">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <input type="hidden" name="phase_id" id="modalTaskPhaseId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título da Tarefa <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control bg-dark text-white border-secondary" required placeholder="Ex: Elaborar Diagrama Elétrico">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsável</label>
                        <select name="assigned_to" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- SELECIONAR RESPONSÁVEL --</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Início Planejado</label>
                            <input type="date" name="planned_start" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Término Planejado</label>
                            <input type="date" name="planned_end" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horas Estimadas</label>
                        <input type="number" step="0.5" name="hours_estimated" class="form-control bg-dark text-white border-secondary" placeholder="Ex: 8">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Tarefa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Tarefa -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Editar Tarefa da Etapa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=update_task">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <input type="hidden" name="task_id" id="editTaskId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título da Tarefa <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editTaskTitle" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Responsável</label>
                            <select name="assigned_to" id="editTaskAssignedTo" class="form-select bg-dark text-white border-secondary">
                                <option value="">-- SELECIONAR RESPONSÁVEL --</option>
                                <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="editTaskStatus" class="form-select bg-dark text-white border-secondary">
                                <option value="pending">Pendente</option>
                                <option value="in_progress">Em Andamento</option>
                                <option value="completed">Concluída</option>
                                <option value="delayed">Atrasada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Início Planejado</label>
                            <input type="date" name="planned_start" id="editTaskPlannedStart" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Término Planejado</label>
                            <input type="date" name="planned_end" id="editTaskPlannedEnd" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Horas Estimadas</label>
                            <input type="number" step="0.5" name="hours_estimated" id="editTaskHoursEstimated" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Horas Trabalhadas (Real)</label>
                            <input type="number" step="0.5" name="hours_spent" id="editTaskHoursSpent" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo do Atraso (se houver)</label>
                        <input type="text" name="delay_reason" id="editTaskDelayReason" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Aguardando componente eletrônico">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Adicionar Compra -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Solicitar Compra de Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=add_purchase">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item / Descrição <span class="text-danger">*</span></label>
                        <input type="text" name="item_description" class="form-control bg-dark text-white border-secondary" required placeholder="Ex: Cabo PP 4x2.5mm² (100m)">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Fornecedor</label>
                            <input type="text" name="supplier" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Eletrofort">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Custo Estimado (R$)</label>
                            <input type="number" step="0.01" name="estimated_cost" class="form-control bg-dark text-white border-secondary" placeholder="0,00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Solicitação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Obra -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Editar Obra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=update">
                <input type="hidden" name="id" value="<?= $project['id'] ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome da Obra</label>
                            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($project['name']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select bg-dark text-white border-secondary">
                                <option value="in_progress" <?= $project['status'] === 'in_progress' ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>Concluída</option>
                                <option value="paused" <?= $project['status'] === 'paused' ? 'selected' : '' ?>>Pausada</option>
                                <option value="canceled" <?= $project['status'] === 'canceled' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Target Materiais (R$)</label>
                            <input type="number" step="0.01" name="target_material_budget" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($project['target_material_budget']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Target Mão de Obra (R$)</label>
                            <input type="number" step="0.01" name="target_labor_budget" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($project['target_labor_budget']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Target Total Acumulado (R$)</label>
                            <input type="number" step="0.01" name="target_budget" class="form-control bg-dark text-white border-secondary fw-bold text-primary" value="<?= htmlspecialchars($project['target_budget']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valor/Hora (R$)</label>
                            <input type="number" step="0.01" name="hourly_rate" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($project['hourly_rate']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prazo Estipulado (Meses)</label>
                            <input type="number" name="total_months" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($project['total_months']) ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir Obra -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-danger">Excluir Obra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir permanentemente a obra <strong><?= htmlspecialchars($project['name']) ?></strong>?</p>
                <p class="text-secondary small mb-0">Todas as etapas, tarefas, custos e compras vinculados serão apagados.</p>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=delete">
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openAddTaskModal(phaseId, phaseName) {
    document.getElementById('modalTaskPhaseId').value = phaseId;
    document.getElementById('modalPhaseNameTitle').innerText = phaseName;
    new bootstrap.Modal(document.getElementById('addTaskModal')).show();
}

function openEditTaskModal(task) {
    document.getElementById('editTaskId').value = task.id || '';
    document.getElementById('editTaskTitle').value = task.title || '';
    document.getElementById('editTaskAssignedTo').value = task.assigned_to || '';
    document.getElementById('editTaskStatus').value = task.status || 'pending';
    document.getElementById('editTaskPlannedStart').value = task.planned_start || '';
    document.getElementById('editTaskPlannedEnd').value = task.planned_end || '';
    document.getElementById('editTaskHoursEstimated').value = task.hours_estimated || '';
    document.getElementById('editTaskHoursSpent').value = task.hours_spent || '';
    document.getElementById('editTaskDelayReason').value = task.delay_reason || '';
    new bootstrap.Modal(document.getElementById('editTaskModal')).show();
}
</script>
