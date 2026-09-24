<?php
$isDelayed = false;
if ($task['status'] !== 'done' && strtotime($task['due_date']) < strtotime(date('Y-m-d'))) {
    $isDelayed = true;
}
$isNear = false;
if ($task['status'] !== 'done' && !$isDelayed && strtotime($task['due_date']) <= strtotime('+3 days')) {
    $isNear = true;
}

$dateColor = 'text-secondary';
if ($isDelayed) $dateColor = 'text-danger fw-bold';
if ($isNear) $dateColor = 'text-warning fw-bold';
if ($task['status'] === 'done') $dateColor = 'text-success';

$delayedStyle = $isDelayed ? 'border: 2px solid #ff4444 !important; box-shadow: 0 0 12px rgba(255, 0, 0, 0.8) !important;' : '';
?>
<div class="kanban-card priority-<?= $task['priority'] ?>" data-id="<?= $task['id'] ?>" data-user-id="<?= $task['assigned_to'] ?? '' ?>" data-mentioned-user-id="<?= $task['mentioned_user_id'] ?? '' ?>" onclick="window.location.href='<?= BASE_URL ?>/?page=timeline&id=<?= $task['id'] ?>'" style="<?= $delayedStyle ?>">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge bg-dark border border-secondary"><?= htmlspecialchars($task['department']) ?></span>
        <?php if ($task['original_due_date'] !== $task['due_date']): ?>
            <i class="bi bi-clock-history text-warning" title="Prazo Prorrogado"></i>
        <?php endif; ?>
    </div>
    <h6 class="mb-1 text-light fw-normal"><?= htmlspecialchars($task['title']) ?></h6>
    
    <div class="mt-2 mb-2" style="font-size: 0.75rem; color: #adb5bd;">
        <div><strong>1º Prazo:</strong> <?= date('d/m', strtotime($task['original_due_date'])) ?></div>
        <?php $postponeCount = isset($task['postpone_count']) ? (int)$task['postpone_count'] : 0; ?>
        <?php if ($task['status'] === 'done'): ?>
            <div><strong>Prorrogada:</strong> <?= $postponeCount ?>x</div>
            <?php 
            // Calcula diferença do primeiro prazo até a data de conclusão
            $completedAt = strtotime(date('Y-m-d', strtotime($task['completed_at'])));
            $daysDiff = round(($completedAt - strtotime($task['original_due_date'])) / (60 * 60 * 24));
            $daysText = $daysDiff > 0 ? "+{$daysDiff} dias" : "{$daysDiff} dias";
            $daysColor = $daysDiff > 0 ? "text-danger" : "text-success";
            ?>
            <div><strong>Conclusão:</strong> <span class="<?= $daysColor ?>"><?= $daysText ?></span></div>
        <?php elseif ($postponeCount > 0): ?>
            <div><strong>Prorrogada:</strong> <?= $postponeCount ?>x</div>
            <?php 
            $currentDue = strtotime($task['due_date']);
            $daysDiff = round(($currentDue - strtotime($task['original_due_date'])) / (60 * 60 * 24));
            ?>
            <div><strong>Atraso total:</strong> +<?= $daysDiff ?> dias</div>
        <?php endif; ?>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="<?= $dateColor ?>">
            <i class="bi bi-calendar-event"></i> <?= date('d/m', strtotime($task['due_date'])) ?>
        </small>
        <div class="d-flex gap-1">
            <?php if (!empty($task['mentions'])): ?>
                <?php foreach ($task['mentions'] as $mention): ?>
                    <div class="avatar shadow-sm border border-2 border-primary" title="Mencionado em comentário: <?= htmlspecialchars($mention['name']) ?>" style="width:24px; height:24px; border-radius:50%; background-color:<?= htmlspecialchars($mention['avatar_color'] ?? '#0d6efd') ?>; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:bold; color:white;">
                        <?= strtoupper(substr($mention['name'], 0, 1)) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="avatar shadow-sm" title="<?= htmlspecialchars($task['assigned_name'] ?? 'Sem Responsável') ?>" style="width:24px; height:24px; border-radius:50%; background-color:<?= $task['avatar_color'] ?? '#6c757d' ?>; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:bold; color:white;">
                <?= strtoupper(substr($task['assigned_name'] ?? '?', 0, 1)) ?>
            </div>
        </div>
    </div>
</div>
