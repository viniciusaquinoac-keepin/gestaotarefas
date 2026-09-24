<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="d-flex align-items-center mb-3">
            <a href="<?= BASE_URL ?>/?page=projects" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left"></i> Voltar</a>
            <h3 class="fw-bold text-white mb-0">Nova Obra</h3>
        </div>

        <div class="card bg-dark border-secondary">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=create">
                    
                    <div class="mb-3">
                        <label class="form-label text-white">Nome da Obra <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Reforma da Automação AF04" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Cliente / Empresa</label>
                            <input type="text" name="client" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Metalsider / ArcelorMittal">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Vincular a Lead Comercial (Ordem Alfabética)</label>
                            <select name="lead_id" class="form-select bg-dark text-white border-secondary">
                                <option value="">-- NENHUM LEAD SELECIONADO --</option>
                                <?php foreach ($leads as $l):
                                    $leadLabel = ($l['company'] ? $l['company'] . ' (' . $l['name'] . ')' : $l['name']);
                                ?>
                                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($leadLabel) ?> - R$ <?= number_format($l['estimated_value'], 2, ',', '.') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Descrição / Escopo Geral</label>
                        <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Resumo do escopo da obra..."></textarea>
                    </div>

                    <div class="card bg-dark border-primary mb-3">
                        <div class="card-header bg-dark border-primary text-primary fw-bold">
                            <i class="bi bi-calculator me-1"></i> Targets de Orçamento da Proposta (Teto Máximo)
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-white">Target Materiais (R$)</label>
                                    <input type="number" step="0.01" name="target_material_budget" id="target_material_budget" class="form-control bg-dark text-white border-secondary" placeholder="0,00">
                                    <div class="form-text text-secondary">Teto orçado para materiais</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-white">Target Mão de Obra (R$)</label>
                                    <input type="number" step="0.01" name="target_labor_budget" id="target_labor_budget" class="form-control bg-dark text-white border-secondary" placeholder="0,00">
                                    <div class="form-text text-secondary">Teto orçado para tempo/horas</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-white fw-bold">Target Total Acumulado (R$)</label>
                                    <input type="number" step="0.01" name="target_budget" id="target_budget" class="form-control bg-dark text-white border-secondary fw-bold text-primary" placeholder="0,00">
                                    <div class="form-text text-secondary">Soma Materiais + M.O.</div>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label text-white">Valor/Hora do Funcionário para esta Obra (R$)</label>
                                <input type="number" step="0.01" name="hourly_rate" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($defaultHourlyRate) ?>">
                                <div class="form-text text-secondary">Utilizado para calcular o custo acumulado de mão de obra (Horas Trabalhadas × Valor/Hora). Padrão: R$ <?= number_format($defaultHourlyRate, 2, ',', '.') ?>/h</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Data de Início</label>
                            <input type="date" name="start_date" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Previsão de Entrega Final</label>
                            <input type="date" name="estimated_end_date" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-white">Prazo Estipulado (Meses)</label>
                            <input type="number" name="total_months" class="form-control bg-dark text-white border-secondary" placeholder="Ex: 4">
                        </div>
                    </div>

                    <div class="alert alert-info bg-dark border-info text-info mb-4">
                        <i class="bi bi-info-circle me-1"></i> Ao criar a obra, as etapas padrão de cronograma (<?= implode(' → ', $defaultPhases) ?>) serão criadas automaticamente. Você poderá editá-las depois.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/?page=projects" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Criar Obra</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/projects.js"></script>
