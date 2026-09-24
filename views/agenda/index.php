<?php
$weekNum = $boundaries['week_number'];
$mondayFmt = date('d/m', strtotime($boundaries['monday']));
$sundayFmt = date('d/m/Y', strtotime($boundaries['sunday']));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-info text-dark fs-6"><i class="bi bi-calendar-week"></i> Gestão Comercial Semanal</span>
            <span class="badge bg-warning text-dark fw-bold">SEMANA # <?= $weekNum ?></span>
            <span class="badge bg-secondary"><?= $mondayFmt ?> a <?= $sundayFmt ?></span>
        </div>
        <h1 class="h3 text-light mb-0">Agenda de Atividades Semanal</h1>
    </div>

    <!-- Navegação de Semanas e Ações -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group" role="group">
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= urlencode($boundaries['prev_week']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>&empresa=<?= urlencode($empresa ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="Semana Anterior">
                <i class="bi bi-chevron-left"></i> Anterior
            </a>
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= date('Y-m-d') ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>&empresa=<?= urlencode($empresa ?? '') ?>" class="btn btn-sm btn-outline-info <?= ($boundaries['current_date'] === date('Y-m-d')) ? 'active' : '' ?>">
                Hoje (Semana Atual)
            </a>
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= urlencode($boundaries['next_week']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>&empresa=<?= urlencode($empresa ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="Próxima Semana">
                Próxima <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <button class="btn btn-sm btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoAgendamento">
            <i class="bi bi-plus-circle me-1"></i> Agendar Atividade
        </button>

        <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="Imprimir Grade">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<!-- Filtros Rápidos de Vendedor e Empresa -->
<div class="card bg-dark border-secondary mb-4 shadow-sm">
    <div class="card-body py-2 px-3">
        <form method="GET" action="<?= BASE_URL ?>/" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="agenda">
            <input type="hidden" name="data_ref" value="<?= htmlspecialchars($boundaries['monday']) ?>">

            <div class="col-auto">
                <span class="text-secondary small fw-bold"><i class="bi bi-funnel"></i> Filtrar:</span>
            </div>

            <div class="col-md-3">
                <select name="vendedor_id" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                    <option value="">Todos os Vendedores</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($vendedorId == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['department']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <select name="empresa" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                    <option value="">Todas as Empresas</option>
                    <option value="Autoitec" <?= ($empresa === 'Autoitec') ? 'selected' : '' ?>>Autoitec (Industrial B2B)</option>
                    <option value="Keepin" <?= ($empresa === 'Keepin') ? 'selected' : '' ?>>Keepin (IoT Varejo)</option>
                </select>
            </div>

            <div class="col-auto">
                <input type="date" name="data_ref" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= htmlspecialchars($boundaries['monday']) ?>" onchange="this.form.submit()" title="Ir para data específica">
            </div>

            <?php if ($vendedorId || $empresa): ?>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= urlencode($boundaries['monday']) ?>" class="btn btn-sm btn-outline-secondary">Limpar Filtros</a>
                </div>
            <?php endif; ?>

            <div class="col text-end small text-secondary">
                Metodologia Semanal: <strong>Prudential Life Planner + SPIN</strong>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1. GRADE SEMANAL (CALENDÁRIO DE SEGUNDA A DOMINGO) -->
<!-- ========================================================================= -->
<div class="row g-2 flex-nowrap overflow-x-auto pb-3 mb-4" style="min-height: 520px;">
    <?php foreach ($diasSemana as $diaData => $col): 
        $isHoje = $col['is_hoje'];
        $cardHeaderClass = $isHoje ? 'bg-primary text-white border-primary' : 'bg-dark text-light border-secondary';
        $borderColClass = $isHoje ? 'border-primary shadow' : 'border-secondary';
        $totalDia = count($col['atividades']);
    ?>
    <div class="col" style="min-width: 250px; max-width: 280px;">
        <div class="card bg-dark <?= $borderColClass ?> h-100 shadow-sm d-flex flex-column">
            
            <!-- Cabeçalho do Dia (Estilo Prudential) -->
            <div class="card-header py-2 <?= $cardHeaderClass ?> d-flex justify-content-between align-items-center">
                <div>
                    <strong class="d-block" style="font-size: 0.95rem;"><?= $col['data_formatada'] ?> (<?= $col['sigla'] ?>)</strong>
                    <small class="opacity-75" style="font-size: 0.72rem;"><?= $col['dia_semana'] ?></small>
                </div>
                <div class="text-end">
                    <span class="badge <?= $isHoje ? 'bg-light text-primary' : 'bg-secondary' ?>"><?= $totalDia ?></span>
                    <?php if ($isHoje): ?>
                        <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 0.65rem;">HOJE</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Corpo do Dia: Atividades por Horário -->
            <div class="card-body p-2 flex-grow-1" style="overflow-y: auto; max-height: 560px;">
                <?php if (empty($col['atividades'])): ?>
                    <div class="text-center text-secondary py-4 small fst-italic">
                        Nenhuma atividade agendada
                    </div>
                <?php else: ?>
                    <?php foreach ($col['atividades'] as $ativ): 
                        // Cor e Ícone do Tipo de Atividade
                        $tipoAtiv = $ativ['tipo_atividade'];
                        $badgeTipoClass = 'bg-secondary';
                        $iconTipo = 'bi-calendar-event';

                        if (stripos($tipoAtiv, 'liga') !== false) {
                            $badgeTipoClass = 'bg-info text-dark';
                            $iconTipo = 'bi-telephone-fill';
                        } elseif (stripos($tipoAtiv, 'abord') !== false) {
                            $badgeTipoClass = 'bg-primary';
                            $iconTipo = 'bi-geo-alt-fill';
                        } elseif (stripos($tipoAtiv, 'diag') !== false || stripos($tipoAtiv, 'spin') !== false) {
                            $badgeTipoClass = 'bg-info bg-opacity-75 text-dark';
                            $iconTipo = 'bi-search';
                        } elseif (stripos($tipoAtiv, 'apres') !== false) {
                            $badgeTipoClass = 'bg-warning text-dark';
                            $iconTipo = 'bi-easel2-fill';
                        } elseif (stripos($tipoAtiv, 'prop') !== false) {
                            $badgeTipoClass = 'bg-purple text-white';
                            $iconTipo = 'bi-file-earmark-text-fill';
                        } elseif (stripos($tipoAtiv, 'fech') !== false) {
                            $badgeTipoClass = 'bg-success';
                            $iconTipo = 'bi-trophy-fill';
                        }

                        $isRealizado = ($ativ['status_resultado'] === 'Realizado');
                        $isReagendado = ($ativ['status_resultado'] === 'Reagendado');
                        $cardBorder = $isRealizado ? 'border-success bg-success bg-opacity-10' : ($isReagendado ? 'border-warning bg-warning bg-opacity-10' : 'border-secondary bg-secondary bg-opacity-10');
                    ?>
                    <div class="card bg-dark <?= $cardBorder ?> mb-2 shadow-sm" style="font-size: 0.82rem;">
                        <div class="card-body p-2">
                            <!-- Horário e Tipo -->
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-dark border border-secondary text-light fw-bold">
                                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($ativ['horario_agendado']) ?>
                                </span>
                                <span class="badge <?= $badgeTipoClass ?>" style="<?= stripos($tipoAtiv, 'prop') !== false ? 'background-color:#6f42c1 !important;' : '' ?>">
                                    <i class="bi <?= $iconTipo ?> me-1"></i><?= htmlspecialchars($tipoAtiv) ?>
                                </span>
                            </div>

                            <!-- Nome do Cliente -->
                            <div class="fw-bold text-light mb-1 text-truncate" title="<?= htmlspecialchars($ativ['cliente_nome']) ?>">
                                <?= htmlspecialchars($ativ['cliente_nome']) ?>
                            </div>

                            <!-- Contato / Telefone -->
                            <?php if (!empty($ativ['contato_nome']) || !empty($ativ['contato_telefone'])): ?>
                                <div class="text-secondary small mb-1">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($ativ['contato_nome']) ?>
                                    <?php if (!empty($ativ['contato_telefone'])): ?>
                                        <a href="tel:<?= preg_replace('/\D/', '', $ativ['contato_telefone']) ?>" class="text-info text-decoration-none ms-1">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($ativ['contato_telefone']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Valor Estimado se houver -->
                            <?php if ($ativ['valor_estimado'] > 0): ?>
                                <div class="text-success fw-bold small mb-1">
                                    R$ <?= number_format($ativ['valor_estimado'], 2, ',', '.') ?>
                                </div>
                            <?php endif; ?>

                            <!-- Observações de resultado -->
                            <?php if (!empty($ativ['resultado_obs'])): ?>
                                <div class="small text-secondary bg-dark p-1 rounded border border-secondary mb-1" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($ativ['resultado_obs']) ?>
                                </div>
                            <?php endif; ?>

                            <hr class="border-secondary my-1">

                            <!-- Status e Ações Rápidas -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($isRealizado): ?>
                                        <span class="badge bg-success small"><i class="bi bi-check2-circle"></i> Realizado</span>
                                    <?php elseif ($isReagendado): ?>
                                        <span class="badge bg-warning text-dark small"><i class="bi bi-arrow-repeat"></i> Reagendado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary small">Planejado</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-1">
                                    <?php if (!$isRealizado): ?>
                                        <button class="btn btn-sm btn-outline-success p-0 px-1" title="Marcar como Realizado" onclick="concluirAtividade(<?= $ativ['id'] ?>)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning p-0 px-1" title="Reagendar" onclick="abrirModalReagendar(<?= htmlspecialchars(json_encode($ativ)) ?>)">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                    <?php endif; ?>
                                    <form method="POST" action="<?= BASE_URL ?>/?page=agenda&action=delete" class="d-inline" onsubmit="return confirm('Deseja excluir este agendamento?');">
                                        <input type="hidden" name="atividade_id" value="<?= $ativ['id'] ?>">
                                        <input type="hidden" name="data_ref" value="<?= htmlspecialchars($boundaries['monday']) ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 px-1" title="Excluir"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Rodapé: Botão Rápido de Agendar no Dia -->
            <div class="card-footer bg-dark border-secondary p-2 text-center">
                <button class="btn btn-sm btn-outline-secondary w-100 small" onclick="agendarNoDia('<?= $col['data'] ?>')">
                    <i class="bi bi-plus-lg me-1"></i> Agendar
                </button>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ========================================================================= -->
<!-- 2. QUADRO DE RESUMO DA SEMANA EM CURSO (ESTILO PRUDENTIAL LIFE PLANNER) -->
<!-- ========================================================================= -->
<div class="card bg-dark border-warning shadow mb-4">
    <div class="card-header bg-warning bg-opacity-10 border-warning py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clipboard-data-fill"></i> RESULTADO</span>
            <h5 class="card-title text-light mb-0 fw-bold">RESULTADOS DA ATIVIDADE DA SEMANA EM CURSO</h5>
        </div>
        <div class="text-secondary small">
            Semana # <strong><?= $weekNum ?></strong> • Meta Semanal Estilo Prudential Life Planner
        </div>
    </div>
    
    <div class="card-body p-0">
        <!-- Tabela Grade de Métricas da Semana (Semelhante à Folha Física) -->
        <div class="table-responsive">
            <table class="table table-dark table-bordered mb-0 text-center align-middle" style="border-color: #495057;">
                <thead>
                    <tr class="bg-secondary bg-opacity-25 text-secondary" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <th style="width: 14%;">LIGAÇÕES<br><span class="text-info fw-normal">(LIG)</span></th>
                        <th style="width: 14%;">ABORDAGENS<br><span class="text-primary fw-normal">(OI / CAMPO)</span></th>
                        <th style="width: 14%;">DIAGNÓSTICOS<br><span class="text-secondary fw-normal">(FF / SPIN)</span></th>
                        <th style="width: 14%;">APRESENTAÇÕES<br><span class="text-warning fw-normal">(P / REUNIÕES)</span></th>
                        <th style="width: 14%;">PROPOSTAS<br><span class="text-purple fw-normal" style="color:#b197fc;">(N / ENVIADAS)</span></th>
                        <th style="width: 14%;">FECHAMENTOS<br><span class="text-success fw-normal">(C / GANHOS 🎉)</span></th>
                        <th style="width: 16%;">CONVERSÃO & TOTAL<br><span class="text-warning fw-normal">(AP R$ / AC R$)</span></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Linha de Valores Grandes (Como os números preenchidos à caneta na folha) -->
                    <tr style="height: 75px;">
                        <!-- Ligações -->
                        <td>
                            <div class="fs-2 fw-bold text-info"><?= $resumo['ligacoes']['realizadas'] ?></div>
                            <small class="text-secondary" style="font-size: 0.72rem;">Planejadas: <?= $resumo['ligacoes']['planejadas'] ?></small>
                        </td>
                        <!-- Abordagens -->
                        <td>
                            <div class="fs-2 fw-bold text-primary"><?= $resumo['abordagens']['realizadas'] ?></div>
                            <small class="text-secondary" style="font-size: 0.72rem;">Planejadas: <?= $resumo['abordagens']['planejadas'] ?></small>
                        </td>
                        <!-- Diagnósticos -->
                        <td>
                            <div class="fs-2 fw-bold text-light"><?= $resumo['diagnosticos']['realizadas'] ?></div>
                            <small class="text-secondary" style="font-size: 0.72rem;">Planejadas: <?= $resumo['diagnosticos']['planejadas'] ?></small>
                        </td>
                        <!-- Apresentações -->
                        <td>
                            <div class="fs-2 fw-bold text-warning"><?= $resumo['apresentacoes']['realizadas'] ?></div>
                            <small class="text-secondary" style="font-size: 0.72rem;">Planejadas: <?= $resumo['apresentacoes']['planejadas'] ?></small>
                        </td>
                        <!-- Propostas Enviadas -->
                        <td>
                            <div class="fs-2 fw-bold" style="color: #b197fc;"><?= $resumo['propostas']['realizadas'] ?></div>
                            <small class="text-secondary" style="font-size: 0.72rem;">Planejadas: <?= $resumo['propostas']['planejadas'] ?></small>
                        </td>
                        <!-- Fechamentos -->
                        <td class="bg-success bg-opacity-10">
                            <div class="fs-2 fw-bold text-success"><?= $resumo['fechamentos']['realizadas'] ?></div>
                            <small class="text-success" style="font-size: 0.72rem;">Planejados: <?= $resumo['fechamentos']['planejadas'] ?></small>
                        </td>
                        <!-- Conversão e Volume Financeiro -->
                        <td class="text-start ps-3 bg-secondary bg-opacity-10">
                            <div class="small text-secondary">Taxa Conversão:</div>
                            <div class="fs-5 fw-bold text-warning mb-1"><?= $resumo['taxa_conversao'] ?>%</div>
                            <div class="small text-secondary">Fechado na Semana:</div>
                            <div class="fs-6 fw-bold text-success">R$ <?= number_format($resumo['valor_fechado_semana'], 2, ',', '.') ?></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Progresso Geral da Semana -->
        <div class="p-3 bg-secondary bg-opacity-10 border-top border-secondary d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="small text-secondary">
                    Total Atividades Realizadas: <strong><?= $resumo['total_realizadas'] ?></strong> de <strong><?= $resumo['total_atividades'] ?></strong> planejadas
                </span>
                <?php 
                $pctRealizada = $resumo['total_atividades'] > 0 ? round(($resumo['total_realizadas'] / $resumo['total_atividades']) * 100) : 0;
                ?>
                <div class="progress bg-dark border border-secondary" style="width: 150px; height: 10px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pctRealizada ?>%;"></div>
                </div>
                <span class="small text-warning fw-bold"><?= $pctRealizada ?>% Executado</span>
            </div>

            <div class="small text-secondary">
                <span class="me-3"><i class="bi bi-circle-fill text-info" style="font-size:8px;"></i> Ligações</span>
                <span class="me-3"><i class="bi bi-circle-fill text-primary" style="font-size:8px;"></i> Abordagens</span>
                <span class="me-3"><i class="bi bi-circle-fill text-warning" style="font-size:8px;"></i> Apresentações</span>
                <span class="me-3"><i class="bi bi-circle-fill" style="font-size:8px; color:#b197fc;"></i> Propostas</span>
                <span><i class="bi bi-circle-fill text-success" style="font-size:8px;"></i> Fechamentos</span>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL NOVO AGENDAMENTO -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalNovoAgendamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-primary">
            <form action="<?= BASE_URL ?>/?page=agenda&action=create" method="POST">
                <div class="modal-header border-primary bg-primary bg-opacity-25">
                    <h5 class="modal-title text-light"><i class="bi bi-calendar-plus me-1"></i> Agendar Atividade Comercial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Empresa</label>
                        <select name="empresa_alvo" class="form-select bg-dark text-light border-secondary" required>
                            <option value="Autoitec">Autoitec (Industrial B2B)</option>
                            <option value="Keepin">Keepin (IoT Varejo)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Tipo de Atividade *</label>
                            <select name="tipo_atividade" id="modal_tipo_atividade" class="form-select bg-dark text-light border-secondary" required>
                                <option value="Ligacao">📞 Ligação de Prospecção / Contato</option>
                                <option value="Abordagem">🎯 Abordagem Presencial / Visita</option>
                                <option value="Diagnostico">🔍 Diagnóstico Técnico (SPIN)</option>
                                <option value="Apresentacao">📊 Apresentação de Solução / Reunião</option>
                                <option value="Proposta">📄 Envio de Proposta Comercial</option>
                                <option value="Fechamento">🏆 Fechamento / Assinatura de Contrato</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Valor Estimado (R$)</label>
                            <input type="text" name="valor_estimado" class="form-control bg-dark text-light border-secondary" placeholder="0,00">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Data Agendada *</label>
                            <input type="date" name="data_agendada" id="modal_data_agendada" class="form-control bg-dark text-light border-secondary" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Horário *</label>
                            <input type="time" name="horario_agendado" id="modal_horario_agendado" class="form-control bg-dark text-light border-secondary" required value="09:00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome do Cliente / Empresa *</label>
                        <input type="text" name="cliente_nome" id="modal_cliente_nome" class="form-control bg-dark text-light border-secondary" required placeholder="Ex: Cerâmica Progresso / Mercado Bom Preço">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Contato Abordado</label>
                            <input type="text" name="contato_nome" id="modal_contato_nome" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Eng. Marcos / Gerente">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Telefone / WhatsApp</label>
                            <input type="text" name="contato_telefone" id="modal_contato_telefone" class="form-control bg-dark text-light border-secondary" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Vincular a Lead Existente (Opcional)</label>
                        <select name="lead_id" id="modal_lead_id" class="form-select bg-dark text-light border-secondary" onchange="preencherDadosLead(this)">
                            <option value="">Nenhum (Novo compromisso avulso)</option>
                            <?php foreach ($leadsRecentes as $l): ?>
                                <option value="<?= $l['id'] ?>" data-nome="<?= htmlspecialchars($l['nome_cliente_fantasia']) ?>" data-contato="<?= htmlspecialchars($l['contato_nome']) ?>" data-telefone="<?= htmlspecialchars($l['contato_telefone']) ?>" data-empresa="<?= htmlspecialchars($l['empresa_alvo']) ?>" data-valor="<?= $l['valor_estimado'] ?>">
                                    [<?= htmlspecialchars($l['empresa_alvo']) ?>] <?= htmlspecialchars($l['nome_cliente_fantasia']) ?> (<?= htmlspecialchars($l['etapa_funil']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Vendedor Responsável</label>
                        <select name="vendedor_id" class="form-select bg-dark text-light border-secondary">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($u['id'] == $_SESSION['user_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Observações / Pauta do Compromisso</label>
                        <textarea name="resultado_obs" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Objetivo da ligação, pontos a abordar na reunião..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Confirmar Agendamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL REAGENDAR ATIVIDADE -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalReagendar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-warning">
            <form action="<?= BASE_URL ?>/?page=agenda&action=reagendar" method="POST">
                <input type="hidden" name="atividade_id" id="reag_atividade_id">
                <div class="modal-header border-warning bg-warning bg-opacity-25">
                    <h5 class="modal-title text-warning"><i class="bi bi-clock-history me-1"></i> Reagendar Atividade</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-light mb-2">Cliente: <strong id="reag_cliente_nome"></strong></p>
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Nova Data *</label>
                            <input type="date" name="nova_data" id="reag_nova_data" class="form-control bg-dark text-light border-secondary" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Novo Horário *</label>
                            <input type="time" name="novo_horario" id="reag_novo_horario" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Motivo do Reagendamento</label>
                        <textarea name="motivo_reagendamento" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Cliente pediu para ligar na quinta-feira à tarde..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Salvar Reagendamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function agendarNoDia(dataStr) {
    document.getElementById('modal_data_agendada').value = dataStr;
    const modal = new bootstrap.Modal(document.getElementById('modalNovoAgendamento'));
    modal.show();
}

function preencherDadosLead(selectElem) {
    const opt = selectElem.options[selectElem.selectedIndex];
    if (opt.value) {
        document.getElementById('modal_cliente_nome').value = opt.getAttribute('data-nome') || '';
        document.getElementById('modal_contato_nome').value = opt.getAttribute('data-contato') || '';
        document.getElementById('modal_contato_telefone').value = opt.getAttribute('data-telefone') || '';
    }
}

function concluirAtividade(id) {
    const obs = prompt('Deseja adicionar alguma anotação sobre o resultado desta atividade? (Opcional):');
    if (obs === null) return; // cancelou

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/?page=agenda&action=update_status';

    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'atividade_id';
    inputId.value = id;
    form.appendChild(inputId);

    const inputStatus = document.createElement('input');
    inputStatus.type = 'hidden';
    inputStatus.name = 'status_resultado';
    inputStatus.value = 'Realizado';
    form.appendChild(inputStatus);

    const inputObs = document.createElement('input');
    inputObs.type = 'hidden';
    inputObs.name = 'resultado_obs';
    inputObs.value = obs || '';
    form.appendChild(inputObs);

    const inputDateRef = document.createElement('input');
    inputDateRef.type = 'hidden';
    inputDateRef.name = 'data_ref';
    inputDateRef.value = '<?= htmlspecialchars($boundaries['monday']) ?>';
    form.appendChild(inputDateRef);

    document.body.appendChild(form);
    form.submit();
}

function abrirModalReagendar(ativ) {
    document.getElementById('reag_atividade_id').value = ativ.id;
    document.getElementById('reag_cliente_nome').textContent = ativ.cliente_nome;
    document.getElementById('reag_nova_data').value = ativ.data_agendada;
    document.getElementById('reag_novo_horario').value = ativ.horario_agendado || '09:00';
    const modal = new bootstrap.Modal(document.getElementById('modalReagendar'));
    modal.show();
}
</script>
