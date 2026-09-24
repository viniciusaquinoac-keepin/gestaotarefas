<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/?page=leads" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
        <h1 class="h3 text-light mb-0">Timeline: <?= htmlspecialchars($lead['name']) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=convert_lead" class="d-inline">
            <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-building-add"></i> Converter em Obra
            </button>
        </form>
        <button class="btn btn-info btn-sm text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#commentModal">
            <i class="bi bi-chat-text"></i> Adicionar Observação
        </button>
        <button class="btn btn-warning btn-sm text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#postponeModal">
            <i class="bi bi-clock-history"></i> Prorrogar
        </button>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="bi bi-trash"></i> Excluir
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card bg-dark border-secondary">
            <div class="card-body">
                <h5 class="card-title text-light mb-4">Histórico de Eventos</h5>
                
                <div class="timeline-container ps-3 border-start border-secondary position-relative">
                    
                    <!-- Evento de Criação (Sempre o primeiro) -->
                    <div class="timeline-item mb-4 position-relative">
                        <span class="position-absolute translate-middle p-2 bg-primary border border-light rounded-circle" style="left: -17px;"></span>
                        <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></div>
                        <div class="text-light"><strong>Lead criado</strong></div>
                        <div class="text-secondary small mt-1">Contato inicial: <?= $lead['next_contact_date'] ? date('d/m/Y', strtotime($lead['next_contact_date'])) : 'Não definido' ?></div>
                    </div>

                    <!-- Iterar sobre o histórico real -->
                    <?php foreach($history as $h): ?>
                        <?php 
                        $iconClass = 'bg-secondary';
                        if ($h['action'] === 'status_change') $iconClass = 'bg-info';
                        if ($h['action'] === 'postponed') $iconClass = 'bg-warning';
                        if ($h['action'] === 'comment') $iconClass = 'bg-light';
                        ?>
                        <div class="timeline-item mb-4 position-relative">
                            <span class="position-absolute translate-middle p-2 <?= $iconClass ?> border border-light rounded-circle" style="left: -17px;"></span>
                            <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?> - <em><?= htmlspecialchars($h['user_name']) ?></em></div>
                            
                            <?php if ($h['action'] === 'status_change'): ?>
                                <div class="text-light">Moveu o lead de <strong><?= $h['old_value'] ?></strong> para <strong><?= $h['new_value'] ?></strong></div>
                            <?php elseif ($h['action'] === 'postponed'): ?>
                                <div class="text-warning fw-bold">Prorrogou o prazo de contato!</div>
                                <div class="text-light">De: <?= $h['old_value'] ? date('d/m/Y', strtotime($h['old_value'])) : 'Sem data' ?> Para: <?= $h['new_value'] ? date('d/m/Y', strtotime($h['new_value'])) : 'Sem data' ?></div>
                                <div class="bg-secondary bg-opacity-25 p-2 rounded mt-2 border border-secondary text-light">
                                    <i class="bi bi-chat-quote me-2"></i> <?= htmlspecialchars($h['comment']) ?>
                                </div>
                            <?php elseif ($h['action'] === 'comment'): ?>
                                <div class="text-info fw-bold d-flex justify-content-between align-items-center">
                                    <span>
                                        Adicionou uma observação:
                                        <?php if (!empty($h['mentioned_user_id'])): ?>
                                            <span class="badge bg-primary ms-2"><i class="bi bi-at"></i><?= htmlspecialchars($h['mentioned_user_name']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <form action="<?= BASE_URL ?>/?page=leads&action=history_delete" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta observação?');">
                                        <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                                        <input type="hidden" name="history_id" value="<?= $h['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Excluir Observação">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="bg-secondary bg-opacity-25 p-2 rounded mt-2 border border-secondary text-light">
                                    <i class="bi bi-chat-text me-2"></i> <?= htmlspecialchars($h['comment']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($lead['status'] === 'closed_won'): ?>
                        <div class="timeline-item position-relative">
                            <span class="position-absolute translate-middle p-2 bg-success border border-light rounded-circle" style="left: -17px;"></span>
                            <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($lead['updated_at'])) ?></div>
                            <div class="text-success fw-bold">Lead ganho! 🎉</div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-dark border-secondary">
            <div class="card-body">
                <h5 class="card-title text-light">Detalhes do Lead</h5>
                <hr class="border-secondary">
                <p class="text-light mb-1"><strong>Responsável:</strong> <?= htmlspecialchars($lead['assigned_name'] ?? 'Ninguém') ?></p>
                <p class="text-light mb-1"><strong>Empresa:</strong> <?= htmlspecialchars($lead['company'] ?? '-') ?></p>
                <p class="text-light mb-1"><strong>Telefone:</strong> <?= htmlspecialchars($lead['phone'] ?? '-') ?></p>
                <p class="text-light mb-1"><strong>Email:</strong> <?= htmlspecialchars($lead['email'] ?? '-') ?></p>
                <p class="text-light mb-1"><strong>Valor Estimado:</strong> R$ <?= number_format($lead['estimated_value'], 2, ',', '.') ?></p>
                <p class="text-light mb-1"><strong>Status Atual:</strong> <?= strtoupper($lead['status']) ?></p>
                <p class="text-light mb-1"><strong>Próximo Contato:</strong> <span class="<?= $lead['next_contact_date'] && strtotime($lead['next_contact_date']) < time() ? 'text-danger fw-bold' : '' ?>"><?= $lead['next_contact_date'] ? date('d/m/Y', strtotime($lead['next_contact_date'])) : 'Sem data definida' ?></span></p>
                <hr class="border-secondary">
                <h6 class="text-light">Observações Iniciais</h6>
                <p class="text-secondary"><?= nl2br(htmlspecialchars($lead['notes'] ?? 'Sem observações.')) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Prorrogar -->
<div class="modal fade" id="postponeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_URL ?>/?page=leads&action=postpone" method="POST">
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <div class="modal-header border-secondary bg-warning bg-opacity-25">
                    <h5 class="modal-title text-warning"><i class="bi bi-clock-history"></i> Prorrogar Próximo Contato</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nova Data de Contato</label>
                        <input type="date" name="new_date" class="form-control bg-dark text-light border-secondary" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-warning fw-bold">Motivo (Obrigatório)</label>
                        <textarea name="comment" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Explique por que está postergando o contato..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Comentário -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="<?= BASE_URL ?>/?page=leads&action=comment" method="POST">
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <div class="modal-header border-info bg-info bg-opacity-25">
                    <h5 class="modal-title text-info"><i class="bi bi-chat-text"></i> Adicionar Observação</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-info fw-bold">Observação</label>
                        <textarea name="comment" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Escreva aqui sobre o andamento do lead..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-info fw-bold">Mencionar Usuário (Opcional)</label>
                        <select name="mentioned_user_id" class="form-select bg-dark text-light border-secondary">
                            <option value="">Ninguém</option>
                            <?php foreach($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-info">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-dark fw-bold">Salvar Observação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-danger">
            <form action="<?= BASE_URL ?>/?page=leads&action=delete" method="POST">
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <div class="modal-header border-danger bg-danger bg-opacity-25">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle"></i> Excluir Lead</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este lead? <strong>Esta ação não pode ser desfeita.</strong> Todo o histórico da timeline também será apagado.</p>
                </div>
                <div class="modal-footer border-danger">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>
