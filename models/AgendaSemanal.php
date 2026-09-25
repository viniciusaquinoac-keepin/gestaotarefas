<?php
require_once __DIR__ . '/../database.php';

class AgendaSemanal {
    public static function getWeekBoundaries($dateStr = null) {
        $time = $dateStr ? strtotime($dateStr) : time();
        
        // Obter a segunda-feira da semana
        $dayOfWeek = (int)date('N', $time); // 1 (Seg) a 7 (Dom)
        $mondayTime = strtotime("-" . ($dayOfWeek - 1) . " days", $time);
        $sundayTime = strtotime("+6 days", $mondayTime);

        return [
            'monday' => date('Y-m-d', $mondayTime),
            'sunday' => date('Y-m-d', $sundayTime),
            'week_number' => (int)date('W', $time),
            'year' => (int)date('Y', $time),
            'current_date' => date('Y-m-d', $time),
            'prev_week' => date('Y-m-d', strtotime('-7 days', $mondayTime)),
            'next_week' => date('Y-m-d', strtotime('+7 days', $mondayTime))
        ];
    }

    public static function getAtividadesSemana($mondayDate, $sundayDate, $vendedorId = null, $empresa = null) {
        $db = Database::getConnection();
        
        $sql = "
            SELECT a.*, u.name as vendedor_nome, u.avatar_color as vendedor_avatar,
                   l.etapa_funil as lead_etapa
            FROM agenda_comercial_semanal a
            LEFT JOIN users u ON a.vendedor_id = u.id
            LEFT JOIN prospeccao_leads l ON a.lead_id = l.id
            WHERE a.data_agendada BETWEEN :monday AND :sunday
        ";
        $params = [
            'monday' => $mondayDate,
            'sunday' => $sundayDate
        ];

        if ($vendedorId) {
            $sql .= " AND a.vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendedorId;
        }

        if ($empresa && in_array($empresa, ['Autoitec', 'Keepin'])) {
            $sql .= " AND a.empresa_alvo = :empresa";
            $params['empresa'] = $empresa;
        }

        $sql .= " ORDER BY a.data_agendada ASC, a.horario_agendado ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por data (Segunda a Domingo)
        $porDia = [];
        $diasSemanaNomes = [
            1 => ['slug' => 'seg', 'label' => 'Segunda-feira', 'sigla' => 'SEG'],
            2 => ['slug' => 'ter', 'label' => 'Terça-feira', 'sigla' => 'TER'],
            3 => ['slug' => 'qua', 'label' => 'Quarta-feira', 'sigla' => 'QUA'],
            4 => ['slug' => 'qui', 'label' => 'Quinta-feira', 'sigla' => 'QUI'],
            5 => ['slug' => 'sex', 'label' => 'Sexta-feira', 'sigla' => 'SEX'],
            6 => ['slug' => 'sab', 'label' => 'Sábado', 'sigla' => 'SÁB'],
            7 => ['slug' => 'dom', 'label' => 'Domingo', 'sigla' => 'DOM']
        ];

        for ($i = 0; $i < 7; $i++) {
            $curDate = date('Y-m-d', strtotime("+{$i} days", strtotime($mondayDate)));
            $dayNum = (int)date('N', strtotime($curDate));
            $porDia[$curDate] = [
                'data' => $curDate,
                'data_formatada' => date('d/m', strtotime($curDate)),
                'dia_semana' => $diasSemanaNomes[$dayNum]['label'],
                'sigla' => $diasSemanaNomes[$dayNum]['sigla'],
                'is_hoje' => ($curDate === date('Y-m-d')),
                'atividades' => []
            ];
        }

        foreach ($atividades as $ativ) {
            $d = $ativ['data_agendada'];
            if (!empty($ativ['data_primeiro_contato'])) {
                $ativ['dias_desde_inicio'] = max(0, round((strtotime($ativ['data_agendada']) - strtotime($ativ['data_primeiro_contato'])) / 86400));
            } else {
                $ativ['dias_desde_inicio'] = 0;
            }
            if (isset($porDia[$d])) {
                $porDia[$d]['atividades'][] = $ativ;
            }
        }

        return $porDia;
    }

