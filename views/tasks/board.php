<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-light mb-0">Tarefas</h1>
    <div class="d-flex gap-2">
        <?php if (isAdmin()): ?>
        <select id="userFilter" class="form-select bg-dark text-light border-secondary" style="width: auto;">
            <option value="">Todos os usuários</option>
            <option value="unassigned">Sem Responsável</option>
            <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <span class="badge bg-secondary p-2 border border-secondary text-light d-flex align-items-center">
            <i class="bi bi-person-fill text-warning me-1"></i> Minhas Tarefas
        </span>
        <?php endif; ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTaskModal">
            <i class="bi bi-plus-lg"></i> Nova Tarefa
        </button>
    </div>
</div>

<div class="row kanban-board g-3">
    <!-- Coluna A Fazer -->
    <div class="col-md-3">
        <div class="card bg-dark border-secondary h-100">
            <div class="card-header bg-secondary bg-opacity-25 border-secondary fw-bold text-light d-flex justify-content-between">
                <span>A Fazer</span>
                <span class="badge bg-secondary rounded-pill"><?= count($tasksTodo) ?></span>
            </div>
            <div class="card-body p-2 kanban-column" id="todo" data-status="todo">
                <?php foreach($tasksTodo as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Em Andamento -->
    <div class="col-md-3">
        <div class="card bg-dark border-secondary h-100">
            <div class="card-header bg-primary bg-opacity-25 border-primary fw-bold text-primary d-flex justify-content-between">
                <span>Em Andamento</span>
                <span class="badge bg-primary rounded-pill"><?= count($tasksInProgress) ?></span>
            </div>
            <div class="card-body p-2 kanban-column" id="in_progress" data-status="in_progress">
                <?php foreach($tasksInProgress as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Em Revisão -->
    <div class="col-md-3">
        <div class="card bg-dark border-secondary h-100">
            <div class="card-header bg-warning bg-opacity-25 border-warning fw-bold text-warning d-flex justify-content-between">
                <span>Em Revisão</span>
                <span class="badge bg-warning rounded-pill text-dark"><?= count($tasksReview) ?></span>
            </div>
            <div class="card-body p-2 kanban-column" id="review" data-status="review">
                <?php foreach($tasksReview as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Concluídas -->
    <div class="col-md-3">
        <div class="card bg-dark border-secondary h-100">
            <div class="card-header bg-success bg-opacity-25 border-success fw-bold text-success d-flex justify-content-between">
                <span>Concluída</span>
                <span class="badge bg-success rounded-pill"><?= count($tasksDone) ?></span>
            </div>
            <div class="card-body p-2 kanban-column" id="done" data-status="done">
                <?php foreach($tasksDone as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Tarefa -->
<div class="modal fade" id="newTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_URL ?>/?page=tasks&action=create" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Nova Tarefa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control bg-dark text-light border-secondary" rows="3"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Departamento</label>
                            <select name="department" class="form-select bg-dark text-light border-secondary" required>
                                <option value="TI">TI</option>
                                <option value="TA">Automação (TA)</option>
                                <option value="COMERCIAL">Comercial</option>
                                <option value="PD">P&D</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Prioridade</label>
                            <select name="priority" class="form-select bg-dark text-light border-secondary">
                                <option value="urgent" class="text-danger">Urgente</option>
                                <option value="high" class="text-warning">Alta</option>
                                <option value="medium" selected>Média</option>
                                <option value="low" class="text-secondary">Baixa</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Responsável</label>
                            <?php if (isAdmin()): ?>
                            <select name="assigned_to" class="form-select bg-dark text-light border-secondary">
                                <option value="">Sem responsável</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="hidden" name="assigned_to" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">
                            <input type="text" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars(getCurrentUser()['name'] ?? 'Meu Usuário') ?>" readonly disabled>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <label class="form-label">Prazo</label>
                            <input type="date" name="due_date" class="form-control bg-dark text-light border-secondary" required>
                        </div>
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

<style>
.kanban-board {
    min-height: calc(100vh - 150px);
}
.kanban-column {
    min-height: 200px;
}
.kanban-card {
    cursor: grab;
    background-color: #2b3035;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 10px;
    border-left: 4px solid #6c757d;
}
.kanban-card:active {
    cursor: grabbing;
}
.kanban-card.priority-urgent { border-left-color: #dc3545; }
.kanban-card.priority-high { border-left-color: #fd7e14; }
.kanban-card.priority-medium { border-left-color: #0d6efd; }
.kanban-card.priority-low { border-left-color: #6c757d; }

.sortable-ghost {
    opacity: 0.4;
    background-color: #495057;
}
</style>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/assets/js/kanban.js"></script>
<script>
const userFilterElem = document.getElementById('userFilter');
if (userFilterElem) {
    userFilterElem.addEventListener('change', function() {
        const userId = this.value;
        const cards = document.querySelectorAll('.kanban-card');
        
        // Save to cookie
        document.cookie = "taskUserFilter=" + userId + "; path=/; max-age=2592000";
        
        cards.forEach(card => {
            if (userId === '') {
                card.style.display = '';
            } else if (userId === 'unassigned') {
                if (card.dataset.userId === '') {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            } else {
                const mentionedIds = card.dataset.mentionedUserId ? card.dataset.mentionedUserId.split(',') : [];
                if (card.dataset.userId === userId || mentionedIds.includes(userId)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            }
        });
        
        // Atualizar contadores das colunas
        const columns = document.querySelectorAll('.kanban-column');
        columns.forEach(col => {
            const count = Array.from(col.querySelectorAll('.kanban-card')).filter(c => c.style.display !== 'none').length;
            const badge = col.closest('.card').querySelector('.badge');
            if(badge) badge.textContent = count;
        });
    });

    // Load filter from cookie on page load
    window.addEventListener('DOMContentLoaded', () => {
        const match = document.cookie.match(new RegExp('(^| )taskUserFilter=([^;]+)'));
        if (match) {
            const filterVal = match[2];
            if (userFilterElem.querySelector(`option[value="${filterVal}"]`)) {
                userFilterElem.value = filterVal;
                userFilterElem.dispatchEvent(new Event('change'));
            }
        }
    });
}
</script>
