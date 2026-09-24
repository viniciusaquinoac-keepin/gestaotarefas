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

        // Filtros
        $vendedorId = !empty($_GET['vendedor_id']) ? (int)$_GET['vendedor_id'] : null;
        $empresa = $_GET['empresa'] ?? null;
        if ($empresa && !in_array($empresa, ['Autoitec', 'Keepin'])) {
            $empresa = null;
        }

        // Buscar atividades agrupadas por dia da semana (Segunda a Domingo)
        $diasSemana = AgendaSemanal::getAtividadesSemana($boundaries['monday'], $boundaries['sunday'], $vendedorId, $empresa);

        // Buscar o quadro de resumo semanal (estilo Prudential)
        $resumo = AgendaSemanal::getResumoSemana($boundaries['monday'], $boundaries['sunday'], $vendedorId, $empresa);

        // Lista de usuários para o filtro
        $db = Database::getConnection();
        $users = $db->query("SELECT id, name, department, avatar_color FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Leads recentes para auto-completar ou vincular agendamento
        $leadsRecentes = ProspeccaoLead::getAll($empresa, $vendedorId);

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/agenda/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $vendedor = !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : $userId;

            $data = [
                'vendedor_id' => $vendedor,
                'lead_id' => !empty($_POST['lead_id']) ? (int)$_POST['lead_id'] : null,
                'cliente_nome' => trim($_POST['cliente_nome']),
                'contato_nome' => trim($_POST['contato_nome'] ?? ''),
                'contato_telefone' => trim($_POST['contato_telefone'] ?? ''),
                'tipo_atividade' => $_POST['tipo_atividade'],
                'data_agendada' => $_POST['data_agendada'],
                'horario_agendado' => $_POST['horario_agendado'] ?: '09:00',
                'status_resultado' => $_POST['status_resultado'] ?? 'Planejado',
                'resultado_obs' => trim($_POST['resultado_obs'] ?? ''),
                'valor_estimado' => (float)str_replace(['.', ','], ['', '.'], $_POST['valor_estimado'] ?? '0'),
                'empresa_alvo' => $_POST['empresa_alvo'] ?? 'Autoitec'
            ];

            AgendaSemanal::create($data);

            $redirectDate = !empty($_POST['data_agendada']) ? $_POST['data_agendada'] : date('Y-m-d');
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate));
            exit;
        }
    }

    public function update_status() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['atividade_id'];
            $status = $_POST['status_resultado'];
            $obs = $_POST['resultado_obs'] ?? null;

            AgendaSemanal::updateStatus($id, $status, $obs);

            if (!empty($_POST['is_ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            $redirectDate = $_POST['data_ref'] ?? date('Y-m-d');
            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate));
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

            AgendaSemanal::reagendar($id, $novaData, $novoHorario, $motivo);

            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($novaData));
            exit;
        }
    }

    public function delete() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['atividade_id'];
            $redirectDate = $_POST['data_ref'] ?? date('Y-m-d');
            AgendaSemanal::delete($id);

            header('Location: ' . BASE_URL . '/?page=agenda&data_ref=' . urlencode($redirectDate));
            exit;
        }
    }
}
