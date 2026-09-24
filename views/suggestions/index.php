<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-light mb-0"><i class="bi bi-lightbulb text-warning"></i> Sugestões de Melhoria</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSuggestionModal">
        <i class="bi bi-plus-lg"></i> Nova Sugestão
    </button>
</div>

<div class="row">
    <?php if (empty($suggestions)): ?>
        <div class="col-12">
            <div class="alert alert-secondary text-center">Nenhuma sugestão cadastrada ainda.</div>
        </div>
    <?php else: ?>
        <?php foreach ($suggestions as $s): ?>
            <?php 
                $statusConfig = [
                    'analysis' => ['Em Análise', 'warning'],
                    'accepted' => ['Aceita (Virou Tarefa)', 'success'],
                    'rejected' => ['Rejeitada', 'danger'],
                    'postponed' => ['Adiada', 'secondary']
                ];
                $label = $statusConfig[$s['status']][0];
                $color = $statusConfig[$s['status']][1];
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card bg-dark border-secondary h-100 shadow-sm">
                    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                        <small class="text-secondary"><?= date('d/m/Y', strtotime($s['created_at'])) ?></small>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-light"><?= htmlspecialchars($s['title']) ?></h5>
                        <h6 class="card-subtitle mb-3 text-info"><i class="bi bi-box"></i> Produto: <?= htmlspecialchars($s['product']) ?></h6>
                        <p class="card-text text-secondary" style="font-size: 0.9rem;"><?= nl2br(htmlspecialchars($s['description'])) ?></p>
                    </div>
                    <div class="card-footer border-secondary bg-transparent d-flex flex-column gap-2">
                        <div class="text-muted small"><i class="bi bi-person"></i> Por: <?= htmlspecialchars($s['created_name']) ?></div>
                        
                        <?php if ($s['status'] === 'analysis'): ?>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-sm btn-success flex-fill" data-bs-toggle="modal" data-bs-target="#acceptModal<?= $s['id'] ?>">
                                    <i class="bi bi-check-lg"></i> Aceitar
                                </button>
                                <form action="<?= BASE_URL ?>/?page=suggestions&action=change_status" method="POST" class="flex-fill d-flex gap-2">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" name="status" value="postponed" class="btn btn-sm btn-secondary flex-fill" title="Adiar"><i class="bi bi-clock"></i></button>
                                    <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger flex-fill" title="Rejeitar"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </div>
                        <?php elseif ($s['status'] === 'accepted'): ?>
                            <div class="alert alert-success bg-success bg-opacity-10 border-success p-2 mb-0 small text-center text-success">
                                <i class="bi bi-link-45deg"></i> Vinculada à tarefa:<br>
                                <strong><?= htmlspecialchars($s['task_title']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modal Aceitar Sugestão (Transformar em Tarefa) -->
            <?php if ($s['status'] === 'analysis'): ?>
            <div class="modal fade" id="acceptModal<?= $s['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-light border-success">
                        <form action="<?= BASE_URL ?>/?page=suggestions&action=accept" method="POST">
                            <input type="hidden" name="suggestion_id" value="<?= $s['id'] ?>">
                            <div class="modal-header border-success bg-success bg-opacity-10">
                                <h5 class="modal-title text-success"><i class="bi bi-magic"></i> Transformar em Tarefa</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-secondary mb-4">Ao aceitar, esta sugestão virará uma tarefa real no Kanban.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label">Título da Tarefa</label>
                                    <input type="text" name="title" class="form-control bg-dark text-light border-secondary" value="[Sugestão] <?= htmlspecialchars($s['title']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Descrição</label>
                                    <textarea name="description" class="form-control bg-dark text-light border-secondary" rows="3"><?= htmlspecialchars($s['description']) ?></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Departamento</label>
                                        <select name="department" class="form-select bg-dark text-light border-secondary" required>
                                            <option value="TI">TI</option>
                                            <option value="TA">TA (Automação)</option>
                                            <option value="COMERCIAL">Comercial</option>
                                            <option value="PD">P&D</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Prioridade</label>
                                        <select name="priority" class="form-select bg-dark text-light border-secondary">
                                            <option value="low">Baixa</option>
                                            <option value="medium" selected>Média</option>
                                            <option value="high">Alta</option>
                                            <option value="urgent">Urgente</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Responsável</label>
                                        <select name="assigned_to" class="form-select bg-dark text-light border-secondary">
                                            <option value="">Sem responsável</option>
                                            <?php foreach($users as $u): ?>
                                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Prazo de Entrega</label>
                                        <input type="date" name="due_date" class="form-control bg-dark text-light border-secondary" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer border-success">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Gerar Tarefa</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Nova Sugestão -->
<div class="modal fade" id="newSuggestionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-primary">
            <form action="<?= BASE_URL ?>/?page=suggestions&action=create" method="POST">
                <div class="modal-header border-primary bg-primary bg-opacity-10">
                    <h5 class="modal-title text-primary"><i class="bi bi-lightbulb"></i> Nova Sugestão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título da Melhoria</label>
                        <input type="text" name="title" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Adicionar modo escuro no App" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Produto / Sistema Afetado</label>
                        <input type="text" name="product" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Keepin App Flutter, Firmware Placa, etc" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição Detalhada</label>
                        <textarea name="description" class="form-control bg-dark text-light border-secondary" rows="4" placeholder="Explique por que esta melhoria é necessária e como ela deve funcionar..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-primary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Sugestão</button>
                </div>
            </form>
        </div>
    </div>
</div>
