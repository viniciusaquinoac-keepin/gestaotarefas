<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-3">
            <a href="<?= BASE_URL ?>/?page=projects" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left"></i> Voltar</a>
            <h3 class="fw-bold text-white mb-0">Configurações de Obras</h3>
        </div>

        <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success bg-dark border-success text-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> Configurações salvas com sucesso!
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="card bg-dark border-secondary">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/?page=projects&action=settings">
                    
                    <div class="mb-4">
                        <label class="form-label text-white fw-bold">Valor/Hora Padrão (R$)</label>
                        <input type="number" step="0.01" name="default_hourly_rate" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($defaultHourlyRate) ?>">
                        <div class="form-text text-secondary">Valor base para novos cadastros de obra (calcula custo de horas apontadas).</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white fw-bold">Etapas Padrão de Cronograma (1 por linha)</label>
                        <textarea name="default_phases" class="form-control bg-dark text-white border-secondary" rows="8"><?= htmlspecialchars(implode("\n", $defaultPhases)) ?></textarea>
                        <div class="form-text text-secondary">Etapas que serão inseridas automaticamente ao criar uma nova obra.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white fw-bold">Categorias Padrão de Custos/Materiais (1 por linha)</label>
                        <textarea name="default_cost_categories" class="form-control bg-dark text-white border-secondary" rows="8"><?= htmlspecialchars(implode("\n", $defaultCategories)) ?></textarea>
                        <div class="form-text text-secondary">Categorias de custos disponíveis para lançamento de despesas.</div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Salvar Configurações</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
