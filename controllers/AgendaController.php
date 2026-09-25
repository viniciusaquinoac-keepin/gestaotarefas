<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/AgendaSemanal.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProspeccaoLead.php';

class AgendaController {
    public function index() {
        requireAuth();
        $currentUser = getCurrentUser();

        // Data de referência da semana (default: data atual)
        $refDate = $_GET['data_ref'] ?? date('Y-m-d');
        $boundaries = AgendaSemanal::getWeekBoundaries($refDate);

        // Filtro de Vendedor: se usuário comum, restrito estritamente a si mesmo
        if (isAdmin()) {
            $vendedorId = !empty($_GET['vendedor_id']) ? (int)$_GET['vendedor_id'] : null;
        } else {
            $vendedorId = (int)$_SESSION['user_id'];
        }

        $empresa = $_GET['empresa'] ?? null;
        if ($empresa && !in_array($empresa, ['Autoitec', 'Keepin'])) {
            $empresa = null;
        }

        // Buscar atividades agrupadas por dia da semana (Segunda a Domingo)
        $diasSemana = AgendaSemanal::getAtividadesSemana($boundaries['monday'], $boundaries['sunday'], $vendedorId, $empresa);

        // Buscar o quadro de resumo semanal (estilo Prudential)
        $resumo = AgendaSemanal::getResumoSemana($boundaries['monday'], $boundaries['sunday'], $vendedorId, $empresa);

        // Lista de usuários para o filtro e formulários
        $db = Database::getConnection();
        if (isAdmin()) {
            $users = $db->query("SELECT id, name, department, avatar_color FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $users = $db->query("SELECT id, name, department, avatar_color FROM users WHERE id = " . (int)$_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);
        }

        // Leads recentes para auto-completar ou vincular agendamento
        $leadsRecentes = ProspeccaoLead::getAll($empresa, $vendedorId);

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/agenda/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_SESSION['user_id'];
            // Se usuário comum, vincula obrigatoriamente a si mesmo
            $vendedor = !isAdmin() ? $userId : (!empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : $userId);
            $leadId = !empty($_POST['lead_id']) ? (int)$_POST['lead_id'] : null;
            $tipoAtividade = $_POST['tipo_atividade'];
            $empresaAlvo = $_POST['empresa_alvo'] ?? 'Autoitec';

            $data = [
                'vendedor_id' => $vendedor,
                'lead_id' => $leadId,
                'atividade_anterior_id' => !empty($_POST['atividade_anterior_id']) ? (int)$_POST['atividade_anterior_id'] : null,
                'ciclo_origem_id' => !empty($_POST['ciclo_origem_id']) ? (int)$_POST['ciclo_origem_id'] : null,
                'data_primeiro_contato' => !empty($_POST['data_primeiro_contato']) ? $_POST['data_primeiro_contato'] : null,
                'passo_sequencia' => !empty($_POST['passo_sequencia']) ? (int)$_POST['passo_sequencia'] : null,
                'cliente_nome' => trim($_POST['cliente_nome']),
                'contato_nome' => trim($_POST['contato_nome'] ?? ''),
                'contato_telefone' => trim($_POST['contato_telefone'] ?? ''),
                'tipo_atividade' => $tipoAtividade,
                'data_agendada' => $_POST['data_agendada'],
                'horario_agendado' => $_POST['horario_agendado'] ?: '09:00',
                'status_resultado' => $_POST['status_resultado'] ?? 'Planejado',
                'resultado_obs' => trim($_POST['resultado_obs'] ?? ''),
                'valor_estimado' => (float)str_replace(['.', ','], ['', '.'], $_POST['valor_estimado'] ?? '0'),
                'empresa_alvo' => $empresaAlvo
            ];

            AgendaSemanal::create($data);

            // Se solicitado, sincroniza a etapa do lead no funil de prospecção
            if ($leadId && !empty($_POST['atualizar_etapa_lead'])) {
                $mapaEtapas = [
                    'Ligacao' => 'Prospeccao',
                    'Abordagem' => 'Abordagem',
                    'Diagnostico' => 'Diagnostico',
                    'Apresentacao' => 'Apresentacao',
                    'Proposta' => 'Proposta',
                    'Fechamento' => 'Fechado'
                ];
                if (isset($mapaEtapas[$tipoAtividade])) {
                    ProspeccaoLead::updateEtapa($leadId, $mapaEtapas[$tipoAtividade], null, null, null, $vendedor);
                }
            }

            $redirectDate = !empty($_POST['data_agendada']) ? $_POST['data_agendada'] : date('Y-m-d');
            $empresaFiltro = !empty($_POST['empresa_filtro']) ? $_POST['empresa_filtro'] : (!empty($_GET['empresa']) ? $_GET['empresa'] : '');
            $empresaParam = !empty($empresaFiltro) ? '&empresa=' . urlencode($empresaFiltro) : '';
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate) . $empresaParam);
            exit;
        }
    }

    public function update_status() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['atividade_id'];
            $status = $_POST['status_resultado'];
            $obs = $_POST['resultado_obs'] ?? null;
            $userId = (int)$_SESSION['user_id'];

            $ativ = AgendaSemanal::getById($id);
            if (!$ativ) {
                if (!empty($_POST['is_ajax'])) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Atividade não encontrada']);
                    exit;
                }
                die("Atividade não encontrada.");
            }

            if (!isAdmin() && (int)$ativ['vendedor_id'] !== $userId) {
                if (!empty($_POST['is_ajax'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você só pode gerenciar compromissos da sua própria agenda.']);
                    exit;
                }
                die("Acesso negado. Você só pode gerenciar compromissos da sua própria agenda.");
            }

            AgendaSemanal::updateStatus($id, $status, $obs);

            if (!empty($_POST['is_ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            $redirectDate = $_POST['data_ref'] ?? date('Y-m-d');
            $empresaParam = !empty($_POST['empresa']) ? '&empresa=' . urlencode($_POST['empresa']) : '';
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate) . $empresaParam);
            exit;
        }
    }

    public function reagendar() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['atividade_id'];
            $novaData = $_POST['nova_data'];
            $novoHorario = $_POST['novo_horario'] ?: '09:00';
            $motivo = $_POST['motivo_reagendamento'] ?? null;
            $userId = (int)$_SESSION['user_id'];

            $ativ = AgendaSemanal::getById($id);
            if (!$ativ) {
                die("Atividade não encontrada.");
            }

            if (!isAdmin() && (int)$ativ['vendedor_id'] !== $userId) {
                die("Acesso negado. Você só pode reagendar compromissos da sua própria agenda.");
            }

            AgendaSemanal::reagendar($id, $novaData, $novoHorario, $motivo);

            $empresaParam = !empty($_POST['empresa']) ? '&empresa=' . urlencode($_POST['empresa']) : '';
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($novaData) . $empresaParam);
            exit;
        }
    }

    public function delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['atividade_id'];
            $redirectDate = $_POST['data_ref'] ?? date('Y-m-d');
            $userId = (int)$_SESSION['user_id'];

            $ativ = AgendaSemanal::getById($id);
            if (!$ativ) {
                die("Atividade não encontrada.");
            }

            if (!isAdmin() && (int)$ativ['vendedor_id'] !== $userId) {
                die("Acesso negado. Você só pode excluir compromissos da sua própria agenda.");
            }

            AgendaSemanal::delete($id);

            $empresaParam = !empty($_POST['empresa']) ? '&empresa=' . urlencode($_POST['empresa']) : '';
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate) . $empresaParam);
            exit;
        }
    }

    public function registrar_perda() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['atividade_id'];
            $motivoPerda = $_POST['motivo_perda'];
            $motivoPerdaObs = $_POST['motivo_perda_obs'] ?? null;
            $dataRecontato = !empty($_POST['data_recontato_futuro']) ? $_POST['data_recontato_futuro'] : null;
            $userId = (int)$_SESSION['user_id'];

            $ativ = AgendaSemanal::getById($id);
            if (!$ativ) {
                die("Atividade não encontrada.");
            }

            if (!isAdmin() && (int)$ativ['vendedor_id'] !== $userId) {
                die("Acesso negado. Você só pode registrar perda de compromissos da sua própria agenda.");
            }

            AgendaSemanal::registrarPerda($id, $motivoPerda, $motivoPerdaObs, $dataRecontato, $userId);

            $redirectDate = $_POST['data_ref'] ?? date('Y-m-d');
            $empresaParam = !empty($_POST['empresa']) ? '&empresa=' . urlencode($_POST['empresa']) : '';
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate) . $empresaParam);
            exit;
        }
    }

    public function ciclo_vida() {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        $ativ = AgendaSemanal::getById($id);
        if (!$ativ) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Atividade não encontrada']);
            exit;
        }

        if (!isAdmin() && (int)$ativ['vendedor_id'] !== $userId) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acesso negado']);
            exit;
        }

        $cadeia = AgendaSemanal::getCadeiaCicloVida($id);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'cadeia' => $cadeia]);
        exit;
    }
}
