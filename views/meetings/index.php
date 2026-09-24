<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-light mb-0">Atas de Reunião</h1>
    <button type="button" class="btn btn-info text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#newMeetingModal">
        <i class="bi bi-plus-lg"></i> Nova Reunião
    </button>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if(empty($meetings)): ?>
            <div class="alert alert-secondary text-center">Nenhuma reunião registrada ainda.</div>
        <?php else: ?>
            <?php foreach($meetings as $meeting): ?>
                <div class="card bg-dark border-secondary mb-3">
                    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-light"><?= htmlspecialchars($meeting['title']) ?></h5>
                        <span class="badge bg-secondary"><?= date('d/m/Y', strtotime($meeting['date'])) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="text-secondary mb-3">
                            <i class="bi bi-people"></i> Registrado por: <?= htmlspecialchars($meeting['created_name']) ?>
                        </div>
                        <h6 class="text-light">Anotações / Decisões:</h6>
                        <p class="text-light bg-secondary bg-opacity-10 p-3 rounded border border-secondary">
                            <?= nl2br(htmlspecialchars($meeting['notes'])) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <div class="card border-danger mb-3 bg-dark">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle"></i> Tarefas Atrasadas Atualmente
            </div>
            <ul class="list-group list-group-flush">
                <?php if(empty($delayedTasks)): ?>
                    <li class="list-group-item bg-dark text-success border-secondary">Nenhuma tarefa atrasada! 🎉</li>
                <?php else: ?>
                    <?php foreach($delayedTasks as $dt): ?>
                        <li class="list-group-item bg-dark text-light border-secondary">
                            <div class="fw-bold text-danger"><?= htmlspecialchars($dt['title']) ?></div>
                            <small class="text-secondary">Responsável: <?= htmlspecialchars($dt['assigned_name'] ?? 'Nenhum') ?> | Venceu: <?= date('d/m', strtotime($dt['due_date'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Nova Reunião -->
<div class="modal fade" id="newMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_URL ?>/?page=meetings&action=create" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Registrar Reunião</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Título da Reunião</label>
                            <input type="text" name="title" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Reunião Semanal 07/07" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data</label>
                            <input type="date" name="date" class="form-control bg-dark text-light border-secondary" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Participantes</label>
                        <select name="participants[]" class="form-select bg-dark text-light border-secondary" multiple style="height: 100px;">
                            <?php foreach($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-secondary">Segure CTRL para selecionar múltiplos.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ata / Decisões da Reunião</label>
                        <textarea name="notes" class="form-control bg-dark text-light border-secondary" rows="6" placeholder="O que foi discutido, tarefas cobradas, etc." required></textarea>
                    </div>

                    <?php if(!empty($delayedTasks)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-light mt-3">
                        <i class="bi bi-exclamation-triangle"></i> Atenção: Lembre-se de cobrar as <strong><?= count($delayedTasks) ?></strong> tarefas que estão atrasadas!
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-dark fw-bold">Salvar Reunião</button>
                </div>
            </form>
        </div>
    </div>
</div>
