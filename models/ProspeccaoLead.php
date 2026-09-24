<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/Task.php';

class ProspeccaoLead {
    public static function getAll($empresa = null, $vendedorId = null) {
        $db = Database::getConnection();
        $sql = "
            SELECT l.*, u.name as vendedor_nome, u.avatar_color as vendedor_avatar,
                   (SELECT a.data_agendada || ' às ' || a.horario_agendado FROM agenda_comercial_semanal a WHERE a.lead_id = l.id AND a.status_resultado = 'Planejado' AND a.data_agendada >= CURRENT_DATE ORDER BY a.data_agendada ASC, a.horario_agendado ASC LIMIT 1) as prox_agendamento,
                   (SELECT a.tipo_atividade FROM agenda_comercial_semanal a WHERE a.lead_id = l.id AND a.status_resultado = 'Planejado' AND a.data_agendada >= CURRENT_DATE ORDER BY a.data_agendada ASC, a.horario_agendado ASC LIMIT 1) as prox_agendamento_tipo
            FROM prospeccao_leads l
            LEFT JOIN users u ON l.vendedor_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($empresa && in_array($empresa, ['Autoitec', 'Keepin'])) {
            $sql .= " AND l.empresa_alvo = :empresa";
            $params['empresa'] = $empresa;
        }
        if ($vendedorId) {
            $sql .= " AND l.vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendedorId;
        }
        $sql .= " ORDER BY l.data_atualizacao DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT l.*, u.name as vendedor_nome
            FROM prospeccao_leads l
            LEFT JOIN users u ON l.vendedor_id = u.id
            WHERE l.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO prospeccao_leads (
                empresa_alvo, nome_cliente_fantasia, contato_nome, contato_telefone, contato_email,
                etapa_funil, valor_estimado, spin_situacao, spin_problema, spin_implicacao, spin_necessidade,
                meddpicc_score, meddpicc_data, vendedor_id
            ) VALUES (
                :empresa_alvo, :nome_cliente_fantasia, :contato_nome, :contato_telefone, :contato_email,
                :etapa_funil, :valor_estimado, :spin_situacao, :spin_problema, :spin_implicacao, :spin_necessidade,
                :meddpicc_score, :meddpicc_data, :vendedor_id
            )
        ");
        $stmt->execute([
            'empresa_alvo' => $data['empresa_alvo'],
            'nome_cliente_fantasia' => $data['nome_cliente_fantasia'],
            'contato_nome' => $data['contato_nome'] ?? '',
            'contato_telefone' => $data['contato_telefone'] ?? '',
            'contato_email' => $data['contato_email'] ?? '',
            'etapa_funil' => $data['etapa_funil'] ?? 'Abordagem/Rua',
            'valor_estimado' => (float)($data['valor_estimado'] ?? 0),
            'spin_situacao' => $data['spin_situacao'] ?? '',
            'spin_problema' => $data['spin_problema'] ?? '',
            'spin_implicacao' => $data['spin_implicacao'] ?? '',
            'spin_necessidade' => $data['spin_necessidade'] ?? '',
            'meddpicc_score' => (int)($data['meddpicc_score'] ?? 0),
            'meddpicc_data' => $data['meddpicc_data'] ?? null,
            'vendedor_id' => $data['vendedor_id']
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE prospeccao_leads SET
                empresa_alvo = :empresa_alvo,
                nome_cliente_fantasia = :nome_cliente_fantasia,
                contato_nome = :contato_nome,
                contato_telefone = :contato_telefone,
                contato_email = :contato_email,
                etapa_funil = :etapa_funil,
                valor_estimado = :valor_estimado,
                spin_situacao = :spin_situacao,
                spin_problema = :spin_problema,
                spin_implicacao = :spin_implicacao,
                spin_necessidade = :spin_necessidade,
                meddpicc_score = :meddpicc_score,
                meddpicc_data = :meddpicc_data,
                data_atualizacao = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'empresa_alvo' => $data['empresa_alvo'],
            'nome_cliente_fantasia' => $data['nome_cliente_fantasia'],
            'contato_nome' => $data['contato_nome'] ?? '',
            'contato_telefone' => $data['contato_telefone'] ?? '',
            'contato_email' => $data['contato_email'] ?? '',
            'etapa_funil' => $data['etapa_funil'],
            'valor_estimado' => (float)($data['valor_estimado'] ?? 0),
            'spin_situacao' => $data['spin_situacao'] ?? '',
            'spin_problema' => $data['spin_problema'] ?? '',
            'spin_implicacao' => $data['spin_implicacao'] ?? '',
            'spin_necessidade' => $data['spin_necessidade'] ?? '',
            'meddpicc_score' => (int)($data['meddpicc_score'] ?? 0),
            'meddpicc_data' => $data['meddpicc_data'] ?? null,
            'id' => $id
        ]);
    }

