<?php
require_once __DIR__ . '/../database.php';

class KpiTrimestral {
    public static function getCurrentQuarter($dateStr = null) {
        $time = $dateStr ? strtotime($dateStr) : time();
        $date = date('Y-m-d', $time);
        
        // Vigência inicial: a partir de 24/09/2026 contabiliza oficialmente para 2026-Q4
        if ($date >= '2026-09-24' && $date <= '2026-12-31') {
            return '2026-Q4';
        }
        
        $year = date('Y', $time);
        $month = (int)date('m', $time);
        $q = ceil($month / 3);
        return "{$year}-Q{$q}";
    }

    public static function getQuarterDateRange($quarter) {
        $parts = explode('-Q', $quarter);
        $year = isset($parts[0]) ? (int)$parts[0] : (int)date('Y');
        $q = isset($parts[1]) ? (int)$parts[1] : 4;

        if ($quarter === '2026-Q4') {
            return [
                'start' => '2026-09-24 00:00:00',
                'end' => '2026-12-31 23:59:59',
                'start_date' => '2026-09-24',
                'end_date' => '2026-12-31'
            ];
        }

        switch ($q) {
            case 1:
                $start = "{$year}-01-01";
                $end = "{$year}-03-31";
                break;
            case 2:
                $start = "{$year}-04-01";
                $end = "{$year}-06-30";
                break;
            case 3:
                $start = "{$year}-07-01";
                $end = "{$year}-09-30";
                break;
            case 4:
            default:
                $start = "{$year}-10-01";
                $end = "{$year}-12-31";
                break;
        }

        return [
            'start' => "{$start} 00:00:00",
            'end' => "{$end} 23:59:59",
            'start_date' => $start,
            'end_date' => $end
        ];
    }

