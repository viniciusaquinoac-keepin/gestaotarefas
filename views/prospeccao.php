<?php
$empresaCor = $empresa === 'Autoitec' ? 'primary' : 'success';
$empresaNome = $empresa === 'Autoitec' ? 'Autoitec Engenharia Industrial (B2B)' : 'Keepin Automação & IoT (Field Sales)';
$totalLeads = count($allLeads);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-<?= $empresaCor ?> fs-6"><i class="bi bi-crosshair"></i> Funil Comercial Metrificado</span>
            <span class="badge bg-secondary"><?= $totalLeads ?> oportunidades</span>
        </div>
        <h1 class="h3 text-light mb-0"><?= $empresaNome ?></h1>
    </div>

    <!-- Seletor de Empresa e Botões de Ação -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group shadow-sm" role="group">
            <a href="<?= BASE_URL ?>/?page=prospeccao&empresa=Autoitec" class="btn btn-sm <?= $empresa === 'Autoitec' ? 'btn-primary active fw-bold' : 'btn-outline-primary' ?>">
                <i class="bi bi-gear-wide-connected me-1"></i> Autoitec (B2B Industrial)
            </a>
            <a href="<?= BASE_URL ?>/?page=prospeccao&empresa=Keepin" class="btn btn-sm <?= $empresa === 'Keepin' ? 'btn-success active fw-bold' : 'btn-outline-success' ?>">
                <i class="bi bi-shop me-1"></i> Keepin (IoT Varejo)
            </a>
        </div>

        <?php if ($empresa === 'Keepin'): ?>
            <button class="btn btn-sm btn-outline-warning fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVisitaKeepin">
                <i class="bi bi-geo-alt-fill me-1"></i> Registrar Visita de Rua
            </button>
        <?php endif; ?>

        <button class="btn btn-sm btn-<?= $empresaCor ?> fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoLead">
            <i class="bi bi-plus-circle me-1"></i> Nova Oportunidade
        </button>
    </div>
</div>