    public static function getResumoSemana($mondayDate, $sundayDate, $vendedorId = null, $empresa = null) {
        $db = Database::getConnection();

        $sql = "
            SELECT 
                tipo_atividade,
                status_resultado,
                COUNT(*) as qtd,
                COALESCE(SUM(valor_estimado), 0) as total_valor
            FROM agenda_comercial_semanal
            WHERE data_agendada BETWEEN :monday AND :sunday
        ";
        $params = [
            'monday' => $mondayDate,
            'sunday' => $sundayDate
        ];

        if ($vendedorId) {
            $sql .= " AND vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendedorId;
        }

        if ($empresa && in_array($empresa, ['Autoitec', 'Keepin'])) {
            $sql .= " AND empresa_alvo = :empresa";
            $params['empresa'] = $empresa;
        }

        $sql .= " GROUP BY tipo_atividade, status_resultado";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resumo = [
            'ligacoes' => ['planejadas' => 0, 'realizadas' => 0],
            'abordagens' => ['planejadas' => 0, 'realizadas' => 0],
            'diagnosticos' => ['planejadas' => 0, 'realizadas' => 0],
            'apresentacoes' => ['planejadas' => 0, 'realizadas' => 0],
            'propostas' => ['planejadas' => 0, 'realizadas' => 0, 'valor' => 0],
            'fechamentos' => ['planejadas' => 0, 'realizadas' => 0, 'valor' => 0],
            'total_atividades' => 0,
            'total_realizadas' => 0,
            'taxa_conversao' => 0,
            'valor_fechado_semana' => 0,
            // Métricas de Tempo Médio de Ciclo de Venda
            'tempo_medio_fechamento' => null,
            'min_ciclo_fechamento' => null,
            'max_ciclo_fechamento' => null,
            'total_fechamentos_ciclo' => 0
        ];

        foreach ($rows as $r) {
            $tipo = strtolower(trim($r['tipo_atividade']));
            $status = $r['status_resultado'];
            $qtd = (int)$r['qtd'];
            $valor = (float)$r['total_valor'];

            $resumo['total_atividades'] += $qtd;
            if ($status === 'Realizado') {
                $resumo['total_realizadas'] += $qtd;
            }

            if (strpos($tipo, 'liga') !== false) {
                $resumo['ligacoes']['planejadas'] += $qtd;
                if ($status === 'Realizado') $resumo['ligacoes']['realizadas'] += $qtd;
            } elseif (strpos($tipo, 'abord') !== false) {
                $resumo['abordagens']['planejadas'] += $qtd;
                if ($status === 'Realizado') $resumo['abordagens']['realizadas'] += $qtd;
            } elseif (strpos($tipo, 'diag') !== false || strpos($tipo, 'spin') !== false) {
                $resumo['diagnosticos']['planejadas'] += $qtd;
                if ($status === 'Realizado') $resumo['diagnosticos']['realizadas'] += $qtd;
            } elseif (strpos($tipo, 'apres') !== false) {
                $resumo['apresentacoes']['planejadas'] += $qtd;
                if ($status === 'Realizado') $resumo['apresentacoes']['realizadas'] += $qtd;
            } elseif (strpos($tipo, 'prop') !== false) {
                $resumo['propostas']['planejadas'] += $qtd;
                if ($status === 'Realizado') {
                    $resumo['propostas']['realizadas'] += $qtd;
                    $resumo['propostas']['valor'] += $valor;
                }
            } elseif (strpos($tipo, 'fech') !== false) {
                $resumo['fechamentos']['planejadas'] += $qtd;
                if ($status === 'Realizado') {
                    $resumo['fechamentos']['realizadas'] += $qtd;
                    $resumo['fechamentos']['valor'] += $valor;
                    $resumo['valor_fechado_semana'] += $valor;
                }
            }
        }

        // Taxa de conversão: Fechamentos Realizados / Abordagens Realizadas (ou Propostas Realizadas)
        if ($resumo['abordagens']['realizadas'] > 0) {
            $resumo['taxa_conversao'] = round(($resumo['fechamentos']['realizadas'] / $resumo['abordagens']['realizadas']) * 100, 1);
        } elseif ($resumo['propostas']['realizadas'] > 0) {
            $resumo['taxa_conversao'] = round(($resumo['fechamentos']['realizadas'] / $resumo['propostas']['realizadas']) * 100, 1);
        }

        // Cálculo do Tempo Médio de Ciclo de Venda (Lead Time do 1º Contato ao Fechamento)
        $sqlCiclo = "
            SELECT 
                AVG(JULIANDAY(data_agendada) - JULIANDAY(data_primeiro_contato)) as media_ciclo_fechamento,
                MIN(JULIANDAY(data_agendada) - JULIANDAY(data_primeiro_contato)) as min_ciclo_fechamento,
                MAX(JULIANDAY(data_agendada) - JULIANDAY(data_primeiro_contato)) as max_ciclo_fechamento,
                COUNT(*) as total_fechamentos_ciclo
            FROM agenda_comercial_semanal
            WHERE tipo_atividade = 'Fechamento'
              AND status_resultado = 'Realizado'
              AND data_primeiro_contato IS NOT NULL
        ";
        $paramsCiclo = [];
        if ($vendedorId) {
            $sqlCiclo .= " AND vendedor_id = :vendedor_id";
            $paramsCiclo['vendedor_id'] = $vendedorId;
        }
        if ($empresa && in_array($empresa, ['Autoitec', 'Keepin'])) {
            $sqlCiclo .= " AND empresa_alvo = :empresa";
            $paramsCiclo['empresa'] = $empresa;
        }
        $stmtCiclo = $db->prepare($sqlCiclo);
        $stmtCiclo->execute($paramsCiclo);
        $cicloRow = $stmtCiclo->fetch(PDO::FETCH_ASSOC);

        if ($cicloRow && $cicloRow['media_ciclo_fechamento'] !== null) {
            $resumo['tempo_medio_fechamento'] = round((float)$cicloRow['media_ciclo_fechamento'], 1);
            $resumo['min_ciclo_fechamento'] = round((float)$cicloRow['min_ciclo_fechamento'], 1);
            $resumo['max_ciclo_fechamento'] = round((float)$cicloRow['max_ciclo_fechamento'], 1);
            $resumo['total_fechamentos_ciclo'] = (int)$cicloRow['total_fechamentos_ciclo'];
        }

        return $resumo;
    }

    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM agenda_comercial_semanal WHERE id = :id");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = Database::getConnection();

