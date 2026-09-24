<?php
$currentStatus = $_GET['status'] ?? 'in_progress';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0 text-white"><i class="bi bi-building text-primary me-2"></i>Gestão de Obras</h2>
        <p class="text-secondary small mb-0">Controle financeiro (Materiais vs Mão de Obra), compras e timeline de execução</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (isAdmin()): ?>
        <a href="<?= BASE_URL ?>/?page=projects&action=settings" class="btn btn-outline-secondary">
            <i class="bi bi-gear me-1"></i> Configurações
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/?page=projects&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nova Obra
        </a>
    </div>
</div>

<!-- Filtros de Status -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'in_progress' ? 'active' : '' ?>" href="<?= BASE_URL ?>/?page=projects&status=in_progress">
            <i class="bi bi-play-circle me-1"></i> Em Andamento
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'completed' ? 'active' : '' ?>" href="<?= BASE_URL ?>/?page=projects&status=completed">
            <i class="bi bi-check-circle me-1"></i> Concluídas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'all' ? 'active' : '' ?>" href="<?= BASE_URL ?>/?page=projects&status=all">
            <i class="bi bi-collection me-1"></i> Todas as Obras
        </a>
    </li>
</ul>

<?php
$getBarColor = function($pct) {
    if ($pct > 100) return 'bg-danger';
    if ($pct > 85) return 'bg-warning text-dark';
    return 'bg-success';
};
?>
<?php if (empty($projects)): ?>
<div class="card bg-dark border-secondary text-center py-5">
    <div class="card-body">
        <i class="bi bi-building-dash text-secondary display-3 mb-3"></i>
        <h5 class="text-white">Nenhuma obra encontrada</h5>
        <p class="text-secondary mb-4">Comece criando uma nova obra ou importando a partir de um Lead Comercial.</p>
        <a href="<?= BASE_URL ?>/?page=projects&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Criar Primeira Obra
        </a>
    </div>
</div>
<?php else: ?>
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
    <?php foreach ($projects as $proj):
        $s = is_array($proj['summary'] ?? null) ? $proj['summary'] : [
            'cost_total' => 0, 'hours_spent' => 0, 'hours_estimated' => 0, 'labor_cost_spent' => 0,
            'total_spent' => 0, 'target_material' => 0, 'target_labor' => 0, 'target_total' => 0,
            'pct_material' => 0, 'pct_labor' => 0, 'pct_total' => 0, 'total_tasks' => 0,
            'completed_tasks' => 0, 'progress_pct' => 0, 'purchases_total' => 0,
            'purchases_delivered' => 0, 'purchases_delayed' => 0
        ];
        $statusBadges = [
            'in_progress' => '<span class="badge bg-primary">Em Andamento</span>',
            'completed' => '<span class="badge bg-success">Concluída</span>',
            'paused' => '<span class="badge bg-warning text-dark">Pausada</span>',
            'canceled' => '<span class="badge bg-danger">Cancelada</span>'
        ];
        $statusBadge = $statusBadges[$proj['status'] ?? 'in_progress'] ?? '<span class="badge bg-secondary">' . htmlspecialchars($proj['status'] ?? '') . '</span>';
    ?>
    <div class="col">
        <div class="card bg-dark border-secondary h-100 shadow-sm hover-shadow">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title text-white mb-0 text-truncate me-2">
                    <a href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $proj['id'] ?>" class="text-white text-decoration-none">
                        <?= htmlspecialchars($proj['name']) ?>
                    </a>
                </h5>
                <?= $statusBadge ?>
            </div>
            <div class="card-body">
                <p class="text-secondary small mb-3"><i class="bi bi-person me-1"></i>Cliente: <strong class="text-light"><?= htmlspecialchars($proj['client'] ?: 'Não informado') ?></strong></p>
                
                <!-- Barra de Progresso de Execução de Tarefas -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary"><i class="bi bi-check-all me-1"></i>Progresso Físico</span>
                        <span class="fw-bold text-info"><?= $s['progress_pct'] ?>% (<?= $s['completed_tasks'] ?>/<?= $s['total_tasks'] ?>)</span>
                    </div>
                    <div class="progress bg-secondary" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $s['progress_pct'] ?>%"></div>
                    </div>
                </div>

                <hr class="border-secondary my-3">

                <!-- 1. Materiais Bar -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary"><i class="bi bi-box-seam me-1"></i>Materiais (Custo vs Target)</span>
                        <span class="fw-bold text-white">R$ <?= number_format($s['cost_total'], 2, ',', '.') ?> / R$ <?= number_format($s['target_material'], 2, ',', '.') ?> (<?= $s['pct_material'] ?>%)</span>
                    </div>
                    <div class="progress bg-secondary" style="height: 6px;">
                        <div class="progress-bar <?= $getBarColor($s['pct_material']) ?>" role="progressbar" style="width: <?= min(100, $s['pct_material']) ?>%"></div>
                    </div>
                </div>

                <!-- 2. Mão de Obra Bar -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary"><i class="bi bi-clock-history me-1"></i>Mão de Obra (Horas)</span>
                        <span class="fw-bold text-white">R$ <?= number_format($s['labor_cost_spent'], 2, ',', '.') ?> / R$ <?= number_format($s['target_labor'], 2, ',', '.') ?> (<?= $s['pct_labor'] ?>%)</span>
                    </div>
                    <div class="progress bg-secondary" style="height: 6px;">
                        <div class="progress-bar <?= $getBarColor($s['pct_labor']) ?>" role="progressbar" style="width: <?= min(100, $s['pct_labor']) ?>%"></div>
                    </div>
                </div>

                <!-- 3. Acumulado Total Bar -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary"><i class="bi bi-calculator me-1"></i>Total Acumulado</span>
                        <span class="fw-bold text-primary">R$ <?= number_format($s['total_spent'], 2, ',', '.') ?> / R$ <?= number_format($s['target_total'], 2, ',', '.') ?> (<?= $s['pct_total'] ?>%)</span>
                    </div>
                    <div class="progress bg-secondary" style="height: 8px;">
                        <div class="progress-bar <?= $getBarColor($s['pct_total']) ?>" role="progressbar" style="width: <?= min(100, $s['pct_total']) ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center py-2">
                <small class="text-secondary"><i class="bi bi-calendar3 me-1"></i>Início: <?= $proj['start_date'] ? date('d/m/Y', strtotime($proj['start_date'])) : '-' ?></small>
                <a href="<?= BASE_URL ?>/?page=projects&action=view&id=<?= $proj['id'] ?>" class="btn btn-sm btn-outline-primary">
                    Ver Obra <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
