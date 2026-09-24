<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-light mb-0">Comercial (Funil de Vendas)</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newLeadModal">
        <i class="bi bi-person-plus"></i> Novo Lead
    </button>
</div>

<!-- Pipeline Canvas (scroll horizontal) -->
<div class="pipeline-container pb-3" style="overflow-x: auto; white-space: nowrap; height: calc(100vh - 150px);">
    
    <?php 
    $stages = [
        ['id' => 'new', 'name' => 'Novo', 'color' => 'secondary', 'items' => $leadsNew],
        ['id' => 'contacted', 'name' => 'Em Contato', 'color' => 'info', 'items' => $leadsContacted],
        ['id' => 'meeting', 'name' => 'Reunião', 'color' => 'primary', 'items' => $leadsMeeting],
        ['id' => 'proposal', 'name' => 'Proposta Enviada', 'color' => 'warning', 'items' => $leadsProposal],
        ['id' => 'in_analysis', 'name' => 'Em Análise', 'color' => 'light', 'items' => $leadsInAnalysis],
        ['id' => 'closed_won', 'name' => 'Fechado (Ganho)', 'color' => 'success', 'items' => $leadsWon],
        ['id' => 'closed_lost', 'name' => 'Perdido', 'color' => 'danger', 'items' => $leadsLost],
        ['id' => 'limbo', 'name' => 'Limbo', 'color' => 'dark', 'items' => $leadsLimbo]
    ];
    ?>

    <?php foreach($stages as $stage): ?>
        <div class="d-inline-block align-top mx-2 pipeline-col" style="width: 280px;">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header bg-<?= $stage['color'] ?> bg-opacity-25 border-<?= $stage['color'] ?> fw-bold text-<?= $stage['color'] === 'dark' ? 'secondary' : $stage['color'] ?> d-flex justify-content-between">
                    <span><?= $stage['name'] ?></span>
                    <span class="badge bg-<?= $stage['color'] === 'dark' ? 'secondary' : $stage['color'] ?> rounded-pill text-<?= $stage['color'] === 'dark' ? 'light' : 'dark' ?>"><?= count($stage['items']) ?></span>
                </div>
                <div class="card-body p-2 kanban-column" data-status="<?= $stage['id'] ?>" style="min-height: 200px; white-space: normal;">
                    <?php foreach($stage['items'] as $lead): ?>
                        <?php
                        $isDelayedLead = false;
                        if ($lead['next_contact_date']) {
                            $isDelayedLead = strtotime($lead['next_contact_date']) < strtotime(date('Y-m-d'));
                        }
                        $delayedLeadStyle = $isDelayedLead ? 'border: 2px solid #ff4444 !important; box-shadow: 0 0 12px rgba(255, 0, 0, 0.8) !important;' : '';
                        ?>
                        <div class="kanban-card lead-card" data-id="<?= $lead['id'] ?>" style="<?= $delayedLeadStyle ?> cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/?page=lead_timeline&id=<?= $lead['id'] ?>'">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 text-light fw-normal"><?= htmlspecialchars($lead['name']) ?></h6>
                                <div>
                                    <span class="badge bg-dark border border-secondary"><?= htmlspecialchars($lead['source']) ?></span>
                                    <div class="dropdown d-inline-block ms-1" onclick="event.stopPropagation()">
                                        <button class="btn btn-sm btn-link text-secondary p-0 border-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                                            <li><a class="dropdown-item" href="#" onclick='editLead(<?= json_encode($lead, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Editar</a></li>
                                            <li>
                                                <form action="<?= BASE_URL ?>/?page=leads&action=delete" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este lead?');">
                                                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger">Excluir</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php if ($lead['company']): ?>
                                <small class="text-secondary d-block mb-2"><i class="bi bi-building"></i> <?= htmlspecialchars($lead['company']) ?></small>
                            <?php endif; ?>
                            
                            <?php if ($lead['estimated_value'] > 0): ?>
                                <div class="text-success fw-bold small mb-2">R$ <?= number_format($lead['estimated_value'], 2, ',', '.') ?></div>
                            <?php endif; ?>

                            <?php $postponeCount = isset($lead['postpone_count']) ? (int)$lead['postpone_count'] : 0; ?>
                            <?php if ($postponeCount > 0): ?>
                                <div style="font-size: 0.75rem; color: #adb5bd;" class="mb-2"><strong>Prorrogada:</strong> <?= $postponeCount ?>x</div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary">
                                <?php
                                $dateColor = 'text-secondary';
                                if ($lead['next_contact_date']) {
                                    $isDelayed = strtotime($lead['next_contact_date']) < strtotime(date('Y-m-d'));
                                    if ($isDelayed) $dateColor = 'text-danger fw-bold';
                                    elseif (strtotime($lead['next_contact_date']) === strtotime(date('Y-m-d'))) $dateColor = 'text-warning fw-bold';
                                }
                                ?>
                                <small class="<?= $dateColor ?>" title="Próximo contato">
                                    <i class="bi bi-calendar-event"></i> <?= $lead['next_contact_date'] ? date('d/m', strtotime($lead['next_contact_date'])) : 'Sem data' ?>
                                </small>
                                
                                <div class="d-flex gap-1">
                                    <?php if (!empty($lead['mentions'])): ?>
                                        <?php foreach ($lead['mentions'] as $mention): ?>
                                            <div class="avatar shadow-sm border border-2 border-primary" title="Mencionado: <?= htmlspecialchars($mention['name']) ?>" style="width:24px; height:24px; border-radius:50%; background-color:<?= htmlspecialchars($mention['avatar_color'] ?? '#0d6efd') ?>; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:bold; color:white;">
                                                <?= strtoupper(substr($mention['name'], 0, 1)) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <div class="avatar shadow-sm" title="<?= htmlspecialchars($lead['assigned_name'] ?? 'Sem Responsável') ?>" style="width:24px; height:24px; border-radius:50%; background-color:<?= $lead['avatar_color'] ?? '#6c757d' ?>; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:bold; color:white;">
                                        <?= strtoupper(substr($lead['assigned_name'] ?? '?', 0, 1)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Novo Lead -->
<div class="modal fade" id="newLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_URL ?>/?page=leads&action=create" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Novo Lead</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome do Contato</label>
                        <input type="text" name="name" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Empresa</label>
                            <input type="text" name="company" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="col">
                            <label class="form-label">Telefone/WhatsApp</label>
                            <input type="text" name="phone" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control bg-dark text-light border-secondary">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Origem</label>
                            <select name="source" class="form-select bg-dark text-light border-secondary">
                                <option value="site">Site</option>
                                <option value="indicacao">Indicação</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="evento">Evento</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Valor Estimado (R$)</label>
                            <input type="number" step="0.01" name="estimated_value" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Vendedor Responsável</label>
                            <select name="assigned_to" class="form-select bg-dark text-light border-secondary">
                                <option value="">Sem responsável</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Próximo Contato</label>
                            <input type="date" name="next_contact_date" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="notes" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Lead -->
<div class="modal fade" id="editLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_URL ?>/?page=leads&action=update" method="POST">
                <input type="hidden" name="lead_id" id="edit_lead_id">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Editar Lead</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome do Contato</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Empresa</label>
                            <input type="text" name="company" id="edit_company" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="col">
                            <label class="form-label">Telefone/WhatsApp</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control bg-dark text-light border-secondary">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Origem</label>
                            <select name="source" id="edit_source" class="form-select bg-dark text-light border-secondary">
                                <option value="site">Site</option>
                                <option value="indicacao">Indicação</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="evento">Evento</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Valor Estimado (R$)</label>
                            <input type="number" step="0.01" name="estimated_value" id="edit_estimated_value" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Vendedor Responsável</label>
                            <select name="assigned_to" id="edit_assigned_to" class="form-select bg-dark text-light border-secondary">
                                <option value="">Sem responsável</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Próximo Contato</label>
                            <input type="date" name="next_contact_date" id="edit_next_contact_date" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="notes" id="edit_notes" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
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


<style>
.lead-card {
    border-left: 3px solid #198754;
}
.lead-card:hover {
    box-shadow: 0 0 8px rgba(25, 135, 84, 0.4);
}
</style>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    
    function editLead(lead) {
        document.getElementById('edit_lead_id').value = lead.id;
        document.getElementById('edit_name').value = lead.name;
        document.getElementById('edit_company').value = lead.company || '';
        document.getElementById('edit_phone').value = lead.phone || '';
        document.getElementById('edit_email').value = lead.email || '';
        document.getElementById('edit_source').value = lead.source || 'site';
        document.getElementById('edit_estimated_value').value = lead.estimated_value > 0 ? lead.estimated_value : '';
        document.getElementById('edit_next_contact_date').value = lead.next_contact_date ? lead.next_contact_date.split(' ')[0] : '';
        document.getElementById('edit_assigned_to').value = lead.assigned_to || '';
        document.getElementById('edit_notes').value = lead.notes || '';
        
        var modal = new bootstrap.Modal(document.getElementById('editLeadModal'));
        modal.show();
    }
</script>
<script src="<?= BASE_URL ?>/assets/js/leads.js"></script>
