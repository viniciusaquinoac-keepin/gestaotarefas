<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Reunião Semanal</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; background: #fff; }
        h1, h2, h3 { color: #000; margin-bottom: 10px; }
        h1 { border-bottom: 2px solid #000; padding-bottom: 5px; font-size: 20px; }
        h2 { font-size: 16px; margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .item { margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; page-break-inside: avoid; }
        .title { font-weight: bold; font-size: 14px; margin-bottom: 5px; }
        .meta { font-size: 11px; color: #666; margin-bottom: 10px; }
        .notes { font-size: 12px; margin-bottom: 10px; white-space: pre-wrap; }
        .history { border-left: 3px solid #007bff; padding-left: 10px; margin-top: 10px; }
        .history-item { margin-bottom: 5px; font-size: 11px; }
        .history-date { font-weight: bold; color: #555; }
        .badge { display: inline-block; padding: 2px 5px; background: #eee; border-radius: 3px; font-size: 10px; text-transform: uppercase; }
        .badge-danger { background: #dc3545; color: #fff; }
        .badge-warning { background: #fd7e14; color: #fff; }
        .user-group { margin-top: 20px; color: #444; border-bottom: 1px dashed #ccc; padding-bottom: 5px; font-size: 16px; font-weight: bold; margin-bottom: 15px; }
        @media print {
            body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0d6efd; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: bold;">Imprimir / Salvar PDF</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; margin-left: 10px;">Fechar</button>
    </div>

    <h1>Relatório de Acompanhamento Semanal</h1>
    <p><strong>Período Filtrado:</strong> <?= htmlspecialchars($periodoLabel) ?></p>
    <p>Gerado em: <?= date('d/m/Y H:i') ?></p>

    <?php $hoje = date('Y-m-d'); ?>
    <h2>Tarefas</h2>
    <?php if (empty($tasks)): ?>
        <p>Nenhuma tarefa no período.</p>
    <?php else: ?>
        <?php $currentUser = -1; ?>
        <?php foreach ($tasks as $task): ?>
            <?php if ($currentUser !== $task['assigned_name']): ?>
                <?php $currentUser = $task['assigned_name']; ?>
                <div class="user-group">Responsável: <?= htmlspecialchars($currentUser ?? 'Sem Responsável') ?></div>
            <?php endif; ?>
            <div class="item">
                <div class="title">
                    Tarefa: <?= htmlspecialchars($task['title']) ?> 
                    <span class="badge"><?= htmlspecialchars($task['status']) ?></span>
                    <?php if ($task['status'] !== 'done' && $task['due_date'] < $hoje): ?>
                        <span class="badge badge-danger">Atrasado</span>
                    <?php endif; ?>
                    <?php if ($task['original_due_date'] !== $task['due_date']): ?>
                        <span class="badge badge-warning">Prorrogado</span>
                    <?php endif; ?>
                </div>
                <div class="meta">
                    Atribuído a: <?= htmlspecialchars($task['assigned_name'] ?? 'Nenhum') ?> | 
                    Depto: <?= htmlspecialchars($task['department']) ?> | 
                    Prazo Inicial: <?= date('d/m/Y', strtotime($task['original_due_date'])) ?> | 
                    Prazo Atual: <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                    <?php if ($task['completed_at']): ?> | Concluído em: <?= date('d/m/Y', strtotime($task['completed_at'])) ?><?php endif; ?>
                </div>
                <?php if (!empty($task['description'])): ?>
                    <div class="notes"><strong>Descrição:</strong> <?= htmlspecialchars($task['description']) ?></div>
                <?php endif; ?>

                <?php if (!empty($task['history'])): ?>
                    <div class="history">
                        <strong>Histórico (Observações / Prorrogações):</strong>
                        <?php foreach ($task['history'] as $h): ?>
                            <div class="history-item">
                                <span class="history-date"><?= date('d/m H:i', strtotime($h['created_at'])) ?> (<?= htmlspecialchars($h['user_name']) ?>):</span> 
                                <?php if ($h['action'] === 'postponed'): ?>
                                    Prorrogado de <?= date('d/m', strtotime($h['old_value'])) ?> para <?= date('d/m', strtotime($h['new_value'])) ?>. Motivo: <?= htmlspecialchars($h['comment']) ?>
                                <?php elseif ($h['action'] === 'status_change'): ?>
                                    Status alterado de <?= htmlspecialchars($h['old_value']) ?> para <?= htmlspecialchars($h['new_value']) ?>. <?= htmlspecialchars($h['comment']) ?>
                                <?php elseif ($h['action'] === 'comment'): ?>
                                    <?= htmlspecialchars($h['comment']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2>Comercial (Leads)</h2>
    <?php if (empty($leads)): ?>
        <p>Nenhum lead no período.</p>
    <?php else: ?>
        <?php $currentUserLead = -1; ?>
        <?php foreach ($leads as $lead): ?>
            <?php if ($currentUserLead !== $lead['assigned_name']): ?>
                <?php $currentUserLead = $lead['assigned_name']; ?>
                <div class="user-group">Responsável: <?= htmlspecialchars($currentUserLead ?? 'Sem Responsável') ?></div>
            <?php endif; ?>
            <div class="item">
                <div class="title">
                    Lead: <?= htmlspecialchars($lead['name']) ?> (<?= htmlspecialchars($lead['company']) ?>) 
                    <span class="badge"><?= htmlspecialchars($lead['status']) ?></span>
                    <?php if (!in_array($lead['status'], ['closed_won', 'closed_lost']) && $lead['next_contact_date'] && $lead['next_contact_date'] < $hoje): ?>
                        <span class="badge badge-danger">Atrasado</span>
                    <?php endif; ?>
                </div>
                <div class="meta">
                    Atribuído a: <?= htmlspecialchars($lead['assigned_name'] ?? 'Nenhum') ?> | 
                    Valor Est.: R$ <?= number_format($lead['estimated_value'], 2, ',', '.') ?> | 
                    Próx. Contato: <?= $lead['next_contact_date'] ? date('d/m/Y', strtotime($lead['next_contact_date'])) : 'N/A' ?>
                </div>
                <?php if (!empty($lead['notes'])): ?>
                    <div class="history">
                        <strong>Observações / Histórico de Contatos:</strong>
                        <div class="notes"><?= htmlspecialchars($lead['notes']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // Imprime automaticamente ao abrir
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
