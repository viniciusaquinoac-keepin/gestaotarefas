<?php
$score = $userKpi['score_total'];
$comissao = $userKpi['percentual_comissao_devido'];
$acelerador = $userKpi['elegivel_acelerador_500'];
$isCurrentQuarter = ($trimestre === $trimestreAtivo);

// Cor do Score
$scoreColor = '#dc3545'; // vermelho < 50
if ($score >= 100) $scoreColor = '#ffc107'; // dourado
elseif ($score >= 85) $scoreColor = '#198754'; // verde
elseif ($score >= 70) $scoreColor = '#0dcaf0'; // azul/ciano
elseif ($score >= 50) $scoreColor = '#fd7e14'; // laranja
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-trophy-fill"></i> Gamificação & Metrificação</span>
            <?php if ($isCurrentQuarter): ?>
                <span class="badge bg-success"><i class="bi bi-broadcast"></i> Ciclo Trimestral Ativo (Dinâmico)</span>
            <?php else: ?>
                <span class="badge bg-secondary"><i class="bi bi-archive"></i> Histórico Arquivado</span>
            <?php endif; ?>
        </div>
        <h1 class="h3 text-light mb-0">Painel de KPIs, Velocímetro & Ranking Aberto</h1>
    </div>

    <!-- Seletor de Trimestre Dinâmico -->
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="<?= BASE_URL ?>/" class="d-flex align-items-center gap-2">
            <input type="hidden" name="page" value="kpis">
            <label class="text-secondary small text-nowrap">Trimestre:</label>
            <select name="trimestre" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                <?php foreach ($trimestresDisponiveis as $k => $label): ?>
                    <option value="<?= $k ?>" <?= ($trimestre === $k) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Informativo do Ciclo Calendardizado 100% Dinâmico -->
<div class="alert alert-dark border-secondary d-flex align-items-center justify-content-between py-2 px-3 mb-4 shadow-sm">
    <div class="small text-secondary">
        <i class="bi bi-info-circle text-info me-1"></i> <strong>Apuração Dinâmica:</strong> Os lançamentos a partir de <strong>24/09/2026</strong> computam oficialmente no ciclo <strong>2026-Q4</strong>. O encerramento e rotação de pontuação ocorrem de forma 100% automática pelo calendário.
    </div>
    <div class="text-end small">
        <span class="text-secondary">Período de Apuração:</span>
        <strong class="text-light"><?= date('d/m/Y', strtotime($dateRange['start_date'])) ?> até <?= date('d/m/Y', strtotime($dateRange['end_date'])) ?></strong>
    </div>
</div>

