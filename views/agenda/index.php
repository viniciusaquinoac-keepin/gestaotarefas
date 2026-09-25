<?php
$weekNum = $boundaries['week_number'];
$mondayFmt = date('d/m', strtotime($boundaries['monday']));
$sundayFmt = date('d/m/Y', strtotime($boundaries['sunday']));

$empresaCor = $empresa === 'Autoitec' ? 'primary' : ($empresa === 'Keepin' ? 'success' : 'info');
$empresaNome = $empresa === 'Autoitec' 
    ? 'Autoitec Engenharia Industrial (B2B)' 
    : ($empresa === 'Keepin' ? 'Keepin Automação & IoT (Field Sales)' : 'Visão Geral (Todas as Empresas)');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-<?= $empresaCor ?> <?= $empresaCor === 'info' ? 'text-dark' : '' ?> fs-6">
                <i class="bi bi-calendar-week"></i> Gestão Comercial Semanal
            </span>
            <span class="badge bg-warning text-dark fw-bold">SEMANA # <?= $weekNum ?></span>
            <span class="badge bg-secondary"><?= $mondayFmt ?> a <?= $sundayFmt ?></span>
        </div>
        <h1 class="h3 text-light mb-0"><?= $empresaNome ?></h1>
    </div>

    <!-- Seletor de Empresa e Ações -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Seletor de Empresa Semelhante à Tela de Prospecção -->
        <div class="btn-group shadow-sm" role="group">
            <a href="<?= BASE_URL ?>/?page=agenda&empresa=Autoitec&data_ref=<?= urlencode($boundaries['monday']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>" 
               class="btn btn-sm <?= $empresa === 'Autoitec' ? 'btn-primary active fw-bold' : 'btn-outline-primary' ?>">
                <i class="bi bi-gear-wide-connected me-1"></i> Autoitec (B2B)
            </a>
            <a href="<?= BASE_URL ?>/?page=agenda&empresa=Keepin&data_ref=<?= urlencode($boundaries['monday']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>" 
               class="btn btn-sm <?= $empresa === 'Keepin' ? 'btn-success active fw-bold' : 'btn-outline-success' ?>">
                <i class="bi bi-shop me-1"></i> Keepin (IoT)
            </a>
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= urlencode($boundaries['monday']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>" 
               class="btn btn-sm <?= empty($empresa) ? 'btn-light text-dark active fw-bold' : 'btn-outline-secondary' ?>">
                <i class="bi bi-grid-fill me-1"></i> Todas
            </a>
        </div>

        <!-- Navegação de Semanas -->
        <div class="btn-group shadow-sm" role="group">
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= urlencode($boundaries['prev_week']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>&empresa=<?= urlencode($empresa ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="Semana Anterior">
                <i class="bi bi-chevron-left"></i> Anterior
            </a>
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= date('Y-m-d') ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>&empresa=<?= urlencode($empresa ?? '') ?>" class="btn btn-sm btn-outline-info <?= ($boundaries['current_date'] === date('Y-m-d')) ? 'active' : '' ?>">
                Hoje
            </a>
            <a href="<?= BASE_URL ?>/?page=agenda&data_ref=<?= urlencode($boundaries['next_week']) ?>&vendedor_id=<?= urlencode($vendedorId ?? '') ?>&empresa=<?= urlencode($empresa ?? '') ?>" class="btn btn-sm btn-outline-secondary" title="Próxima Semana">
                Próxima <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <button class="btn btn-sm btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoAgendamento">
            <i class="bi bi-plus-circle me-1"></i> Agendar Atividade
        </button>

        <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="Imprimir Grade">
            <i class="bi bi-printer"></i>
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
                        $isPerdido = ($ativ['status_resultado'] === 'Perdido');

                        if ($isPerdido) {
                            $cardBorder = 'border-danger bg-danger bg-opacity-10';
                        } elseif ($isRealizado) {
                            $cardBorder = 'border-success bg-success bg-opacity-10';
                        } elseif ($isReagendado) {
                            $cardBorder = 'border-warning bg-warning bg-opacity-10';
                        } else {
                            $cardBorder = 'border-secondary bg-secondary bg-opacity-10';
                        }
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

                            <!-- Nome do Cliente e Passo do Ciclo de Vida -->
                            <div class="fw-bold text-light mb-1 text-truncate" title="<?= htmlspecialchars($ativ['cliente_nome']) ?>">
                                <?= htmlspecialchars($ativ['cliente_nome']) ?>
                            </div>

                            <!-- Indicador de Sequência e Tempo de Vida do Lead -->
                            <?php if (!empty($ativ['passo_sequencia']) && $ativ['passo_sequencia'] > 1): ?>
                                <div class="d-flex align-items-center justify-content-between mb-1 py-1 px-2 rounded bg-dark border border-secondary" style="font-size: 0.70rem;">
                                    <span class="text-info fw-bold">
                                        <i class="bi bi-diagram-3-fill me-1"></i> Passo #<?= $ativ['passo_sequencia'] ?>
                                    </span>
                                    <?php if (!empty($ativ['dias_desde_inicio']) && $ativ['dias_desde_inicio'] > 0): ?>
                                        <span class="text-secondary" title="Dias corridos desde a 1ª atividade deste cliente">
                                            <i class="bi bi-stopwatch"></i> +<?= $ativ['dias_desde_inicio'] ?>d de ciclo
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

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

                            <!-- Informações de Perda se houver -->
                            <?php if ($isPerdido): ?>
                                <div class="small text-danger bg-dark p-2 rounded border border-danger mb-1" style="font-size: 0.72rem;">
                                    <div><strong><i class="bi bi-x-octagon me-1"></i> Motivo:</strong> <?= htmlspecialchars($ativ['motivo_perda'] ?: 'Não informado') ?></div>
                                    <?php if (!empty($ativ['motivo_perda_obs'])): ?>
                                        <div class="text-secondary mt-1"><?= htmlspecialchars($ativ['motivo_perda_obs']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($ativ['data_recontato'])): ?>
                                        <div class="text-warning mt-1"><i class="bi bi-calendar2-event me-1"></i> Recontato: <?= date('d/m/Y', strtotime($ativ['data_recontato'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (!empty($ativ['resultado_obs'])): ?>
                                <div class="small text-secondary bg-dark p-1 rounded border border-secondary mb-1" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($ativ['resultado_obs']) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Link para Visualizar Linha do Tempo / Ciclo Completo -->
                            <div class="mb-1 text-end">
                                <a href="javascript:void(0)" class="text-info text-decoration-none small" style="font-size: 0.71rem;" onclick="abrirModalJornada(<?= $ativ['id'] ?>)">
                                    <i class="bi bi-clock-history me-1"></i> Ver Jornada Completa
                                </a>
                            </div>

                            <hr class="border-secondary my-1">

                            <!-- Status e Ações Rápidas -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($isRealizado): ?>
                                        <span class="badge bg-success small"><i class="bi bi-check2-circle"></i> Realizado</span>
                                    <?php elseif ($isPerdido): ?>
                                        <span class="badge bg-danger small"><i class="bi bi-x-circle-fill"></i> Perdido</span>
                                    <?php elseif ($isReagendado): ?>
                                        <span class="badge bg-warning text-dark small"><i class="bi bi-arrow-repeat"></i> Reagendado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary small">Planejado</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-1 align-items-center">
                                    <?php if (!$isRealizado && !$isPerdido): ?>
                                        <button class="btn btn-sm btn-outline-success p-0 px-1" title="Marcar como Realizado" onclick="concluirAtividade(<?= $ativ['id'] ?>)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning p-0 px-1" title="Reagendar" onclick="abrirModalReagendar(<?= htmlspecialchars(json_encode($ativ)) ?>)">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger p-0 px-1" title="Registrar Perda / Não Fechou" onclick="abrirModalRegistrarPerda(<?= htmlspecialchars(json_encode($ativ)) ?>)">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php elseif ($isRealizado): ?>
                                        <button class="btn btn-sm btn-outline-info p-0 px-1" title="Dar Sequência / Nova Atividade" onclick="gerarSequenciaAtividade(<?= htmlspecialchars(json_encode($ativ)) ?>)">
                                            <i class="bi bi-arrow-right-circle-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger p-0 px-1" title="Registrar Perda" onclick="abrirModalRegistrarPerda(<?= htmlspecialchars(json_encode($ativ)) ?>)">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <form method="POST" action="<?= BASE_URL ?>/?page=agenda&action=delete" class="d-inline" onsubmit="return confirm('Deseja excluir este agendamento?');">
                                        <input type="hidden" name="atividade_id" value="<?= $ativ['id'] ?>">
                                        <input type="hidden" name="data_ref" value="<?= htmlspecialchars($boundaries['monday']) ?>">
                                        <input type="hidden" name="empresa" value="<?= htmlspecialchars($empresa ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 px-1" title="Excluir"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>

                            <?php if ($isRealizado): ?>
                                <!-- Botão de Sequência Rápida para o Comercial -->
                                <button type="button" class="btn btn-sm btn-outline-info w-100 mt-2 py-1 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1" 
                                        onclick="gerarSequenciaAtividade(<?= htmlspecialchars(json_encode($ativ)) ?>)"
                                        title="Dar sequência agendando a próxima atividade deste lead">
                                    <i class="bi bi-arrow-right-circle-fill"></i> Dar Sequência / Nova Atividade
                                </button>
                            <?php elseif ($isPerdido): ?>
                                <button type="button" class="btn btn-sm btn-outline-warning w-100 mt-2 py-1 small fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1"
                                        onclick="abrirModalReagendar(<?= htmlspecialchars(json_encode($ativ)) ?>)">
                                    <i class="bi bi-arrow-repeat"></i> Tentar Recontato Agora
                                </button>
                            <?php endif; ?>
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
                        <th style="width: 12%;">LIGAÇÕES<br><span class="text-info fw-normal">(LIG)</span></th>
                        <th style="width: 12%;">ABORDAGENS<br><span class="text-primary fw-normal">(OI / CAMPO)</span></th>
                        <th style="width: 12%;">DIAGNÓSTICOS<br><span class="text-secondary fw-normal">(FF / SPIN)</span></th>
                        <th style="width: 12%;">APRESENTAÇÕES<br><span class="text-warning fw-normal">(P / REUNIÕES)</span></th>
                        <th style="width: 12%;">PROPOSTAS<br><span class="text-purple fw-normal" style="color:#b197fc;">(N / ENVIADAS)</span></th>
                        <th style="width: 12%;">FECHAMENTOS<br><span class="text-success fw-normal">(C / GANHOS 🎉)</span></th>
                        <th style="width: 12%;">PERDAS<br><span class="text-danger fw-normal">(RECUSAS ❌)</span></th>
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
                        <!-- Perdas / Recusas -->
                        <td class="bg-danger bg-opacity-10">
                            <div class="fs-2 fw-bold text-danger"><?= $resumo['perdas']['total'] ?></div>
                            <small class="text-danger d-block" style="font-size: 0.72rem;">R$ <?= number_format($resumo['perdas']['valor'], 2, ',', '.') ?></small>
                            <?php if (!empty($resumo['perdas']['motivos'])): ?>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger p-1 mt-1" style="font-size: 0.65rem;" title="Principais motivos de perda">
                                    <?= htmlspecialchars($resumo['perdas']['motivos'][0]['motivo_perda'] ?? '') ?> (<?= $resumo['perdas']['motivos'][0]['qtd'] ?? 0 ?>x)
                                </span>
                            <?php endif; ?>
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

        <!-- Painel de Velocidade Comercial & Tempo de Vida Médio (Sales Cycle / Lead Time) -->
        <div class="p-3 bg-dark border-top border-secondary">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4 col-md-5">
                    <div class="p-2 rounded bg-secondary bg-opacity-10 border border-secondary d-flex align-items-center gap-3">
                        <div class="fs-1 text-warning"><i class="bi bi-stopwatch"></i></div>
                        <div>
                            <div class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Tempo Médio de Ciclo da Venda</div>
                            <?php if (!empty($resumo['tempo_medio_fechamento'])): ?>
                                <div class="fs-4 fw-bold text-warning">
                                    <?= $resumo['tempo_medio_fechamento'] ?> <span class="fs-6 fw-normal text-light">dias</span>
                                </div>
                                <div class="text-secondary small" style="font-size: 0.72rem;">
                                    Do 1º telefonema ao contrato (Mín: <?= $resumo['min_ciclo_fechamento'] ?>d | Máx: <?= $resumo['max_ciclo_fechamento'] ?>d • <?= $resumo['total_fechamentos_ciclo'] ?> fechamentos)
                                </div>
                            <?php else: ?>
                                <div class="fs-6 fw-bold text-secondary">Aguardando fechamentos</div>
                                <div class="text-secondary small" style="font-size: 0.72rem;">
                                    Mede o tempo entre o contato telefônico inicial e o fechamento
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-7">
                    <div class="p-2 rounded bg-secondary bg-opacity-10 border border-secondary">
                        <div class="text-secondary small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="bi bi-diagram-3-fill text-info me-1"></i> Régua Sequencial do Ciclo de Vida da Oportunidade
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 text-center" style="font-size: 0.73rem;">
                            <div class="p-1 px-2 rounded bg-info bg-opacity-25 border border-info text-info">
                                📞 1. Ligação
                            </div>
                            <i class="bi bi-arrow-right text-secondary"></i>
                            <div class="p-1 px-2 rounded bg-primary bg-opacity-25 border border-primary text-light">
                                🎯 2. Campo
                            </div>
                            <i class="bi bi-arrow-right text-secondary"></i>
                            <div class="p-1 px-2 rounded bg-info bg-opacity-25 border border-info text-light">
                                📋 3. Diagnóstico
                            </div>
                            <i class="bi bi-arrow-right text-secondary"></i>
                            <div class="p-1 px-2 rounded bg-warning bg-opacity-25 border border-warning text-warning">
                                📊 4. Apresentação
                            </div>
                            <i class="bi bi-arrow-right text-secondary"></i>
                            <div class="p-1 px-2 rounded bg-purple bg-opacity-25 border text-light" style="border-color:#6f42c1 !important;">
                                📄 5. Proposta
                            </div>
                            <i class="bi bi-arrow-right text-secondary"></i>
                            <div class="p-1 px-2 rounded bg-success bg-opacity-25 border border-success text-success fw-bold">
                                🏆 6. Fechamento
                            </div>
                        </div>
                    </div>
                </div>
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
                <input type="hidden" name="empresa_filtro" value="<?= htmlspecialchars($empresa ?? '') ?>">
                <input type="hidden" name="atividade_anterior_id" id="modal_atividade_anterior_id">
                <input type="hidden" name="ciclo_origem_id" id="modal_ciclo_origem_id">
                <input type="hidden" name="data_primeiro_contato" id="modal_data_primeiro_contato">
                <input type="hidden" name="passo_sequencia" id="modal_passo_sequencia" value="1">
                <div class="modal-header border-primary bg-primary bg-opacity-25">
                    <h5 class="modal-title text-light" id="modalNovoAgendamentoTitulo"><i class="bi bi-calendar-plus me-1"></i> Agendar Atividade Comercial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Banner de Encadeamento de Sequência -->
                    <div id="banner_sequencia_ciclo" class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between" style="display: none;">
                        <div>
                            <i class="bi bi-diagram-3-fill me-1"></i> <strong>Sequência Comercial Ativa</strong>
                            <div class="small" id="banner_sequencia_texto">Continuando ciclo de vida do cliente</div>
                        </div>
                        <span class="badge bg-info text-dark" id="badge_passo_sequencia">Passo #2</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Empresa Alvo</label>
                        <select name="empresa_alvo" id="modal_empresa_alvo" class="form-select bg-dark text-light border-secondary" required>
                            <option value="Autoitec" <?= ($empresa === 'Autoitec') ? 'selected' : '' ?>>Autoitec (Industrial B2B)</option>
                            <option value="Keepin" <?= ($empresa === 'Keepin') ? 'selected' : '' ?>>Keepin (IoT Varejo)</option>
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
                            <input type="text" name="valor_estimado" id="modal_valor_estimado" class="form-control bg-dark text-light border-secondary" placeholder="0,00">
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

                    <div class="form-check form-switch mb-3 p-2 bg-dark rounded border border-secondary" id="box_atualizar_lead" style="display: none;">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="atualizar_etapa_lead" id="modal_atualizar_etapa_lead" value="1" checked>
                        <label class="form-check-label small text-info fw-bold" for="modal_atualizar_etapa_lead">
                            <i class="bi bi-funnel"></i> Atualizar também a etapa do lead no funil de prospecção para acompanhar esta atividade
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Vendedor Responsável</label>
                        <select name="vendedor_id" id="modal_vendedor_id" class="form-select bg-dark text-light border-secondary">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($u['id'] == $_SESSION['user_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Observações / Pauta do Compromisso</label>
                        <textarea name="resultado_obs" id="modal_resultado_obs" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Objetivo da ligação, pontos a abordar na reunião..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="btnSalvarAgendamento">Confirmar Agendamento</button>
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
                <input type="hidden" name="empresa" value="<?= htmlspecialchars($empresa ?? '') ?>">
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

<!-- ========================================================================= -->
<!-- MODAL REGISTRAR PERDA / RECUSA COMERCIAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalRegistrarPerdaAgenda" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-danger">
            <form action="<?= BASE_URL ?>/?page=agenda&action=registrar_perda" method="POST">
                <input type="hidden" name="atividade_id" id="perda_atividade_id">
                <input type="hidden" name="data_ref" value="<?= htmlspecialchars($boundaries['monday']) ?>">
                <input type="hidden" name="empresa" value="<?= htmlspecialchars($empresa ?? '') ?>">
                
                <div class="modal-header border-danger bg-danger bg-opacity-25">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-x-octagon-fill me-1"></i> Registrar Perda / Recusa Comercial
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Esta ação marca a atividade como <strong>Perdida</strong>, atualiza o lead no funil de prospecção e permite agendar um recontato futuro com criação de tarefa automática no Kanban.
                    </div>

                    <div class="p-2 rounded bg-secondary bg-opacity-10 border border-secondary mb-3 small">
                        <div>Cliente: <strong class="text-light fs-6" id="perda_cliente_nome"></strong></div>
                        <div class="text-secondary">Atividade: <span id="perda_tipo_atividade" class="badge bg-secondary"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-danger">Motivo da Perda (Obrigatório) *</label>
                        <select name="motivo_perda" id="perda_motivo_perda" class="form-select bg-dark text-light border-secondary" required>
                            <option value="">Selecione o motivo da perda...</option>
                            <option value="Preco/CAPEX">Preço / CAPEX Elevado (Sem Verba)</option>
                            <option value="Concorrente">Optou por Concorrente</option>
                            <option value="Decisor Nao Acessado">Decisor Não Acessado / Bloqueado</option>
                            <option value="Sem Orcamento">Sem Orçamento / Projeto Congelado</option>
                            <option value="Sem Interesse / Desistiu">Sem Interesse no momento / Desistiu</option>
                            <option value="Prazo / Urgencia">Prazo ou Urgência Incompatível</option>
                            <option value="Outro">Outro Motivo</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-warning">Data para Recontato Futuro (Opcional)</label>
                        <input type="date" name="data_recontato_futuro" id="perda_data_recontato" class="form-control bg-dark text-light border-secondary" min="<?= date('Y-m-d') ?>">
                        <div class="form-text text-secondary small">
                            Se preenchida, criará uma tarefa de recontato comercial atribuída ao vendedor no Kanban.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Observações e Lições Aprendidas</label>
                        <textarea name="motivo_perda_obs" id="perda_motivo_obs" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="O que faltou para o fechamento? O que podemos melhorar em abordagens futuras?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-x-circle me-1"></i> Confirmar Perda
                    </button>
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
    const boxAtualizar = document.getElementById('box_atualizar_lead');
    if (opt.value) {
        document.getElementById('modal_cliente_nome').value = opt.getAttribute('data-nome') || '';
        document.getElementById('modal_contato_nome').value = opt.getAttribute('data-contato') || '';
        document.getElementById('modal_contato_telefone').value = opt.getAttribute('data-telefone') || '';
        if (opt.getAttribute('data-empresa') && document.getElementById('modal_empresa_alvo')) {
            document.getElementById('modal_empresa_alvo').value = opt.getAttribute('data-empresa');
        }
        if (boxAtualizar) boxAtualizar.style.display = 'block';
    } else {
        if (boxAtualizar) boxAtualizar.style.display = 'none';
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

    const inputEmpresa = document.createElement('input');
    inputEmpresa.type = 'hidden';
    inputEmpresa.name = 'empresa';
    inputEmpresa.value = '<?= htmlspecialchars($empresa ?? '') ?>';
    form.appendChild(inputEmpresa);

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

function abrirModalRegistrarPerda(ativ) {
    document.getElementById('perda_atividade_id').value = ativ.id;
    document.getElementById('perda_cliente_nome').textContent = ativ.cliente_nome || 'Cliente';
    document.getElementById('perda_tipo_atividade').textContent = ativ.tipo_atividade || '';
    document.getElementById('perda_motivo_perda').value = ativ.motivo_perda || '';
    document.getElementById('perda_motivo_obs').value = ativ.motivo_perda_obs || '';
    document.getElementById('perda_data_recontato').value = ativ.data_recontato || '';
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarPerdaAgenda'));
    modal.show();
}

function gerarSequenciaAtividade(ativ) {
    // 1. Ajustar título e botão do modal
    const modalTitulo = document.getElementById('modalNovoAgendamentoTitulo');
    if (modalTitulo) {
        modalTitulo.innerHTML = '<i class="bi bi-arrow-repeat me-1 text-info"></i> Dar Sequência: ' + (ativ.cliente_nome || 'Lead');
    }
    const btnSalvar = document.getElementById('btnSalvarAgendamento');
    if (btnSalvar) {
        btnSalvar.textContent = 'Agendar Próxima Atividade';
    }

    // 2. Informações de Encadeamento e Ciclo de Vida
    const inputAntId = document.getElementById('modal_atividade_anterior_id');
    const inputCicloId = document.getElementById('modal_ciclo_origem_id');
    const inputDataPrim = document.getElementById('modal_data_primeiro_contato');
    const inputPasso = document.getElementById('modal_passo_sequencia');
    const bannerCiclo = document.getElementById('banner_sequencia_ciclo');
    const badgePasso = document.getElementById('badge_passo_sequencia');
    const bannerTexto = document.getElementById('banner_sequencia_texto');

    const proxPasso = (parseInt(ativ.passo_sequencia) || 1) + 1;
    const primData = ativ.data_primeiro_contato || ativ.data_agendada;

    if (inputAntId) inputAntId.value = ativ.id;
    if (inputCicloId) inputCicloId.value = ativ.ciclo_origem_id || ativ.id;
    if (inputDataPrim) inputDataPrim.value = primData;
    if (inputPasso) inputPasso.value = proxPasso;

    if (bannerCiclo) {
        bannerCiclo.style.display = 'flex';
        if (badgePasso) badgePasso.textContent = 'Passo #' + proxPasso;
        if (bannerTexto) bannerTexto.textContent = 'Cliente: ' + (ativ.cliente_nome || '') + ' • 1º Contato em ' + primData;
    }

    // 3. Preencher empresa e dados de contato
    if (document.getElementById('modal_empresa_alvo') && ativ.empresa_alvo) {
        document.getElementById('modal_empresa_alvo').value = ativ.empresa_alvo;
    }
    if (document.getElementById('modal_cliente_nome')) {
        document.getElementById('modal_cliente_nome').value = ativ.cliente_nome || '';
    }
    if (document.getElementById('modal_contato_nome')) {
        document.getElementById('modal_contato_nome').value = ativ.contato_nome || '';
    }
    if (document.getElementById('modal_contato_telefone')) {
        document.getElementById('modal_contato_telefone').value = ativ.contato_telefone || '';
    }

    // 4. Vincular Lead se houver
    const leadSelect = document.getElementById('modal_lead_id');
    const boxAtualizar = document.getElementById('box_atualizar_lead');
    if (leadSelect) {
        if (ativ.lead_id) {
            leadSelect.value = ativ.lead_id;
            if (boxAtualizar) boxAtualizar.style.display = 'block';
        } else {
            let achou = false;
            for (let i = 0; i < leadSelect.options.length; i++) {
                if (leadSelect.options[i].text.toLowerCase().includes((ativ.cliente_nome || '').toLowerCase().trim())) {
                    leadSelect.selectedIndex = i;
                    achou = true;
                    if (boxAtualizar) boxAtualizar.style.display = 'block';
                    break;
                }
            }
            if (!achou) {
                leadSelect.value = '';
                if (boxAtualizar) boxAtualizar.style.display = 'none';
            }
        }
    }

    // 5. Determinar próxima atividade recomendada no fluxo de vendas
    // Ordem natural: Ligação -> Abordagem -> Diagnóstico -> Apresentação -> Proposta -> Fechamento
    const tipoSelect = document.getElementById('modal_tipo_atividade');
    let proxAtiv = 'Abordagem';
    const tipoAnt = (ativ.tipo_atividade || '').toLowerCase();

    if (tipoAnt.includes('liga')) {
        proxAtiv = 'Abordagem'; // ligou -> vai em campo abordar ou apresentar proposta
    } else if (tipoAnt.includes('abord')) {
        proxAtiv = 'Apresentacao';
    } else if (tipoAnt.includes('diag') || tipoAnt.includes('spin')) {
        proxAtiv = 'Apresentacao';
    } else if (tipoAnt.includes('apres')) {
        proxAtiv = 'Proposta';
    } else if (tipoAnt.includes('prop')) {
        proxAtiv = 'Fechamento';
    } else if (tipoAnt.includes('fech')) {
        proxAtiv = 'Fechamento';
    }
    if (tipoSelect) {
        tipoSelect.value = proxAtiv;
    }

    // 6. Data sugerida: hoje ou amanhã
    const hoje = new Date().toISOString().split('T')[0];
    const dataSug = (ativ.data_agendada && ativ.data_agendada >= hoje) ? ativ.data_agendada : hoje;
    if (document.getElementById('modal_data_agendada')) {
        document.getElementById('modal_data_agendada').value = dataSug;
    }
    if (document.getElementById('modal_horario_agendado')) {
        document.getElementById('modal_horario_agendado').value = '14:00';
    }

    // 7. Valor estimado se houver
    if (ativ.valor_estimado && ativ.valor_estimado > 0) {
        const valElem = document.getElementById('modal_valor_estimado');
        if (valElem) valElem.value = ativ.valor_estimado;
    }

    // 8. Vendedor
    if (ativ.vendedor_id && document.getElementById('modal_vendedor_id')) {
        document.getElementById('modal_vendedor_id').value = ativ.vendedor_id;
    }

    // 9. Observações de sequência
    const txtObs = document.getElementById('modal_resultado_obs');
    if (txtObs) {
        let obsTxt = 'Passo #' + proxPasso + ' após ' + ativ.tipo_atividade + ' realizada.';
        if (ativ.resultado_obs) {
            obsTxt += ' (Anterior: ' + ativ.resultado_obs + ')';
        }
        txtObs.value = obsTxt;
    }

    // 10. Abrir Modal
    const modal = new bootstrap.Modal(document.getElementById('modalNovoAgendamento'));
    modal.show();
}

function abrirModalJornada(ativId) {
    const modal = new bootstrap.Modal(document.getElementById('modalJornadaCliente'));
    document.getElementById('jornadaLoading').style.display = 'block';
    document.getElementById('jornadaContent').style.display = 'none';
    modal.show();

    fetch('<?= BASE_URL ?>/?page=agenda&action=ciclo_vida&id=' + ativId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('jornadaLoading').style.display = 'none';
            document.getElementById('jornadaContent').style.display = 'block';

            if (!data.success || !data.cadeia || data.cadeia.length === 0) {
                document.getElementById('jornadaTimelineItens').innerHTML = '<p class="text-secondary small fst-italic">Nenhum evento encadeado encontrado para esta oportunidade.</p>';
                return;
            }

            const cadeia = data.cadeia;
            const primeira = cadeia[0];
            const ultima = cadeia[cadeia.length - 1];

            document.getElementById('jornadaClienteNome').textContent = primeira.cliente_nome;
            document.getElementById('jornadaLeadInfo').textContent = (primeira.empresa_alvo || 'Autoitec') + (primeira.contato_nome ? ' • Contato: ' + primeira.contato_nome : '');
            
            const diasTotais = ultima.dias_desde_inicio || 0;
            document.getElementById('jornadaTempoTotal').textContent = diasTotais + (diasTotais === 1 ? ' dia de ciclo' : ' dias de ciclo comercial');
            document.getElementById('jornadaTotalPassos').textContent = cadeia.length + (cadeia.length === 1 ? ' evento registrado' : ' eventos encadeados');

            let html = '';
            cadeia.forEach((item, idx) => {
                let badgeClass = 'bg-secondary';
                let iconClass = 'bi-circle-fill';
                const t = (item.tipo_atividade || '').toLowerCase();

                if (t.includes('liga')) { badgeClass = 'bg-info text-dark'; iconClass = 'bi-telephone-fill'; }
                else if (t.includes('abord')) { badgeClass = 'bg-primary'; iconClass = 'bi-geo-alt-fill'; }
                else if (t.includes('diag') || t.includes('spin')) { badgeClass = 'bg-info bg-opacity-75 text-dark'; iconClass = 'bi-search'; }
                else if (t.includes('apres')) { badgeClass = 'bg-warning text-dark'; iconClass = 'bi-easel2-fill'; }
                else if (t.includes('prop')) { badgeClass = 'bg-purple text-white'; iconClass = 'bi-file-earmark-text-fill'; }
                else if (t.includes('fech')) { badgeClass = 'bg-success'; iconClass = 'bi-trophy-fill'; }

                const isPerdido = (item.status_resultado === 'Perdido');
                const isRealizado = (item.status_resultado === 'Realizado');
                let statusBadge = '<span class="badge bg-secondary small">Planejado</span>';
                if (isPerdido) {
                    statusBadge = '<span class="badge bg-danger small"><i class="bi bi-x-circle-fill"></i> Perdido</span>';
                    badgeClass = 'bg-danger text-white';
                    iconClass = 'bi-x-circle-fill';
                } else if (isRealizado) {
                    statusBadge = '<span class="badge bg-success small"><i class="bi bi-check2"></i> Realizado</span>';
                }

                let perdaInfoHtml = '';
                if (isPerdido && item.motivo_perda) {
                    perdaInfoHtml = `
                        <div class="p-2 bg-danger bg-opacity-10 rounded border border-danger small text-danger mt-1">
                            <div><strong><i class="bi bi-x-octagon me-1"></i> Motivo da Perda:</strong> ${item.motivo_perda}</div>
                            ${item.motivo_perda_obs ? `<div class="text-secondary mt-1">${item.motivo_perda_obs}</div>` : ''}
                            ${item.data_recontato ? `<div class="text-warning mt-1"><i class="bi bi-calendar2-event me-1"></i> Recontato agendado para: <strong>${item.data_recontato}</strong></div>` : ''}
                        </div>
                    `;
                }

                html += `
                    <div class="position-relative mb-4">
                        <div class="position-absolute translate-middle-x" style="left: -25px; top: 2px;">
                            <span class="badge rounded-circle p-2 ${badgeClass}">
                                <i class="bi ${iconClass}"></i>
                            </span>
                        </div>
                        <div class="card bg-dark ${isPerdido ? 'border-danger bg-danger bg-opacity-10' : 'border-secondary'} p-3 shadow-sm ms-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="badge ${badgeClass} me-2">${item.tipo_atividade}</span>
                                    <span class="text-light fw-bold">Passo #${item.passo_sequencia || (idx + 1)}</span>
                                    ${statusBadge}
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-dark border border-secondary text-secondary">
                                        <i class="bi bi-calendar3 me-1"></i>${item.data_agendada} às ${item.horario_agendado}
                                    </span>
                                </div>
                            </div>
                            <div class="small text-secondary mb-1">
                                Vendedor: <strong class="text-light">${item.vendedor_nome || 'Equipe'}</strong>
                                ${item.valor_estimado > 0 ? ` • <span class="text-success fw-bold">R$ ${parseFloat(item.valor_estimado).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>` : ''}
                            </div>
                            ${item.resultado_obs ? `<div class="p-2 bg-secondary bg-opacity-10 rounded border border-secondary small text-light mt-1">${item.resultado_obs}</div>` : ''}
                            ${perdaInfoHtml}
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary small text-secondary" style="font-size: 0.72rem;">
                                <span>${idx === 0 ? '🏁 Início do ciclo de vida' : `⏱️ +${item.dias_desde_anterior} dia(s) após a etapa anterior`}</span>
                                <span>Total acumulado: <strong>${item.dias_desde_inicio} dia(s)</strong></span>
                            </div>
                        </div>
                    </div>
                `;
            });

            document.getElementById('jornadaTimelineItens').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('jornadaLoading').style.display = 'none';
            document.getElementById('jornadaContent').style.display = 'block';
            document.getElementById('jornadaTimelineItens').innerHTML = '<div class="alert alert-danger small">Erro ao carregar histórico da jornada.</div>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const modalElem = document.getElementById('modalNovoAgendamento');
    if (modalElem) {
        modalElem.addEventListener('hidden.bs.modal', function () {
            const modalTitulo = document.getElementById('modalNovoAgendamentoTitulo');
            if (modalTitulo) {
                modalTitulo.innerHTML = '<i class="bi bi-calendar-plus me-1"></i> Agendar Atividade Comercial';
            }
            const btnSalvar = document.getElementById('btnSalvarAgendamento');
            if (btnSalvar) {
                btnSalvar.textContent = 'Confirmar Agendamento';
            }
            const boxAtualizar = document.getElementById('box_atualizar_lead');
            if (boxAtualizar) {
                boxAtualizar.style.display = 'none';
            }
            const bannerCiclo = document.getElementById('banner_sequencia_ciclo');
            if (bannerCiclo) {
                bannerCiclo.style.display = 'none';
            }
            // Resetar inputs de encadeamento
            if (document.getElementById('modal_atividade_anterior_id')) document.getElementById('modal_atividade_anterior_id').value = '';
            if (document.getElementById('modal_ciclo_origem_id')) document.getElementById('modal_ciclo_origem_id').value = '';
            if (document.getElementById('modal_data_primeiro_contato')) document.getElementById('modal_data_primeiro_contato').value = '';
            if (document.getElementById('modal_passo_sequencia')) document.getElementById('modal_passo_sequencia').value = '1';
        });
    }
});
</script>

<!-- ========================================================================= -->
<!-- MODAL LINHA DO TEMPO / JORNADA DO CLIENTE -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalJornadaCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-info">
            <div class="modal-header border-info bg-info bg-opacity-25">
                <h5 class="modal-title text-light" id="modalJornadaTitulo">
                    <i class="bi bi-clock-history me-1 text-info"></i> Linha do Tempo & Ciclo de Vida da Oportunidade
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="jornadaLoading" class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="text-secondary small mt-2">Carregando jornada de eventos...</p>
                </div>
                <div id="jornadaContent" style="display: none;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 p-3 bg-secondary bg-opacity-10 rounded border border-secondary gap-2">
                        <div>
                            <h5 class="text-light fw-bold mb-0" id="jornadaClienteNome">Cliente</h5>
                            <small class="text-secondary" id="jornadaLeadInfo">Lead / Contato</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark fs-6" id="jornadaTempoTotal">0 dias de ciclo</span>
                            <small class="text-secondary d-block mt-1" id="jornadaTotalPassos">0 eventos encadeados</small>
                        </div>
                    </div>

                    <!-- Linha do Tempo Vertical -->
                    <div class="timeline-container position-relative ps-4 border-start border-2 border-secondary ms-3" id="jornadaTimelineItens">
                        <!-- Renderizado via JavaScript -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