        $atividadeAnteriorId = !empty($data['atividade_anterior_id']) ? (int)$data['atividade_anterior_id'] : null;
        $cicloOrigemId = !empty($data['ciclo_origem_id']) ? (int)$data['ciclo_origem_id'] : null;
        $dataPrimeiroContato = !empty($data['data_primeiro_contato']) ? $data['data_primeiro_contato'] : null;
        $passoSequencia = !empty($data['passo_sequencia']) ? (int)$data['passo_sequencia'] : 1;
        $leadId = !empty($data['lead_id']) ? (int)$data['lead_id'] : null;

        // Se veio de uma atividade anterior, herda dados do ciclo
        if ($atividadeAnteriorId) {
            $ant = self::getById($atividadeAnteriorId);
            if ($ant) {
                $cicloOrigemId = !empty($ant['ciclo_origem_id']) ? (int)$ant['ciclo_origem_id'] : (int)$ant['id'];
                $dataPrimeiroContato = !empty($ant['data_primeiro_contato']) ? $ant['data_primeiro_contato'] : $ant['data_agendada'];
                $passoSequencia = (int)($ant['passo_sequencia'] ?? 1) + 1;
                if (!$leadId && !empty($ant['lead_id'])) {
                    $leadId = (int)$ant['lead_id'];
                }
            }
        } elseif ($leadId) {
            // Se tem lead mas não tem anterior_id, verifica histórico existente
            $stmtHist = $db->prepare("
                SELECT id, ciclo_origem_id, data_agendada, data_primeiro_contato, passo_sequencia 
                FROM agenda_comercial_semanal 
                WHERE lead_id = :lead_id 
                ORDER BY data_agendada DESC, id DESC 
                LIMIT 1
            ");
            $stmtHist->execute(['lead_id' => $leadId]);
            $ultimo = $stmtHist->fetch(PDO::FETCH_ASSOC);
            if ($ultimo) {
                $cicloOrigemId = !empty($ultimo['ciclo_origem_id']) ? (int)$ultimo['ciclo_origem_id'] : (int)$ultimo['id'];
                $dataPrimeiroContato = !empty($ultimo['data_primeiro_contato']) ? $ultimo['data_primeiro_contato'] : $ultimo['data_agendada'];
                $passoSequencia = (int)($ultimo['passo_sequencia'] ?? 1) + 1;
                $atividadeAnteriorId = (int)$ultimo['id'];
            } else {
                $dataPrimeiroContato = $data['data_agendada'];
                $passoSequencia = 1;
            }
        } else {
            $dataPrimeiroContato = $data['data_agendada'];
            $passoSequencia = 1;
        }

        $stmt = $db->prepare("
            INSERT INTO agenda_comercial_semanal (
                vendedor_id, lead_id, atividade_anterior_id, ciclo_origem_id,
                data_primeiro_contato, passo_sequencia,
                cliente_nome, contato_nome, contato_telefone,
                tipo_atividade, data_agendada, horario_agendado, status_resultado,
                resultado_obs, valor_estimado, empresa_alvo
            ) VALUES (
                :vendedor_id, :lead_id, :atividade_anterior_id, :ciclo_origem_id,
                :data_primeiro_contato, :passo_sequencia,
                :cliente_nome, :contato_nome, :contato_telefone,
                :tipo_atividade, :data_agendada, :horario_agendado, :status_resultado,
                :resultado_obs, :valor_estimado, :empresa_alvo
            )
        ");
        $stmt->execute([
            'vendedor_id' => $data['vendedor_id'],
            'lead_id' => $leadId,
            'atividade_anterior_id' => $atividadeAnteriorId,
            'ciclo_origem_id' => $cicloOrigemId,
            'data_primeiro_contato' => $dataPrimeiroContato,
            'passo_sequencia' => $passoSequencia,
            'cliente_nome' => $data['cliente_nome'],
            'contato_nome' => $data['contato_nome'] ?? '',
            'contato_telefone' => $data['contato_telefone'] ?? '',
            'tipo_atividade' => $data['tipo_atividade'],
            'data_agendada' => $data['data_agendada'],
            'horario_agendado' => $data['horario_agendado'] ?? '09:00',
            'status_resultado' => $data['status_resultado'] ?? 'Planejado',
            'resultado_obs' => $data['resultado_obs'] ?? '',
            'valor_estimado' => (float)($data['valor_estimado'] ?? 0),
            'empresa_alvo' => $data['empresa_alvo'] ?? 'Autoitec'
        ]);

        $newId = (int)$db->lastInsertId();

        // Se foi o início de um novo ciclo, atualiza ciclo_origem_id com seu próprio id
        if (!$cicloOrigemId) {
            $db->prepare("UPDATE agenda_comercial_semanal SET ciclo_origem_id = :id WHERE id = :id")->execute(['id' => $newId]);
        }

        return $newId;
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE agenda_comercial_semanal SET
                cliente_nome = :cliente_nome,
                contato_nome = :contato_nome,
                contato_telefone = :contato_telefone,
                tipo_atividade = :tipo_atividade,
                data_agendada = :data_agendada,
                horario_agendado = :horario_agendado,
                status_resultado = :status_resultado,
                resultado_obs = :resultado_obs,
                valor_estimado = :valor_estimado,
                empresa_alvo = :empresa_alvo,
                data_atualizacao = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'cliente_nome' => $data['cliente_nome'],
            'contato_nome' => $data['contato_nome'] ?? '',
            'contato_telefone' => $data['contato_telefone'] ?? '',
            'tipo_atividade' => $data['tipo_atividade'],
            'data_agendada' => $data['data_agendada'],
            'horario_agendado' => $data['horario_agendado'] ?? '09:00',
            'status_resultado' => $data['status_resultado'] ?? 'Planejado',
            'resultado_obs' => $data['resultado_obs'] ?? '',
            'valor_estimado' => (float)($data['valor_estimado'] ?? 0),
            'empresa_alvo' => $data['empresa_alvo'] ?? 'Autoitec',
            'id' => $id
        ]);
    }

    public static function updateStatus($id, $status, $obs = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE agenda_comercial_semanal SET
                status_resultado = :status,
                resultado_obs = COALESCE(:obs, resultado_obs),
                data_atualizacao = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'obs' => $obs,
            'id' => $id
        ]);
    }

    public static function reagendar($id, $novaData, $novoHorario, $motivo = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE agenda_comercial_semanal SET
                data_agendada = :nova_data,
                horario_agendado = :novo_horario,
                status_resultado = 'Reagendado',
                resultado_obs = CASE WHEN :motivo IS NOT NULL THEN :motivo ELSE resultado_obs END,
                data_atualizacao = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'nova_data' => $novaData,
            'novo_horario' => $novoHorario,
            'motivo' => $motivo,
            'id' => $id
        ]);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM agenda_comercial_semanal WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getByLeadId($leadId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM agenda_comercial_semanal
            WHERE lead_id = :lead_id
            ORDER BY data_agendada DESC, horario_agendado DESC
        ");
        $stmt->execute(['lead_id' => $leadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCadeiaCicloVida($atividadeId) {
        $db = Database::getConnection();
        $ativ = self::getById($atividadeId);
        if (!$ativ) return [];

        $cicloOrigemId = !empty($ativ['ciclo_origem_id']) ? $ativ['ciclo_origem_id'] : $ativ['id'];
        $leadId = $ativ['lead_id'];
        $clienteNome = trim($ativ['cliente_nome']);

        $sql = "
            SELECT a.*, u.name as vendedor_nome, u.avatar_color as vendedor_avatar
            FROM agenda_comercial_semanal a
            LEFT JOIN users u ON a.vendedor_id = u.id
            WHERE (a.ciclo_origem_id = :origem_id OR a.id = :origem_id)
        ";
        $params = ['origem_id' => $cicloOrigemId];

        if ($leadId) {
            $sql .= " OR a.lead_id = :lead_id";
            $params['lead_id'] = $leadId;
        } elseif (!empty($clienteNome)) {
            $sql .= " OR LOWER(TRIM(a.cliente_nome)) = :cliente_nome";
            $params['cliente_nome'] = strtolower($clienteNome);
        }

        $sql .= " ORDER BY a.data_agendada ASC, a.horario_agendado ASC, a.id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Remover duplicatas caso a query traga mesma id por condições OR
        $vistos = [];
        $lista = [];
        foreach ($raw as $r) {
            if (!isset($vistos[$r['id']])) {
                $vistos[$r['id']] = true;
                $lista[] = $r;
            }
        }

        $cadeia = [];
        $dataAnterior = null;
        $primeiraData = null;

        foreach ($lista as $idx => $item) {
            if ($idx === 0) {
                $primeiraData = $item['data_agendada'];
                $diasDesdeAnterior = 0;
                $diasDesdeInicio = 0;
            } else {
                $diasDesdeAnterior = max(0, round((strtotime($item['data_agendada']) - strtotime($dataAnterior)) / 86400));
                $diasDesdeInicio = max(0, round((strtotime($item['data_agendada']) - strtotime($primeiraData)) / 86400));
            }
            $dataAnterior = $item['data_agendada'];

            $item['dias_desde_anterior'] = $diasDesdeAnterior;
            $item['dias_desde_inicio'] = $diasDesdeInicio;
            $cadeia[] = $item;
        }

        return $cadeia;
    }
}