<!-- Filtros Rápidos -->
<div class="card bg-dark border-secondary mb-4 shadow-sm">
    <div class="card-body py-2 px-3">
        <form method="GET" action="<?= BASE_URL ?>/" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="prospeccao">
            <input type="hidden" name="empresa" value="<?= htmlspecialchars($empresa) ?>">
            
            <div class="col-auto">
                <span class="text-secondary small fw-bold"><i class="bi bi-funnel"></i> Filtrar Vendedor:</span>
            </div>
            <div class="col-md-3">
                <select name="vendedor_id" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                    <option value="">Todos os Vendedores</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($vendedorFiltro == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['department']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($vendedorFiltro): ?>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/?page=prospeccao&empresa=<?= urlencode($empresa) ?>" class="btn btn-sm btn-outline-secondary">Limpar Filtro</a>
                </div>
            <?php endif; ?>
            <div class="col text-end small text-secondary">
                Metodologia Ativa: 
                <strong><?= $empresa === 'Autoitec' ? 'SPIN Selling + MEDDPICC' : 'Field Sales Door-to-Door (KPRemote & Placas)' ?></strong>
            </div>
        </form>
    </div>
</div>

<!-- KANBAN BOARD -->
<div class="row g-3 flex-nowrap overflow-x-auto pb-4" style="min-height: 650px;">
    <?php
    $colunasConfig = [
        'Prospeccao' => ['titulo' => 'Prospecção (Ligações)', 'cor' => 'info', 'icon' => 'bi-telephone'],
        'Abordagem' => ['titulo' => 'Abordagem / Campo', 'cor' => 'primary', 'icon' => 'bi-geo-alt'],
        'Diagnostico' => ['titulo' => 'Diagnóstico (SPIN Opcional)', 'cor' => 'secondary', 'icon' => 'bi-search'],
        'Apresentacao' => ['titulo' => 'Apresentação / Reunião', 'cor' => 'warning', 'icon' => 'bi-easel2'],
        'Proposta' => ['titulo' => 'Proposta Enviada', 'cor' => 'purple', 'icon' => 'bi-file-earmark-text', 'customBg' => 'background-color:#6f42c1 !important; color:#fff !important;'],
        'Fechado' => ['titulo' => 'Fechado / Ganho', 'cor' => 'success', 'icon' => 'bi-trophy-fill'],
        'Perdido' => ['titulo' => 'Perdido / Insucesso', 'cor' => 'danger', 'icon' => 'bi-x-circle']
    ];

    foreach ($colunasConfig as $etapaChave => $cfg):
        $leadsColuna = $etapas[$etapaChave] ?? [];
        $qtdColuna = count($leadsColuna);
        $totalValorColuna = array_sum(array_column($leadsColuna, 'valor_estimado'));
    ?>
    <div class="col" style="min-width: 310px; max-width: 340px;">
        <div class="card bg-dark border-secondary h-100 shadow-sm">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-<?= $cfg['cor'] ?> bg-opacity-25 text-<?= $cfg['cor'] ?> border border-<?= $cfg['cor'] ?>" style="<?= $cfg['customBg'] ?? '' ?>">
                        <i class="bi <?= $cfg['icon'] ?>"></i>
                    </span>
                    <strong class="text-light small"><?= $cfg['titulo'] ?></strong>
                </div>
                <span class="badge bg-secondary"><?= $qtdColuna ?></span>
            </div>
            
            <?php if ($totalValorColuna > 0): ?>
                <div class="px-3 py-1 bg-secondary bg-opacity-10 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">Volume:</span>
                    <span class="text-light fw-bold small">R$ <?= number_format($totalValorColuna, 2, ',', '.') ?></span>
                </div>
            <?php endif; ?>

            <div class="card-body p-2 kanban-column" style="overflow-y: auto; max-height: 720px;" data-etapa="<?= $etapaChave ?>">
                <?php if (empty($leadsColuna)): ?>
                    <div class="text-center text-secondary py-4 small fst-italic">
                        Nenhum lead nesta etapa
                    </div>
                <?php else: ?>
                    <?php foreach ($leadsColuna as $lead): 
                        // Regra de bordas visuais de alerta (Amarelo / Vermelho / Verde)
                        $cardBorder = 'border: 1px solid rgba(255,255,255,0.1);';
                        $prazoBadge = '';
                        
                        if ($etapaChave === 'Fechado') {
                            $cardBorder = 'border: 2px solid #198754 !important; box-shadow: 0 0 8px rgba(25, 135, 84, 0.4);';
                        } elseif ($etapaChave === 'Perdido') {
                            $cardBorder = 'border: 1px solid rgba(220, 53, 69, 0.4);';
                        } elseif (!empty($lead['data_recontato_futuro'])) {
                            $hoje = strtotime(date('Y-m-d'));
                            $recTime = strtotime($lead['data_recontato_futuro']);
                            if ($recTime < $hoje) {
                                $cardBorder = 'border: 2px solid #ff4444 !important; box-shadow: 0 0 10px rgba(255, 68, 68, 0.6) !important;';
                                $prazoBadge = '<span class="badge bg-danger">Recontato Atrasado</span>';
                            } elseif ($recTime <= strtotime('+3 days', $hoje)) {
                                $cardBorder = 'border: 2px solid #ffc107 !important; box-shadow: 0 0 10px rgba(255, 193, 7, 0.5) !important;';
                                $prazoBadge = '<span class="badge bg-warning text-dark">Recontato Próximo</span>';
                            }
                        }
                    ?>
                    <div class="card bg-dark bg-opacity-75 border-secondary mb-2 lead-card shadow-sm position-relative" style="<?= $cardBorder ?>">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary bg-opacity-50 text-light small">
                                    <i class="bi bi-clock"></i> <?= date('d/m', strtotime($lead['data_atualizacao'])) ?>
                                </span>
                                <?php if ($lead['meddpicc_score'] > 0): ?>
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info" title="Score MEDDPICC">
                                        MEDDPICC: <?= $lead['meddpicc_score'] ?>%
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h6 class="text-light fw-bold mb-1"><?= htmlspecialchars($lead['nome_cliente_fantasia']) ?></h6>

                            <?php if (!empty($lead['contato_nome']) || !empty($lead['contato_telefone'])): ?>
                                <div class="text-secondary small mb-2">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($lead['contato_nome'] ?: 'Sem contato') ?>
                                    <?php if (!empty($lead['contato_telefone'])): ?>
                                        <span class="ms-1">(<?= htmlspecialchars($lead['contato_telefone']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($lead['valor_estimado'] > 0): ?>
                                <div class="text-success fw-bold small mb-2">
                                    R$ <?= number_format($lead['valor_estimado'], 2, ',', '.') ?>
                                </div>
                            <?php endif; ?>

                            <!-- Detalhes SPIN Rápido -->
                            <?php if (!empty($lead['spin_problema'])): ?>
                                <div class="bg-secondary bg-opacity-10 rounded p-2 small text-light border border-secondary mb-2" style="font-size: 0.78rem;">
                                    <strong>Dor/Problema:</strong> <?= htmlspecialchars(mb_strimwidth($lead['spin_problema'], 0, 75, '...')) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Informações de Perda / Recontato -->
                            <?php if ($etapaChave === 'Perdido'): ?>
                                <div class="bg-danger bg-opacity-10 border border-danger rounded p-2 small text-light mb-2" style="font-size: 0.75rem;">
                                    <div class="text-danger fw-bold"><i class="bi bi-x-octagon"></i> Motivo: <?= htmlspecialchars($lead['motivo_perda'] ?: 'Não especificado') ?></div>
                                    <?php if (!empty($lead['data_recontato_futuro'])): ?>
                                        <div class="mt-1"><i class="bi bi-calendar-event"></i> Recontato: <strong><?= date('d/m/Y', strtotime($lead['data_recontato_futuro'])) ?></strong></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Próximo Agendamento na Semana (se houver) -->
                            <?php if (!empty($lead['prox_agendamento'])): ?>
                                <div class="bg-info bg-opacity-10 border border-info rounded p-1 px-2 small text-light mb-2 d-flex justify-content-between align-items-center" style="font-size: 0.74rem;">
                                    <span class="text-truncate me-1"><i class="bi bi-calendar-event text-info me-1"></i><strong><?= htmlspecialchars($lead['prox_agendamento_tipo'] ?? 'Agendado') ?>:</strong> <?= htmlspecialchars($lead['prox_agendamento']) ?></span>
                                    <a href="<?= BASE_URL ?>/?page=agenda" class="badge bg-info text-dark text-decoration-none" title="Ver na Agenda da Semana">Ver</a>
                                </div>
                            <?php endif; ?>

                            <?= $prazoBadge ?>

                            <hr class="border-secondary my-2">

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="avatar shadow-sm" title="Vendedor: <?= htmlspecialchars($lead['vendedor_nome'] ?? 'Ninguém') ?>" style="width:24px; height:24px; border-radius:50%; background-color:<?= htmlspecialchars($lead['vendedor_avatar'] ?? '#6c757d') ?>; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:bold; color:white;">
                                        <?= strtoupper(substr($lead['vendedor_nome'] ?? 'V', 0, 1)) ?>
                                    </div>
                                    <small class="text-secondary" style="font-size: 0.75rem;"><?= htmlspecialchars($lead['vendedor_nome'] ?? '') ?></small>
                                </div>

                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-info p-1 px-2" title="Visualizar / Editar SPIN & MEDDPICC" onclick="abrirModalLead(<?= htmlspecialchars(json_encode($lead)) ?>)">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary p-1 px-2" data-bs-toggle="dropdown" title="Mover Etapa & Agendar na Semana">
                                            <i class="bi bi-arrow-right-circle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                            <li><h6 class="dropdown-header">Mover & Agendar Atividade:</h6></li>
                                            <?php foreach ($colunasConfig as $targetEtapa => $tCfg): ?>
                                                <?php if ($targetEtapa !== $etapaChave): ?>
                                                    <li>
                                                        <button class="dropdown-item small" onclick='abrirModalMoverLead(<?= htmlspecialchars(json_encode($lead)) ?>, "<?= $targetEtapa ?>")'>
                                                            <i class="bi <?= $tCfg['icon'] ?> text-<?= $tCfg['cor'] ?> me-1"></i> <?= $tCfg['titulo'] ?>
                                                        </button>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- SEÇÃO EXTRA SE KEEPIN: Histórico de Visitas de Rua (Field Sales) -->
<?php if ($empresa === 'Keepin'): ?>
<div class="card bg-dark border-secondary mt-4 shadow-sm">
    <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark"><i class="bi bi-geo-alt-fill"></i> Field Sales Keepin</span>
            <h5 class="card-title text-light mb-0">Registro de Visitas Presenciais em Lojas / Mercados</h5>
        </div>
        <button class="btn btn-sm btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalVisitaKeepin">
            <i class="bi bi-plus-lg"></i> Registrar Nova Visita
        </button>
    </div>
    <div class="card-body p-0">
        <?php if (empty($visitasRecentes)): ?>
            <div class="text-center text-secondary py-4 small">
                Nenhuma visita de rua registrada no período.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped mb-0 align-middle">
                    <thead>
                        <tr class="border-secondary text-secondary small">
                            <th>DATA / HORA</th>
                            <th>ESTABELECIMENTO</th>
                            <th>SEGMENTO</th>
                            <th>CONTATO ABORDADO</th>
                            <th>INTERESSE</th>
                            <th>OBSERVAÇÃO DE CAMPO</th>
                            <th>VENDEDOR</th>
                            <th class="text-end">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitasRecentes as $v): ?>
                        <tr>
                            <td class="small text-secondary"><?= date('d/m/Y H:i', strtotime($v['data_visita'])) ?></td>
                            <td class="fw-bold text-light"><?= htmlspecialchars($v['estabelecimento_nome']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($v['segmento']) ?></span></td>
                            <td class="small text-light">
                                <?= htmlspecialchars($v['contato_abordado']) ?>
                                <?php if (!empty($v['telefone'])): ?>
                                    <br><small class="text-secondary"><?= htmlspecialchars($v['telefone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($v['interesse_placa']): ?>
                                    <span class="badge bg-primary me-1">Placa R$ 4k</span>
                                <?php endif; ?>
                                <?php if ($v['interesse_kpremote']): ?>
                                    <span class="badge bg-success">KPRemote R$ 40/mês</span>
                                <?php endif; ?>
                                <?php if (!$v['interesse_placa'] && !$v['interesse_kpremote']): ?>
                                    <span class="text-secondary small">Prospecção Fria</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-secondary" style="max-width: 250px;">
                                <?= htmlspecialchars($v['observacao']) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <div class="avatar" style="width:20px; height:20px; border-radius:50%; background-color:<?= htmlspecialchars($v['vendedor_avatar'] ?? '#6c757d') ?>; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold; color:white;">
                                        <?= strtoupper(substr($v['vendedor_nome'] ?? 'V', 0, 1)) ?>
                                    </div>
                                    <span class="small text-light"><?= htmlspecialchars($v['vendedor_nome'] ?? '') ?></span>
                                </div>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?= BASE_URL ?>/?page=prospeccao&action=delete_visita" class="d-inline" onsubmit="return confirm('Deseja excluir esta visita?');">
                                    <input type="hidden" name="visita_id" value="<?= $v['id'] ?>">
                                    <button class="btn btn-sm btn-link text-danger p-0" title="Excluir"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- MODAL NOVO LEAD -->
<div class="modal fade" id="modalNovoLead" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-<?= $empresaCor ?>">
            <form action="<?= BASE_URL ?>/?page=prospeccao&action=create_lead" method="POST">
                <div class="modal-header border-<?= $empresaCor ?> bg-<?= $empresaCor ?> bg-opacity-25">
                    <h5 class="modal-title text-light"><i class="bi bi-plus-circle me-1"></i> Nova Oportunidade Comercial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Empresa Alvo</label>
                            <select name="empresa_alvo" class="form-select bg-dark text-light border-secondary" required id="novo_empresa_alvo">
                                <option value="Autoitec" <?= $empresa === 'Autoitec' ? 'selected' : '' ?>>Autoitec (Industrial B2B)</option>
                                <option value="Keepin" <?= $empresa === 'Keepin' ? 'selected' : '' ?>>Keepin (IoT Varejo)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Nome do Cliente / Razão Social / Fantasia *</label>
                            <input type="text" name="nome_cliente_fantasia" class="form-control bg-dark text-light border-secondary" required placeholder="Ex: Cerâmica Progresso / Supermercado Alvorada">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Contato Principal</label>
                            <input type="text" name="contato_nome" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Eng. Marcos / Sr. João">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Telefone / WhatsApp</label>
                            <input type="text" name="contato_telefone" class="form-control bg-dark text-light border-secondary" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">E-mail</label>
                            <input type="email" name="contato_email" class="form-control bg-dark text-light border-secondary" placeholder="contato@empresa.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Etapa Inicial</label>
                            <select name="etapa_funil" class="form-select bg-dark text-light border-secondary">
                                <option value="Prospeccao" selected>Prospeccao (Ligações / Contatos)</option>
                                <option value="Abordagem">Abordagem / Campo</option>
                                <option value="Diagnostico">Diagnóstico (SPIN Opcional)</option>
                                <option value="Apresentacao">Apresentação / Reunião</option>
                                <option value="Proposta">Proposta Enviada</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Valor Estimado (R$)</label>
                            <input type="text" name="valor_estimado" class="form-control bg-dark text-light border-secondary" placeholder="0,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Vendedor Responsável</label>
                            <select name="vendedor_id" class="form-select bg-dark text-light border-secondary">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($u['id'] == $_SESSION['user_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Diagnóstico SPIN Selling (Opcional) -->
                    <div class="card bg-secondary bg-opacity-10 border-secondary mb-3">
                        <div class="card-header bg-transparent border-secondary py-2 d-flex justify-content-between align-items-center">
                            <strong class="text-info small"><i class="bi bi-lightbulb"></i> Diagnóstico SPIN Selling (100% Opcional)</strong>
                            <span class="badge bg-secondary">Preenchimento Flexível</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-info fw-bold">[S] Situação Atual</label>
                                    <textarea name="spin_situacao" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Qual o maquinário, CLPs, câmaras ou estrutura atual?"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-warning fw-bold">[P] Problema / Dor Central</label>
                                    <textarea name="spin_problema" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Quais falhas, paradas ou perdas o cliente relata?"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-danger fw-bold">[I] Implicação Financeira / Risco</label>
                                    <textarea name="spin_implicacao" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Quanto dinheiro ele perde por hora parada ou insumo descartado?"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-success fw-bold">[N] Necessidade de Solução</label>
                                    <textarea name="spin_necessidade" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Como o sistema Autoitec/Keepin resolve sem dor de cabeça?"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Qualificação MEDDPICC (Autoitec B2B) -->
                    <div class="card bg-secondary bg-opacity-10 border-secondary">
                        <div class="card-header bg-transparent border-secondary py-2 d-flex justify-content-between align-items-center">
                            <strong class="text-primary small"><i class="bi bi-check2-circle"></i> Checklist de Qualificação MEDDPICC</strong>
                            <span class="badge bg-secondary">Checklist B2B</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 small">
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="M" id="chk_m">
                                    <label class="form-check-label text-light" for="chk_m"><strong>[M] Metrics:</strong> ROI ou economia mensurada</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="E" id="chk_e">
                                    <label class="form-check-label text-light" for="chk_e"><strong>[E] Economic Buyer:</strong> Dono/Diretor financeiro identificado</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="Dc" id="chk_dc">
                                    <label class="form-check-label text-light" for="chk_dc"><strong>[D] Decision Criteria:</strong> Critérios técnicos claros</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="Dp" id="chk_dp">
                                    <label class="form-check-label text-light" for="chk_dp"><strong>[D] Decision Process:</strong> Etapas de aprovação mapeadas</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="P" id="chk_p">
                                    <label class="form-check-label text-light" for="chk_p"><strong>[P] Paper Process:</strong> Jurídico, compras e minuta do contrato</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="I" id="chk_i">
                                    <label class="form-check-label text-light" for="chk_i"><strong>[I] Identify Pain:</strong> Dor aguda quantificada em R$</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="C" id="chk_c">
                                    <label class="form-check-label text-light" for="chk_c"><strong>[C] Champion:</strong> Padrinho interno que defende nosso projeto</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="meddpicc[]" value="Co" id="chk_co">
                                    <label class="form-check-label text-light" for="chk_co"><strong>[C] Competition:</strong> Concorrentes ou inércia mapeados</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-<?= $empresaCor ?> fw-bold">Cadastrar Oportunidade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR VISITA DE CAMPO KEEPIN -->
<div class="modal fade" id="modalVisitaKeepin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-warning">
            <form action="<?= BASE_URL ?>/?page=prospeccao&action=registrar_visita" method="POST">
                <div class="modal-header border-warning bg-warning bg-opacity-25">
                    <h5 class="modal-title text-warning"><i class="bi bi-geo-alt-fill me-1"></i> Check-in de Visita de Campo Keepin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome do Estabelecimento *</label>
                        <input type="text" name="estabelecimento_nome" class="form-control bg-dark text-light border-secondary" required placeholder="Ex: Supermercado Estrela Dalva">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Segmento</label>
                            <select name="segmento" class="form-select bg-dark text-light border-secondary">
                                <option value="Supermercado">Supermercado</option>
                                <option value="Mercearia">Mercearia</option>
                                <option value="Acougue">Açougue</option>
                                <option value="Padaria">Padaria</option>
                                <option value="Distribuidora">Distribuidora</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Contato Abordado</label>
                            <input type="text" name="contato_abordado" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Gerente Carlos / Dono">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control bg-dark text-light border-secondary" placeholder="(00) 00000-0000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-info">Interesse Demonstrado na Abordagem:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interesse_placa" value="1" id="vis_placa">
                            <label class="form-check-label text-light" for="vis_placa">Interesse em Placas Keepin (R$ 4.000,00)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="interesse_kpremote" value="1" id="vis_remote">
                            <label class="form-check-label text-light" for="vis_remote">Interesse em Locação KPRemote (R$ 40,00/mês)</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Observações da Abordagem</label>
                        <textarea name="observacao" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Como foi a reação do cliente ao Gatilho da Madrugada? Quantas câmaras possui?"></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="criar_lead_prospeccao" value="1" id="vis_lead" checked>
                        <label class="form-check-label text-warning small fw-bold" for="vis_lead">
                            Criar card de Oportunidade no Funil de Prospecção
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Salvar Check-in de Visita</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL REGRA DE PERDA / INSUCESSO -->
<div class="modal fade" id="modalPerdaLead" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-danger">
            <form action="<?= BASE_URL ?>/?page=prospeccao&action=update_etapa" method="POST">
                <input type="hidden" name="lead_id" id="perda_lead_id">
                <input type="hidden" name="etapa" value="Perdido">
                <input type="hidden" name="empresa_alvo" value="<?= htmlspecialchars($empresa) ?>">

                <div class="modal-header border-danger bg-danger bg-opacity-25">
                    <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-1"></i> Registrar Perda / Insucesso da Oportunidade</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger small">
                        <strong>Regra Comercial de Alta Performance:</strong> Toda perda exige um motivo claro e um agendamento de recontato futuro, que criará uma tarefa automática no seu Kanban!
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-danger">Motivo da Perda (Obrigatório) *</label>
                        <select name="motivo_perda" class="form-select bg-dark text-light border-secondary" required>
                            <option value="">Selecione o motivo...</option>
                            <option value="Preco/CAPEX">Preço / CAPEX Elevado (Sem Verba)</option>
                            <option value="Concorrente">Optou por Concorrente</option>
                            <option value="Decisor Nao Acessado">Decisor Não Acessado / Bloqueado</option>
                            <option value="Sem Orcamento">Sem Orçamento / Projeto Congelado</option>
                            <option value="Outro">Outro Motivo</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-warning">Data para Recontato Futuro (Obrigatório) *</label>
                        <input type="date" name="data_recontato_futuro" class="form-control bg-dark text-light border-secondary" required min="<?= date('Y-m-d') ?>">
                        <div class="form-text text-secondary small">
                            Será gerada automaticamente uma tarefa no Kanban para não perder o relacionamento com a conta.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Observações e Lições Aprendidas</label>
                        <textarea name="motivo_perda_obs" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="O que faltou para o fechamento?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold">Confirmar Perda & Agendar Tarefa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL VISUALIZAR / EDITAR LEAD -->
<div class="modal fade" id="modalEditarLead" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-info">
            <form action="<?= BASE_URL ?>/?page=prospeccao&action=update_lead" method="POST">
                <input type="hidden" name="lead_id" id="edit_lead_id">
                <div class="modal-header border-info bg-info bg-opacity-25">
                    <h5 class="modal-title text-info"><i class="bi bi-pencil-square me-1"></i> Oportunidade: <span id="edit_titulo_fantasia"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Empresa Alvo</label>
                            <select name="empresa_alvo" id="edit_empresa_alvo" class="form-select bg-dark text-light border-secondary" required>
                                <option value="Autoitec">Autoitec (Industrial B2B)</option>
                                <option value="Keepin">Keepin (IoT Varejo)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Cliente / Razão Social *</label>
                            <input type="text" name="nome_cliente_fantasia" id="edit_nome_cliente_fantasia" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Contato</label>
                            <input type="text" name="contato_nome" id="edit_contato_nome" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Telefone / WhatsApp</label>
                            <input type="text" name="contato_telefone" id="edit_contato_telefone" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">E-mail</label>
                            <input type="email" name="contato_email" id="edit_contato_email" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Etapa do Funil</label>
                            <select name="etapa_funil" id="edit_etapa_funil" class="form-select bg-dark text-light border-secondary">
                                <option value="Prospeccao">Prospecção (Ligações / Contatos)</option>
                                <option value="Abordagem">Abordagem / Campo</option>
                                <option value="Diagnostico">Diagnóstico (SPIN)</option>
                                <option value="Apresentacao">Apresentação / Reunião</option>
                                <option value="Proposta">Proposta Enviada</option>
                                <option value="Fechado">Fechado / Ganho</option>
                                <option value="Perdido">Perdido / Insucesso</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Valor Estimado (R$)</label>
                            <input type="text" name="valor_estimado" id="edit_valor_estimado" class="form-control bg-dark text-light border-secondary">
                        </div>
                    </div>

                    <!-- SPIN Selling (Opcional) -->
                    <div class="card bg-secondary bg-opacity-10 border-secondary mb-3">
                        <div class="card-header bg-transparent border-secondary py-2 d-flex justify-content-between align-items-center">
                            <strong class="text-info small"><i class="bi bi-lightbulb"></i> Diagnóstico SPIN Selling (100% Opcional)</strong>
                            <span class="badge bg-secondary">Preenchimento Flexível</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-info fw-bold">[S] Situação</label>
                                    <textarea name="spin_situacao" id="edit_spin_situacao" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-warning fw-bold">[P] Problema / Dor</label>
                                    <textarea name="spin_problema" id="edit_spin_problema" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-danger fw-bold">[I] Implicação / Risco R$</label>
                                    <textarea name="spin_implicacao" id="edit_spin_implicacao" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-success fw-bold">[N] Necessidade Solucionada</label>
                                    <textarea name="spin_necessidade" id="edit_spin_necessidade" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MEDDPICC -->
                    <div class="card bg-secondary bg-opacity-10 border-secondary">
                        <div class="card-header bg-transparent border-secondary py-2 d-flex justify-content-between align-items-center">
                            <strong class="text-primary small"><i class="bi bi-check2-circle"></i> Qualificação MEDDPICC</strong>
                            <span class="badge bg-info text-dark fw-bold" id="edit_meddpicc_badge">Score: 0%</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 small">
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="M" id="edit_chk_m">
                                    <label class="form-check-label text-light" for="edit_chk_m"><strong>[M] Metrics:</strong> ROI / Economia mensurada</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="E" id="edit_chk_e">
                                    <label class="form-check-label text-light" for="edit_chk_e"><strong>[E] Economic Buyer:</strong> Decisor financeiro</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="Dc" id="edit_chk_dc">
                                    <label class="form-check-label text-light" for="edit_chk_dc"><strong>[D] Decision Criteria:</strong> Critérios técnicos</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="Dp" id="edit_chk_dp">
                                    <label class="form-check-label text-light" for="edit_chk_dp"><strong>[D] Decision Process:</strong> Rito de aprovação</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="P" id="edit_chk_p">
                                    <label class="form-check-label text-light" for="edit_chk_p"><strong>[P] Paper Process:</strong> Jurídico e contratos</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="I" id="edit_chk_i">
                                    <label class="form-check-label text-light" for="edit_chk_i"><strong>[I] Identify Pain:</strong> Dor quantificada em R$</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="C" id="edit_chk_c">
                                    <label class="form-check-label text-light" for="edit_chk_c"><strong>[C] Champion:</strong> Padrinho interno</label>
                                </div>
                                <div class="col-md-6 form-check">
                                    <input class="form-check-input edit-medd" type="checkbox" name="meddpicc[]" value="Co" id="edit_chk_co">
                                    <label class="form-check-label text-light" for="edit_chk_co"><strong>[C] Competition:</strong> Concorrentes mapeados</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="excluirLeadAtual()">
                            <i class="bi bi-trash"></i> Excluir Oportunidade
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-info text-dark fw-bold">Salvar Alterações</button>
                    </div>
                </div>
            </form>
            <!-- Form Exclusão Auxiliar -->
            <form id="formExcluirLead" action="<?= BASE_URL ?>/?page=prospeccao&action=delete_lead" method="POST" style="display:none;">
                <input type="hidden" name="lead_id" id="delete_lead_id">
                <input type="hidden" name="empresa" value="<?= htmlspecialchars($empresa) ?>">
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL MOVER LEAD & AGENDAR NA SEMANA -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalMoverAgendarLead" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-warning">
            <form action="<?= BASE_URL ?>/?page=prospeccao&action=update_etapa" method="POST">
                <input type="hidden" name="lead_id" id="agendar_lead_id">
                <input type="hidden" name="empresa_alvo" id="agendar_empresa_alvo">

                <div class="modal-header border-warning bg-warning bg-opacity-25">
                    <h5 class="modal-title text-warning"><i class="bi bi-calendar-plus me-1"></i> Mover Card & Agendar na Semana</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-light mb-3">Cliente: <strong id="agendar_cliente_nome" class="text-info fs-6"></strong></p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mover para a Etapa:</label>
                        <select name="etapa" id="agendar_nova_etapa" class="form-select bg-dark text-light border-secondary" required onchange="aoMudarEtapaAgendamento(this.value)">
                            <option value="Prospeccao">Prospecção (Ligações / Contatos)</option>
                            <option value="Abordagem">Abordagem / Campo</option>
                            <option value="Diagnostico">Diagnóstico (SPIN Opcional)</option>
                            <option value="Apresentacao">Apresentação / Reunião</option>
                            <option value="Proposta">Proposta Enviada</option>
                            <option value="Fechado">Fechado / Ganho</option>
                            <option value="Perdido">Perdido / Insucesso</option>
                        </select>
                    </div>

                    <div class="card bg-secondary bg-opacity-10 border-secondary p-3 mb-3" id="box_dados_agendamento">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-warning small"><i class="bi bi-calendar-week me-1"></i> Agendamento na Semana (Prudential)</strong>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="check_habilitar_agenda" checked onchange="toggleBoxAgenda(this.checked)">
                                <label class="form-check-label text-secondary small" for="check_habilitar_agenda">Agendar</label>
                            </div>
                        </div>

                        <div id="campos_agenda_container">
                            <div class="row g-2 mb-2">
                                <div class="col-md-7">
                                    <label class="form-label small">Data *</label>
                                    <input type="date" name="data_agendada" id="agendar_data" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">Horário *</label>
                                    <input type="time" name="horario_agendado" id="agendar_horario" class="form-control form-control-sm bg-dark text-light border-secondary" value="09:00">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">Tipo de Atividade na Agenda</label>
                                <select name="tipo_atividade" id="agendar_tipo_atividade" class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="Ligacao">Ligação</option>
                                    <option value="Abordagem">Abordagem Presencial</option>
                                    <option value="Diagnostico">Diagnóstico SPIN</option>
                                    <option value="Apresentacao">Apresentação de Solução</option>
                                    <option value="Proposta">Envio de Proposta</option>
                                    <option value="Fechamento">Fechamento</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label small">Anotação rápida (Pauta)</label>
                                <input type="text" name="obs_atividade" id="agendar_obs" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Ex: Ligar para confirmar se diretor estará presente">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Salvar e Atualizar Funil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalMoverLead(lead, novaEtapa) {
    if (novaEtapa === 'Perdido') {
        document.getElementById('perda_lead_id').value = lead.id;
        const modalPerda = new bootstrap.Modal(document.getElementById('modalPerdaLead'));
        modalPerda.show();
        return;
    }

    document.getElementById('agendar_lead_id').value = lead.id;
    document.getElementById('agendar_empresa_alvo').value = lead.empresa_alvo;
    document.getElementById('agendar_cliente_nome').textContent = lead.nome_cliente_fantasia;
    document.getElementById('agendar_nova_etapa').value = novaEtapa;
    
    aoMudarEtapaAgendamento(novaEtapa);

    const modal = new bootstrap.Modal(document.getElementById('modalMoverAgendarLead'));
    modal.show();
}

function aoMudarEtapaAgendamento(etapa) {
    if (etapa === 'Perdido') {
        const modalMover = bootstrap.Modal.getInstance(document.getElementById('modalMoverAgendarLead'));
        if (modalMover) modalMover.hide();
        document.getElementById('perda_lead_id').value = document.getElementById('agendar_lead_id').value;
        const modalPerda = new bootstrap.Modal(document.getElementById('modalPerdaLead'));
        modalPerda.show();
        return;
    }

    const selectTipo = document.getElementById('agendar_tipo_atividade');
    if (etapa === 'Prospeccao') selectTipo.value = 'Ligacao';
    else if (etapa === 'Abordagem') selectTipo.value = 'Abordagem';
    else if (etapa === 'Diagnostico') selectTipo.value = 'Diagnostico';
    else if (etapa === 'Apresentacao') selectTipo.value = 'Apresentacao';
    else if (etapa === 'Proposta') selectTipo.value = 'Proposta';
    else if (etapa === 'Fechado') selectTipo.value = 'Fechamento';
}

function toggleBoxAgenda(habilitado) {
    const container = document.getElementById('campos_agenda_container');
    const dataInput = document.getElementById('agendar_data');
    const horaInput = document.getElementById('agendar_horario');
    if (habilitado) {
        container.style.opacity = '1';
        dataInput.removeAttribute('disabled');
        horaInput.removeAttribute('disabled');
    } else {
        container.style.opacity = '0.4';
        dataInput.setAttribute('disabled', 'disabled');
        horaInput.setAttribute('disabled', 'disabled');
    }
}

function moverLead(leadId, novaEtapa, empresaAlvo) {
    if (novaEtapa === 'Perdido') {
        document.getElementById('perda_lead_id').value = leadId;
        const modalPerda = new bootstrap.Modal(document.getElementById('modalPerdaLead'));
        modalPerda.show();
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/?page=prospeccao&action=update_etapa';

    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'lead_id';
    inputId.value = leadId;
    form.appendChild(inputId);

    const inputEtapa = document.createElement('input');
    inputEtapa.type = 'hidden';
    inputEtapa.name = 'etapa';
    inputEtapa.value = novaEtapa;
    form.appendChild(inputEtapa);

    const inputEmpresa = document.createElement('input');
    inputEmpresa.type = 'hidden';
    inputEmpresa.name = 'empresa_alvo';
    inputEmpresa.value = empresaAlvo;
    form.appendChild(inputEmpresa);

    document.body.appendChild(form);
    form.submit();
}

function abrirModalLead(lead) {
    document.getElementById('edit_lead_id').value = lead.id;
    document.getElementById('delete_lead_id').value = lead.id;
    document.getElementById('edit_titulo_fantasia').textContent = lead.nome_cliente_fantasia;
    document.getElementById('edit_empresa_alvo').value = lead.empresa_alvo;
    document.getElementById('edit_nome_cliente_fantasia').value = lead.nome_cliente_fantasia;
    document.getElementById('edit_contato_nome').value = lead.contato_nome || '';
    document.getElementById('edit_contato_telefone').value = lead.contato_telefone || '';
    document.getElementById('edit_contato_email').value = lead.contato_email || '';
    document.getElementById('edit_etapa_funil').value = lead.etapa_funil;
    document.getElementById('edit_valor_estimado').value = lead.valor_estimado ? Number(lead.valor_estimado).toLocaleString('pt-BR', {minimumFractionDigits: 2}) : '0,00';
    document.getElementById('edit_spin_situacao').value = lead.spin_situacao || '';
    document.getElementById('edit_spin_problema').value = lead.spin_problema || '';
    document.getElementById('edit_spin_implicacao').value = lead.spin_implicacao || '';
    document.getElementById('edit_spin_necessidade').value = lead.spin_necessidade || '';

    // Marcar checkboxes do MEDDPICC
    let meddArray = [];
    try {
        if (lead.meddpicc_data) {
            meddArray = JSON.parse(lead.meddpicc_data);
        }
    } catch(e) {}

    document.querySelectorAll('.edit-medd').forEach(chk => {
        chk.checked = meddArray.includes(chk.value);
    });

    document.getElementById('edit_meddpicc_badge').textContent = 'Score: ' + (lead.meddpicc_score || 0) + '%';

    const modal = new bootstrap.Modal(document.getElementById('modalEditarLead'));
    modal.show();
}

function excluirLeadAtual() {
    if (confirm('Deseja realmente excluir esta oportunidade? Esta ação não pode ser desfeita.')) {
        document.getElementById('formExcluirLead').submit();
    }
}
</script>