    public static function resolvePeriodDateRange($tipoPeriodo = 'trimestral', $valorPeriodo = null) {
        if ($tipoPeriodo === 'semanal') {
            if (!$valorPeriodo) {
                $valorPeriodo = date('Y-\WW'); // ex: 2026-W39
            }
            $dto = new DateTime();
            if (preg_match('/^(\d{4})-W(\d{2})$/', $valorPeriodo, $m)) {
                $year = (int)$m[1];
                $week = (int)$m[2];
                $dto->setISODate($year, $week, 1); // 1 = Segunda-feira
                $startDate = $dto->format('Y-m-d');
                $dto->modify('+6 days'); // Domingo
                $endDate = $dto->format('Y-m-d');
            } else {
                $dto->modify('monday this week');
                $startDate = $dto->format('Y-m-d');
                $dto->modify('+6 days');
                $endDate = $dto->format('Y-m-d');
                $valorPeriodo = date('Y-\WW');
            }

            return [
                'tipo' => 'semanal',
                'chave' => $valorPeriodo,
                'label' => 'Semana ' . substr($valorPeriodo, 6) . ' (' . date('d/m', strtotime($startDate)) . ' a ' . date('d/m/Y', strtotime($endDate)) . ')',
                'start' => "{$startDate} 00:00:00",
                'end' => "{$endDate} 23:59:59",
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
        } elseif ($tipoPeriodo === 'mensal') {
            if (!$valorPeriodo) {
                $valorPeriodo = date('Y-m'); // ex: 2026-09
            }
            if (preg_match('/^(\d{4})-(\d{2})$/', $valorPeriodo, $m)) {
                $year = (int)$m[1];
                $month = (int)$m[2];
                $startDate = date("Y-m-01", strtotime("{$year}-{$month}-01"));
                $endDate = date("Y-m-t", strtotime("{$year}-{$month}-01"));
            } else {
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                $valorPeriodo = date('Y-m');
            }

            $mesesNomes = [
                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
            ];
            $mesNum = substr($valorPeriodo, 5, 2);
            $mesNome = $mesesNomes[$mesNum] ?? $mesNum;

            return [
                'tipo' => 'mensal',
                'chave' => $valorPeriodo,
                'label' => "{$mesNome} / " . substr($valorPeriodo, 0, 4),
                'start' => "{$startDate} 00:00:00",
                'end' => "{$endDate} 23:59:59",
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
        } else {
            // Trimestral (default)
            if (!$valorPeriodo) {
                $valorPeriodo = self::getCurrentQuarter();
            }
            $range = self::getQuarterDateRange($valorPeriodo);
            return [
                'tipo' => 'trimestral',
                'chave' => $valorPeriodo,
                'label' => "Trimestre {$valorPeriodo}",
                'start' => $range['start'],
                'end' => $range['end'],
                'start_date' => $range['start_date'],
                'end_date' => $range['end_date']
            ];
        }
    }

    public static function getPeriodosDisponiveis($tipoPeriodo = 'trimestral') {
        if ($tipoPeriodo === 'semanal') {
            $semanas = [];
            $dto = new DateTime();
            $dto->modify('monday this week');
            for ($i = 0; $i < 8; $i++) {
                $wKey = $dto->format('Y-\WW');
                $startFormatted = $dto->format('d/m');
                $dtoSunday = clone $dto;
                $dtoSunday->modify('+6 days');
                $endFormatted = $dtoSunday->format('d/m/Y');
                $isCurrent = ($i === 0) ? ' (Semana Atual)' : '';
                $semanas[$wKey] = "Semana " . $dto->format('W') . " ({$startFormatted} a {$endFormatted}){$isCurrent}";
                $dto->modify('-7 days');
            }
            return $semanas;
        } elseif ($tipoPeriodo === 'mensal') {
            $meses = [];
            $mesesNomes = [
                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
            ];
            $dto = new DateTime('first day of this month');
            for ($i = 0; $i < 8; $i++) {
                $mKey = $dto->format('Y-m');
                $mNum = $dto->format('m');
                $nomeMes = $mesesNomes[$mNum] ?? $mNum;
                $ano = $dto->format('Y');
                $isCurrent = ($i === 0) ? ' (Mês Atual)' : '';
                $meses[$mKey] = "{$nomeMes}/{$ano}{$isCurrent}";
                $dto->modify('-1 month');
            }
            return $meses;
        } else {
            return [
                '2026-Q4' => '2026 - Q4 (Out/Nov/Dez - Ciclo Ativo)',
                '2026-Q3' => '2026 - Q3 (Jul/Ago/Set)',
                '2026-Q2' => '2026 - Q2 (Abr/Mai/Jun)',
                '2026-Q1' => '2026 - Q1 (Jan/Fev/Mar)'
            ];
        }
    }

    public static function calculateUserKpi($userId, $periodoParam = null) {
        $db = Database::getConnection();
        
        if (is_array($periodoParam) && isset($periodoParam['start_date']) && isset($periodoParam['end_date'])) {
            $range = $periodoParam;
        } elseif (is_string($periodoParam)) {
            if (preg_match('/^\d{4}-W\d{2}$/', $periodoParam)) {
                $range = self::resolvePeriodDateRange('semanal', $periodoParam);
            } elseif (preg_match('/^\d{4}-\d{2}$/', $periodoParam)) {
                $range = self::resolvePeriodDateRange('mensal', $periodoParam);
            } else {
                $range = self::resolvePeriodDateRange('trimestral', $periodoParam);
            }
        } else {
            $range = self::resolvePeriodDateRange('trimestral');
        }
        $quarter = $range['chave'];

        // 1. PONTUAÇÃO DE VENDAS / FECHAMENTOS NA AGENDA (Máx 40 pts)
        // Busca na tabela agenda_comercial_semanal os fechamentos realizados no trimestre
        $stmtVendas = $db->prepare("
            SELECT COUNT(*) as total_fechados, COALESCE(SUM(valor_estimado), 0) as valor_fechado
            FROM agenda_comercial_semanal
            WHERE vendedor_id = :user_id
              AND tipo_atividade = 'Fechamento'
              AND status_resultado = 'Realizado'
              AND data_agendada BETWEEN :start_date AND :end_date
        ");
        $stmtVendas->execute([
            'user_id' => $userId,
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date']
        ]);
        $vendasData = $stmtVendas->fetch(PDO::FETCH_ASSOC);
        $totalFechados = (int)($vendasData['total_fechados'] ?? 0);
        $valorFechado = (float)($vendasData['valor_fechado'] ?? 0);
        
        // 10 pts por fechamento realizado na agenda + bônus de volume (teto 40 pts)
        $pontosVendas = min(40, ($totalFechados * 10) + (int)floor($valorFechado / 10000));

        // 2. PONTUAÇÃO DE ATIVIDADES CONSOLIDADAS DA AGENDA COMERCIAL (Máx 30 pts)
        // Busca a execução das atividades planejadas e realizadas na agenda (Ligações, Abordagens, Apresentações e Propostas)
        $stmtAgenda = $db->prepare("
            SELECT 
                tipo_atividade,
                COUNT(*) as qtd,
                COALESCE(SUM(valor_estimado), 0) as total_valor
            FROM agenda_comercial_semanal
            WHERE vendedor_id = :user_id
              AND status_resultado = 'Realizado'
              AND data_agendada BETWEEN :start_date AND :end_date
            GROUP BY tipo_atividade
        ");
        $stmtAgenda->execute([
            'user_id' => $userId,
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date']
        ]);
        $agendaRows = $stmtAgenda->fetchAll(PDO::FETCH_ASSOC);

        $totalLigacoes = 0;
        $totalAbordagens = 0;
        $totalDiagnosticos = 0;
        $totalApresentacoes = 0;
        $totalPropostas = 0;
        $totalAtividadesAgenda = 0;

        foreach ($agendaRows as $row) {
            $t = $row['tipo_atividade'];
            $q = (int)$row['qtd'];
            $totalAtividadesAgenda += $q;

            if (stripos($t, 'liga') !== false) {
                $totalLigacoes += $q;
            } elseif (stripos($t, 'abord') !== false) {
                $totalAbordagens += $q;
            } elseif (stripos($t, 'diag') !== false || stripos($t, 'spin') !== false) {
                $totalDiagnosticos += $q;
            } elseif (stripos($t, 'apres') !== false) {
                $totalApresentacoes += $q;
            } elseif (stripos($t, 'prop') !== false) {
                $totalPropostas += $q;
            }
        }

        // Pontuação de Execução da Agenda (Prudential):
        // Abordagens presenciais, Diagnósticos e Apresentações = 2 pts cada
        // Ligações e Propostas enviadas = 1 pt cada
        $pontosVisitas = min(30, ($totalAbordagens * 2) + ($totalApresentacoes * 2) + ($totalDiagnosticos * 2) + ($totalLigacoes * 1) + ($totalPropostas * 1));

        // 3. PONTUAÇÃO DE SLA / OTIF (Máx 30 pts)
        // Avalia tarefas atribuídas ao usuário com vencimento no trimestre
        $stmtTasks = $db->prepare("
            SELECT t.id, t.due_date, t.original_due_date, t.status, t.completed_at
            FROM tasks t
            WHERE t.assigned_to = :user_id
              AND t.created_at BETWEEN :start AND :end
        ");
        $stmtTasks->execute([
            'user_id' => $userId,
            'start' => $range['start'],
            'end' => $range['end']
        ]);
        $tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

        $totalTasks = count($tasks);
        if ($totalTasks === 0) {
            // Se não tem tarefas atribuídas, não penaliza o SLA (concede pontuação padrão para não zerar quem foca em campo)
            $pontosSla = 30;
            $slaPercent = 100;
            $totalProrrogacoesNaoAbonadas = 0;
            $totalProrrogacoesAbonadas = 0;
        } else {
            // Verificar prorrogações da tarefa
            $stmtP = $db->prepare("
                SELECT abono_penalidade, COUNT(*) as qtd
                FROM prorrogacoes_tarefas
                WHERE usuario_id = :user_id
                  AND data_solicitacao BETWEEN :start AND :end
                GROUP BY abono_penalidade
            ");
            $stmtP->execute([
                'user_id' => $userId,
                'start' => $range['start'],
                'end' => $range['end']
            ]);
            $pRows = $stmtP->fetchAll(PDO::FETCH_ASSOC);
            
            $totalProrrogacoesAbonadas = 0;
            $totalProrrogacoesNaoAbonadas = 0;
            foreach ($pRows as $pr) {
                if ($pr['abono_penalidade'] == 1) {
                    $totalProrrogacoesAbonadas += (int)$pr['qtd'];
                } else {
                    $totalProrrogacoesNaoAbonadas += (int)$pr['qtd'];
                }
            }

            // Cada prorrogação não-abonada desconta 5 pts do SLA
            $pontosSla = max(0, 30 - ($totalProrrogacoesNaoAbonadas * 5));
            $slaPercent = round(($pontosSla / 30) * 100);
        }

        // SCORE TOTAL (0 a 100)
        $scoreTotal = min(100, $pontosVendas + $pontosVisitas + $pontosSla);

        // PERCENTUAL DE COMISSÃO DEVIDO
        if ($scoreTotal >= 100) {
            $comissaoPercent = 100.00;
            $statusTexto = "Superou Meta (Acelerador Ativado)";
            $acelerador500 = 1;
        } elseif ($scoreTotal >= 85) {
            $comissaoPercent = 100.00;
            $statusTexto = "Meta Atingida (Elegível Integral)";
            $acelerador500 = 0;
        } elseif ($scoreTotal >= 70) {
            $comissaoPercent = 80.00;
            $statusTexto = "Desempenho Parcial (-20% Penalizado)";
            $acelerador500 = 0;
        } elseif ($scoreTotal >= 50) {
            $comissaoPercent = 50.00;
            $statusTexto = "Abaixo do Esperado (-50% Penalizado)";
            $acelerador500 = 0;
        } else {
            $comissaoPercent = 0.00;
            $statusTexto = "Sem Elegibilidade no Trimestre";
            $acelerador500 = 0;
        }

        // Salvar/Atualizar consolidação no banco dinamicamente
        $stmtCheck = $db->prepare("SELECT id FROM kpis_trimestrais_usuario WHERE usuario_id = :uid AND trimestre_ano = :tri");
        $stmtCheck->execute(['uid' => $userId, 'tri' => $quarter]);
        $existingId = $stmtCheck->fetchColumn();

        if ($existingId) {
            $stmtUp = $db->prepare("
                UPDATE kpis_trimestrais_usuario SET
                    pontos_vendas = :pv,
                    pontos_sla_otif = :psla,
                    pontos_visitas = :pvis,
                    score_total = :score,
                    percentual_comissao_devido = :comissao,
                    elegivel_acelerador_500 = :acel,
                    data_atualizacao = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmtUp->execute([
                'pv' => $pontosVendas,
                'psla' => $pontosSla,
                'pvis' => $pontosVisitas,
                'score' => $scoreTotal,
                'comissao' => $comissaoPercent,
                'acel' => $acelerador500,
                'id' => $existingId
            ]);
        } else {
            $stmtIn = $db->prepare("
                INSERT INTO kpis_trimestrais_usuario (
                    usuario_id, trimestre_ano, pontos_vendas, pontos_sla_otif, pontos_visitas,
                    score_total, percentual_comissao_devido, elegivel_acelerador_500
                ) VALUES (
                    :uid, :tri, :pv, :psla, :pvis, :score, :comissao, :acel
                )
            ");
            $stmtIn->execute([
                'uid' => $userId,
                'tri' => $quarter,
                'pv' => $pontosVendas,
                'psla' => $pontosSla,
                'pvis' => $pontosVisitas,
                'score' => $scoreTotal,
                'comissao' => $comissaoPercent,
                'acel' => $acelerador500
            ]);
        }

        return [
            'usuario_id' => $userId,
            'trimestre_ano' => $quarter,
            'pontos_vendas' => $pontosVendas,
            'pontos_sla_otif' => $pontosSla,
            'pontos_visitas' => $pontosVisitas,
            'score_total' => $scoreTotal,
            'percentual_comissao_devido' => $comissaoPercent,
            'elegivel_acelerador_500' => $acelerador500,
            'status_texto' => $statusTexto,
            'total_fechados' => $totalFechados,
            'valor_fechado' => $valorFechado,
            'total_visitas' => $totalAbordagens,
            'total_atividades_agenda' => $totalAtividadesAgenda,
            'total_ligacoes' => $totalLigacoes,
            'total_abordagens' => $totalAbordagens,
            'total_diagnosticos' => $totalDiagnosticos,
            'total_apresentacoes' => $totalApresentacoes,
            'total_propostas' => $totalPropostas,
            'total_tasks' => $totalTasks,
            'prorrogacoes_abonadas' => $totalProrrogacoesAbonadas ?? 0,
            'prorrogacoes_penalizadas' => $totalProrrogacoesNaoAbonadas ?? 0
        ];
    }

    public static function getRanking($periodoParam = null) {
        $db = Database::getConnection();

        // Buscar todos os usuários ativos
        $users = $db->query("SELECT id, name, email, department, avatar_color FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $ranking = [];
        foreach ($users as $u) {
            $kpi = self::calculateUserKpi($u['id'], $periodoParam);
            $ranking[] = array_merge($u, $kpi);
        }

        // Ordenar do maior score_total para o menor
        usort($ranking, function ($a, $b) {
            if ($b['score_total'] === $a['score_total']) {
                if ($b['pontos_vendas'] === $a['pontos_vendas']) {
                    return $b['pontos_visitas'] <=> $a['pontos_visitas'];
                }
                return $b['pontos_vendas'] <=> $a['pontos_vendas'];
            }
            return $b['score_total'] <=> $a['score_total'];
        });

        // Adicionar posição no ranking (1º, 2º, 3º...)
        $pos = 1;
        foreach ($ranking as &$item) {
            $item['posicao'] = $pos++;
        }

        return $ranking;
    }
}