<!-- SELEÇÃO VISÃO POR DEPARTAMENTO -->
<div class="row g-3 mb-4">
    <!-- Card Automação / Engenharia -->
    <div class="col-md-6">
        <div class="card bg-dark border-secondary h-100 shadow-sm">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-2">
                <span class="text-info fw-bold small"><i class="bi bi-gear-fill me-1"></i> Engenharia & Automação (Operacional)</span>
                <span class="badge bg-secondary">SLA & OTIF</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="bg-secondary bg-opacity-10 rounded p-2 border border-secondary">
                            <div class="text-secondary small" style="font-size: 0.75rem;">Total Prorrogações</div>
                            <div class="fs-5 fw-bold text-light"><?= (int)($prorrogacoesStats['total_prorrogacoes'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-success bg-opacity-10 rounded p-2 border border-success">
                            <div class="text-success small" style="font-size: 0.75rem;">Abonadas (Cliente)</div>
                            <div class="fs-5 fw-bold text-success"><?= (int)($prorrogacoesStats['abonadas'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-danger bg-opacity-10 rounded p-2 border border-danger">
                            <div class="text-danger small" style="font-size: 0.75rem;">Penalizadas (Interno)</div>
                            <div class="fs-5 fw-bold text-danger"><?= (int)($prorrogacoesStats['penalizadas'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 small text-secondary">
                    <i class="bi bi-shield-check text-success"></i> <strong>Abono de SLA Ativo:</strong> Prorrogações com culpa atribuída ao <em>Cliente/Planta</em> não diminuem o score de OTIF dos colaboradores.
                </div>
            </div>
        </div>
    </div>

    <!-- Card Comercial / Agenda Semanal Consolidada -->
    <div class="col-md-6">
        <div class="card bg-dark border-secondary h-100 shadow-sm">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-2">
                <span class="text-warning fw-bold small"><i class="bi bi-calendar-check-fill me-1"></i> Comercial & Agenda Semanal (Consolidado)</span>
                <span class="badge bg-secondary">Equipe no Trimestre</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 text-center">
                    <div class="col-3">
                        <div class="bg-secondary bg-opacity-10 rounded p-2 border border-secondary">
                            <div class="text-secondary small" style="font-size: 0.72rem;">Ligações</div>
                            <div class="fs-5 fw-bold text-light"><?= (int)($agendaEquipeStats['total_ligacoes'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="bg-secondary bg-opacity-10 rounded p-2 border border-secondary">
                            <div class="text-secondary small" style="font-size: 0.72rem;">Abordagens</div>
                            <div class="fs-5 fw-bold text-info"><?= (int)($agendaEquipeStats['total_abordagens'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="bg-secondary bg-opacity-10 rounded p-2 border border-secondary">
                            <div class="text-secondary small" style="font-size: 0.72rem;">Propostas</div>
                            <div class="fs-5 fw-bold text-warning"><?= (int)($agendaEquipeStats['total_propostas'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="bg-success bg-opacity-10 rounded p-2 border border-success">
                            <div class="text-success small" style="font-size: 0.72rem;">Fechamentos</div>
                            <div class="fs-5 fw-bold text-success"><?= (int)($agendaEquipeStats['total_fechamentos'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center small text-secondary">
                    <div>
                        <i class="bi bi-cash-stack text-success me-1"></i> Total Fechado: <strong class="text-light">R$ <?= number_format((float)($agendaEquipeStats['valor_fechado_total'] ?? 0), 2, ',', '.') ?></strong>
                    </div>
                    <div>
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Acelerador 100 Pts: <strong class="text-light">R$ 500,00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VELOCÍMETRO / TERMÔMETRO VISUAL DO COLABORADOR -->
<div class="card bg-dark border-secondary mb-4 shadow">
    <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar shadow" style="width:32px; height:32px; border-radius:50%; background-color:<?= htmlspecialchars($targetUser['avatar_color'] ?? '#6c757d') ?>; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:bold; color:white;">
                <?= strtoupper(substr($targetUser['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <h5 class="card-title text-light mb-0">Termômetro Individual de Desempenho: <strong><?= htmlspecialchars($targetUser['name']) ?></strong></h5>
                <span class="text-secondary small"><?= htmlspecialchars($targetUser['department']) ?> • Trimestre <?= htmlspecialchars($trimestre) ?></span>
            </div>
        </div>

        <?php if (isAdmin()): ?>
        <!-- Seletor de Colaborador para Gestão -->
        <form method="GET" action="<?= BASE_URL ?>/" class="d-flex align-items-center gap-2">
            <input type="hidden" name="page" value="kpis">
            <input type="hidden" name="trimestre" value="<?= htmlspecialchars($trimestre) ?>">
            <label class="text-secondary small text-nowrap">Ver Colaborador:</label>
            <select name="usuario_id" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                <?php foreach ($ranking as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= ($userIdVisao == $r['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?> (<?= $r['score_total'] ?> pts)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>
    </div>
    <div class="card-body p-4">
        <div class="row align-items-center">
            
            <!-- Velocímetro SVG Interativo -->
            <div class="col-lg-5 text-center mb-4 mb-lg-0">
                <div class="position-relative d-inline-block" style="width: 280px; height: 160px;">
                    <svg viewBox="0 0 200 120" width="280" height="160">
                        <!-- Arco de Fundo Cinza -->
                        <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#2b3035" stroke-width="18" stroke-linecap="round" />
                        
                        <!-- Arco Colorido do Score -->
                        <?php
                        // Cálculo do arco em radianos (180 graus de semi-círculo)
                        $percent = min(1, max(0, $score / 100));
                        $angle = 180 * $percent;
                        $rad = deg2rad(180 - $angle);
                        $x = 100 - 80 * cos($rad);
                        $y = 100 - 80 * sin($rad);
                        $largeArc = ($angle > 180) ? 1 : 0;
                        ?>
                        <path d="M 20 100 A 80 80 0 0 1 <?= $x ?> <?= $y ?>" fill="none" stroke="<?= $scoreColor ?>" stroke-width="18" stroke-linecap="round" />

                        <!-- Centro com Valor do Score -->
                        <text x="100" y="85" text-anchor="middle" font-size="34" font-weight="bold" fill="#f8f9fa"><?= $score ?></text>
                        <text x="100" y="105" text-anchor="middle" font-size="11" fill="#adb5bd">DE 100 PONTOS</text>
                    </svg>
                </div>

                <div class="mt-2">
                    <span class="badge fs-6 px-3 py-2" style="background-color: <?= $scoreColor ?>; color: <?= ($score >= 85) ? '#000' : '#fff' ?>;">
                        <?= $userKpi['status_texto'] ?>
                    </span>
                </div>

                <?php if ($acelerador): ?>
                    <div class="mt-3 p-2 rounded border border-warning bg-warning bg-opacity-10 text-warning fw-bold animate__animated animate__pulse">
                        <i class="bi bi-trophy-fill fs-5 me-1"></i> BÔNUS ACELERADOR DE R$ 500,00 DESTRAVADO!
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detalhamento dos 3 Pilares e Comissão -->
            <div class="col-lg-7">
                <div class="card bg-secondary bg-opacity-10 border-secondary p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-light fw-bold">Elegibilidade da Comissão de Vendas:</span>
                        <span class="fs-4 fw-bold text-success"><?= number_format($comissao, 0) ?>%</span>
                    </div>
                    <div class="progress bg-dark" style="height: 12px;">
                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: <?= $comissao ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-secondary small mt-1" style="font-size: 0.72rem;">
                        <span>&lt;50 pts: 0%</span>
                        <span>50-69 pts: 50%</span>
                        <span>70-84 pts: 80%</span>
                        <span>85-99 pts: 100%</span>
                        <span>100 pts: 100% + R$ 500</span>
                    </div>
                </div>

                <!-- 3 Pilares de Pontuação -->
                <div class="row g-2">
                    <!-- 1. Fechamentos na Agenda -->
                    <div class="col-md-4">
                        <div class="bg-dark border border-secondary rounded p-3 text-center h-100">
                            <span class="text-secondary small d-block">1. Fechamentos (Agenda)</span>
                            <div class="fs-4 fw-bold text-primary my-1"><?= $userKpi['pontos_vendas'] ?> <span class="fs-6 text-secondary">/ 40 pts</span></div>
                            <span class="small text-secondary d-block"><?= $userKpi['total_fechados'] ?> contratos</span>
                            <span class="small text-success" style="font-size: 0.72rem;">R$ <?= number_format((float)($userKpi['valor_fechado_total'] ?? 0), 2, ',', '.') ?></span>
                        </div>
                    </div>

                    <!-- 2. SLA / OTIF -->
                    <div class="col-md-4">
                        <div class="bg-dark border border-secondary rounded p-3 text-center h-100">
                            <span class="text-secondary small d-block">2. SLA & OTIF Prazos</span>
                            <div class="fs-4 fw-bold text-info my-1"><?= $userKpi['pontos_sla_otif'] ?> <span class="fs-6 text-secondary">/ 30 pts</span></div>
                            <span class="small text-success d-block"><?= $userKpi['prorrogacoes_abonadas'] ?> abonadas</span>
                            <span class="small text-secondary" style="font-size: 0.72rem;">Conformidade interna</span>
                        </div>
                    </div>

                    <!-- 3. Agenda Comercial Semanal -->
                    <div class="col-md-4">
                        <div class="bg-dark border border-secondary rounded p-3 text-center h-100">
                            <span class="text-secondary small d-block">3. Agenda Executada</span>
                            <div class="fs-4 fw-bold text-warning my-1"><?= $userKpi['pontos_visitas'] ?> <span class="fs-6 text-secondary">/ 30 pts</span></div>
                            <span class="small text-secondary d-block"><?= (int)($userKpi['total_atividades_agenda'] ?? 0) ?> realizadas</span>
                            <span class="small text-secondary" style="font-size: 0.70rem;">
                                <?= (int)($userKpi['total_ligacoes'] ?? 0) ?> lig • <?= (int)($userKpi['total_abordagens'] ?? 0) ?> abord • <?= (int)($userKpi['total_propostas'] ?? 0) ?> prop
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- RANKING COMERCIAL ABERTO (GAMIFICAÇÃO AO VIVO) -->
<div class="card bg-dark border-secondary shadow">
    <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark"><i class="bi bi-award-fill"></i> Leaderboard</span>
            <h5 class="card-title text-light mb-0">Ranking Comercial Aberto da Equipe</h5>
        </div>
        <span class="badge bg-secondary"><?= count($ranking) ?> colaboradores pontuando</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr class="border-secondary text-secondary small">
                        <th class="text-center" style="width: 70px;">POSIÇÃO</th>
                        <th>COLABORADOR</th>
                        <th>DEPARTAMENTO</th>
                        <th class="text-center">FECHAMENTOS (MÁX 40)</th>
                        <th class="text-center">SLA/OTIF (MÁX 30)</th>
                        <th class="text-center">AGENDA (MÁX 30)</th>
                        <th class="text-center">SCORE TOTAL</th>
                        <th class="text-center">COMISSÃO DEVIDA</th>
                        <th class="text-center">ACELERADOR R$ 500</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $colab): 
                        // Destaque para top 3
                        $posBadge = "<span class='badge bg-secondary'>#{$colab['posicao']}</span>";
                        if ($colab['posicao'] === 1) $posBadge = "<span class='badge bg-warning text-dark fs-6 fw-bold'><i class='bi bi-trophy-fill me-1'></i> 1º</span>";
                        elseif ($colab['posicao'] === 2) $posBadge = "<span class='badge bg-light text-dark fs-6 fw-bold'><i class='bi bi-award-fill me-1'></i> 2º</span>";
                        elseif ($colab['posicao'] === 3) $posBadge = "<span class='badge bg-secondary text-light fs-6 fw-bold'><i class='bi bi-award me-1'></i> 3º</span>";

                        $isMe = ($colab['id'] == $currentUser['id']);
                        $rowClass = $isMe ? 'table-primary bg-opacity-25' : '';
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="text-center"><?= $posBadge ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar shadow-sm" style="width:28px; height:28px; border-radius:50%; background-color:<?= htmlspecialchars($colab['avatar_color'] ?? '#6c757d') ?>; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; color:white;">
                                    <?= strtoupper(substr($colab['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <strong class="text-light"><?= htmlspecialchars($colab['name']) ?></strong>
                                    <?php if ($isMe): ?>
                                        <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Você</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($colab['department']) ?></span></td>
                        <td class="text-center fw-bold text-primary"><?= $colab['pontos_vendas'] ?> pts</td>
                        <td class="text-center fw-bold text-info"><?= $colab['pontos_sla_otif'] ?> pts</td>
                        <td class="text-center fw-bold text-warning"><?= $colab['pontos_visitas'] ?> pts</td>
                        <td class="text-center">
                            <span class="badge fs-6 px-3 py-2 <?= $colab['score_total'] >= 100 ? 'bg-warning text-dark fw-bold' : ($colab['score_total'] >= 85 ? 'bg-success' : ($colab['score_total'] >= 70 ? 'bg-info text-dark' : ($colab['score_total'] >= 50 ? 'bg-warning text-dark' : 'bg-danger'))) ?>">
                                <?= $colab['score_total'] ?> pts
                            </span>
                        </td>
                        <td class="text-center fw-bold text-light"><?= number_format($colab['percentual_comissao_devido'], 0) ?>%</td>
                        <td class="text-center">
                            <?php if ($colab['elegivel_acelerador_500']): ?>
                                <span class="badge bg-warning text-dark fw-bold border border-warning" title="Score 100 atingido!">
                                    <i class="bi bi-check-circle-fill"></i> DESTRAVADO (+R$ 500)
                                </span>
                            <?php else: ?>
                                <span class="text-secondary small">Bloqueado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