    public static function updateEtapa($id, $etapa, $motivoPerda = null, $motivoPerdaObs = null, $dataRecontato = null, $userId = null) {
        $db = Database::getConnection();
        
        $lead = self::getById($id);
        if (!$lead) return false;

        $recontatoTaskId = $lead['recontato_task_id'];

        // Se for etapa Perdido e forneceu data de recontato, cria tarefa agendada no Kanban
        if ($etapa === 'Perdido' && !empty($dataRecontato)) {
            $taskTitle = "[Recontato Comercial] {$lead['empresa_alvo']} - {$lead['nome_cliente_fantasia']}";
            $taskDesc = "Recontato agendado após perda da oportunidade.\nMotivo: {$motivoPerda}\nObservações: {$motivoPerdaObs}\nContato: {$lead['contato_nome']} ({$lead['contato_telefone']})";
            $vendedor = $lead['vendedor_id'] ?: ($userId ?: 1);

            $taskData = [
                'title' => $taskTitle,
                'description' => $taskDesc,
                'department' => 'Comercial',
                'priority' => 'high',
                'assigned_to' => $vendedor,
                'created_by' => $userId ?: $vendedor,
                'due_date' => $dataRecontato
            ];
            $newTaskId = Task::create($taskData);
            if ($newTaskId) {
                $recontatoTaskId = $newTaskId;
            }
        }

        $stmt = $db->prepare("
            UPDATE prospeccao_leads SET
                etapa_funil = :etapa,
                motivo_perda = :motivo_perda,
                motivo_perda_obs = :motivo_perda_obs,
                data_recontato_futuro = :data_recontato,
                recontato_task_id = :recontato_task_id,
                data_atualizacao = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'etapa' => $etapa,
            'motivo_perda' => $motivoPerda,
            'motivo_perda_obs' => $motivoPerdaObs,
            'data_recontato' => $dataRecontato ?: null,
            'recontato_task_id' => $recontatoTaskId,
            'id' => $id
        ]);
    }

    public static function agendarAtividade($leadId, $dataAgendada, $horarioAgendado, $tipoAtividade, $observacao = '', $userId = null) {
        $lead = self::getById($leadId);
        if (!$lead) return false;

        require_once __DIR__ . '/AgendaSemanal.php';
        return AgendaSemanal::create([
            'vendedor_id' => $userId ?: ($lead['vendedor_id'] ?: 1),
            'lead_id' => $leadId,
            'cliente_nome' => $lead['nome_cliente_fantasia'],
            'contato_nome' => $lead['contato_nome'],
            'contato_telefone' => $lead['contato_telefone'],
            'tipo_atividade' => $tipoAtividade,
            'data_agendada' => $dataAgendada,
            'horario_agendado' => $horarioAgendado ?: '09:00',
            'status_resultado' => 'Planejado',
            'resultado_obs' => $observacao,
            'valor_estimado' => $lead['valor_estimado'],
            'empresa_alvo' => $lead['empresa_alvo']
        ]);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM prospeccao_leads WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
