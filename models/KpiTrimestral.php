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

    public static function calculateUserKpi($userId, $quarter = null) {
        $db = Database::getConnection();
        if (!$quarter) {
            $quarter = self::getCurrentQuarter();
        }
        $range = self::getQuarterDateRange($quarter);

        // 1. PONTUAÇÃO DE VENDAS / FECHAMENTOS (Máx 40 pts)
        // Cada lead fechado na prospecção vale 10 pts (ou proporcional ao valor)
        $stmtVendas = $db->prepare("
            SELECT COUNT(*) as total_fechados, COALESCE(SUM(valor_estimado), 0) as valor_fechado
            FROM prospeccao_leads
            WHERE vendedor_id = :user_id
              AND etapa_funil = 'Fechado'
              AND data_atualizacao BETWEEN :start AND :end
        ");
        $stmtVendas->execute([
            'user_id' => $userId,
            'start' => $range['start'],
            'end' => $range['end']
        ]);
        $vendasData = $stmtVendas->fetch(PDO::FETCH_ASSOC);
        $totalFechados = (int)($vendasData['total_fechados'] ?? 0);
        $valorFechado = (float)($vendasData['valor_fechado'] ?? 0);
        
        // 10 pts por lead fechado, com bônus de valor (teto 40 pts)
        $pontosVendas = min(40, ($totalFechados * 10) + (int)floor($valorFechado / 10000));

        // 2. PONTUAÇÃO DE VISITAS PRESENCIAIS DE CAMPO (Máx 30 pts)
        // 2 pts por visita presencial registrada (meta de 15 visitas = 30 pts)
        $stmtVisitas = $db->prepare("
            SELECT COUNT(*) FROM visitas_campo_keepin
            WHERE vendedor_id = :user_id
              AND data_visita BETWEEN :start AND :end
        ");
        $stmtVisitas->execute([
            'user_id' => $userId,
            'start' => $range['start'],
            'end' => $range['end']
        ]);
        $totalVisitas = (int)$stmtVisitas->fetchColumn();
        $pontosVisitas = min(30, $totalVisitas * 2);

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
            $statusTexto = "🏆 Superou Meta (Acelerador Ativado)";
            $acelerador500 = 1;
        } elseif ($scoreTotal >= 85) {
            $comissaoPercent = 100.00;
            $statusTexto = "✅ Meta Atingida (Elegível Integral)";
            $acelerador500 = 0;
        } elseif ($scoreTotal >= 70) {
            $comissaoPercent = 80.00;
            $statusTexto = "⚠️ Desempenho Parcial (-20% Penalizado)";
            $acelerador500 = 0;
        } elseif ($scoreTotal >= 50) {
            $comissaoPercent = 50.00;
            $statusTexto = "🔴 Abaixo do Esperado (-50% Penalizado)";
            $acelerador500 = 0;
        } else {
            $comissaoPercent = 0.00;
            $statusTexto = "❌ Sem Elegibilidade no Trimestre";
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
            'total_visitas' => $totalVisitas,
            'total_tasks' => $totalTasks,
            'prorrogacoes_abonadas' => $totalProrrogacoesAbonadas ?? 0,
            'prorrogacoes_penalizadas' => $totalProrrogacoesNaoAbonadas ?? 0
        ];
    }

    public static function getRanking($quarter = null) {
        $db = Database::getConnection();
        if (!$quarter) {
            $quarter = self::getCurrentQuarter();
        }

        // Buscar todos os usuários ativos
        $users = $db->query("SELECT id, name, email, department, avatar_color FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $ranking = [];
        foreach ($users as $u) {
            $kpi = self::calculateUserKpi($u['id'], $quarter);
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
