<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-light mb-0">Gestão de Usuários</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="bi bi-person-plus"></i> Novo Usuário
    </button>
</div>

<div class="card bg-dark border-secondary">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Departamento</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Último Login</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar shadow-sm me-2" style="width:30px; height:30px; border-radius:50%; background-color:<?= $u['avatar_color'] ?>; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; color:white;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($u['name']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($u['department']) ?></span></td>
                            <td>
                                <?php if($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-shield-lock"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Usuário</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($u['active']): ?>
                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Ativo</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary small">
                                <?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Nunca' ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Novo Usuário -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_URL ?>/?page=users&action=create" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Novo Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="name" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email (Login)</label>
                        <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha Inicial</label>
                        <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
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
                            <label class="form-label">Perfil</label>
                            <select name="role" class="form-select bg-dark text-light border-secondary">
                                <option value="user">Usuário Comum</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>
