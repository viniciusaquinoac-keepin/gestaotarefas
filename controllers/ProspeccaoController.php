<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/ProspeccaoLead.php';
require_once __DIR__ . '/../models/VisitaKeepin.php';
require_once __DIR__ . '/../models/User.php';

class ProspeccaoController {
    public function index() {
        requireAuth();
        $currentUser = getCurrentUser();
        
        $empresa = $_GET['empresa'] ?? 'Autoitec';
        if (!in_array($empresa, ['Autoitec', 'Keepin'])) {
            $empresa = 'Autoitec';
        }

        // Filtro por vendedor opcional (ou todos para gestores)
        $vendedorFiltro = !empty($_GET['vendedor_id']) ? (int)$_GET['vendedor_id'] : null;

        // Buscar leads
        $allLeads = ProspeccaoLead::getAll($empresa, $vendedorFiltro);

        // Agrupar leads por etapa do funil
        $etapas = [
            'Abordagem/Rua' => [],
            'Diagnostico' => [],
            'Proposta' => [],
            'Negociacao' => [],
            'Fechado' => [],
            'Perdido' => []
        ];

        foreach ($allLeads as $lead) {
            $etapa = $lead['etapa_funil'];
            if (!isset($etapas[$etapa])) {
                $etapas['Abordagem/Rua'][] = $lead;
            } else {
                $etapas[$etapa][] = $lead;
            }
        }

        // Se for Keepin, buscar histórico recente de visitas presenciais de rua
        $visitasRecentes = [];
        if ($empresa === 'Keepin') {
            $visitasRecentes = VisitaKeepin::getAll($vendedorFiltro);
        }

        // Lista de vendedores/usuários para formulários
        $db = Database::getConnection();
        $users = $db->query("SELECT id, name, department, avatar_color FROM users WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/prospeccao.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create_lead() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            // Tratamento MEDDPICC Score e Checklist JSON
            $meddpiccChecks = $_POST['meddpicc'] ?? [];
            $meddpiccScore = count($meddpiccChecks) > 0 ? round((count($meddpiccChecks) / 8) * 100) : 0;
            $meddpiccJson = json_encode($meddpiccChecks, JSON_UNESCAPED_UNICODE);

            $data = [
                'empresa_alvo' => $_POST['empresa_alvo'] ?? 'Autoitec',
                'nome_cliente_fantasia' => trim($_POST['nome_cliente_fantasia']),
                'contato_nome' => trim($_POST['contato_nome'] ?? ''),
                'contato_telefone' => trim($_POST['contato_telefone'] ?? ''),
                'contato_email' => trim($_POST['contato_email'] ?? ''),
                'etapa_funil' => $_POST['etapa_funil'] ?? 'Abordagem/Rua',
                'valor_estimado' => (float)str_replace(['.', ','], ['', '.'], $_POST['valor_estimado'] ?? '0'),
                'spin_situacao' => trim($_POST['spin_situacao'] ?? ''),
                'spin_problema' => trim($_POST['spin_problema'] ?? ''),
                'spin_implicacao' => trim($_POST['spin_implicacao'] ?? ''),
                'spin_necessidade' => trim($_POST['spin_necessidade'] ?? ''),
                'meddpicc_score' => $meddpiccScore,
                'meddpicc_data' => $meddpiccJson,
                'vendedor_id' => !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : $userId
            ];

            ProspeccaoLead::create($data);

            header('Location: ' . BASE_URL . '/?page=prospeccao&empresa=' . urlencode($data['empresa_alvo']));
            exit;
        }
    }

    public function update_lead() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['lead_id'];
            $lead = ProspeccaoLead::getById($id);
            if (!$lead) {
                header('Location: ' . BASE_URL . '/?page=prospeccao');
                exit;
            }

            $meddpiccChecks = $_POST['meddpicc'] ?? [];
            $meddpiccScore = count($meddpiccChecks) > 0 ? round((count($meddpiccChecks) / 8) * 100) : 0;
            $meddpiccJson = json_encode($meddpiccChecks, JSON_UNESCAPED_UNICODE);

            $data = [
                'empresa_alvo' => $_POST['empresa_alvo'] ?? $lead['empresa_alvo'],
                'nome_cliente_fantasia' => trim($_POST['nome_cliente_fantasia']),
                'contato_nome' => trim($_POST['contato_nome'] ?? ''),
                'contato_telefone' => trim($_POST['contato_telefone'] ?? ''),
                'contato_email' => trim($_POST['contato_email'] ?? ''),
                'etapa_funil' => $_POST['etapa_funil'] ?? $lead['etapa_funil'],
                'valor_estimado' => (float)str_replace(['.', ','], ['', '.'], $_POST['valor_estimado'] ?? '0'),
                'spin_situacao' => trim($_POST['spin_situacao'] ?? ''),
                'spin_problema' => trim($_POST['spin_problema'] ?? ''),
                'spin_implicacao' => trim($_POST['spin_implicacao'] ?? ''),
                'spin_necessidade' => trim($_POST['spin_necessidade'] ?? ''),
                'meddpicc_score' => $meddpiccScore,
                'meddpicc_data' => $meddpiccJson
            ];

            ProspeccaoLead::update($id, $data);

            header('Location: ' . BASE_URL . '/?page=prospeccao&empresa=' . urlencode($data['empresa_alvo']));
            exit;
        }
    }

    public function update_etapa() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['lead_id'];
            $novaEtapa = $_POST['etapa'];
            $motivoPerda = $_POST['motivo_perda'] ?? null;
            $motivoPerdaObs = $_POST['motivo_perda_obs'] ?? null;
            $dataRecontato = $_POST['data_recontato_futuro'] ?? null;
            $userId = $_SESSION['user_id'];

            ProspeccaoLead::updateEtapa($id, $novaEtapa, $motivoPerda, $motivoPerdaObs, $dataRecontato, $userId);

            if (!empty($_POST['is_ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            $empresa = $_POST['empresa_alvo'] ?? 'Autoitec';
            header('Location: ' . BASE_URL . '/?page=prospeccao&empresa=' . urlencode($empresa));
            exit;
        }
    }

    public function registrar_visita() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            $data = [
                'vendedor_id' => !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : $userId,
                'estabelecimento_nome' => trim($_POST['estabelecimento_nome']),
                'contato_abordado' => trim($_POST['contato_abordado'] ?? ''),
                'telefone' => trim($_POST['telefone'] ?? ''),
                'segmento' => $_POST['segmento'] ?? 'Supermercado',
                'observacao' => trim($_POST['observacao'] ?? ''),
                'interesse_placa' => !empty($_POST['interesse_placa']) ? 1 : 0,
                'interesse_kpremote' => !empty($_POST['interesse_kpremote']) ? 1 : 0,
                'data_visita' => !empty($_POST['data_visita']) ? $_POST['data_visita'] : date('Y-m-d H:i:s')
            ];

            VisitaKeepin::create($data);

            // Opcionalmente também cria lead de Prospecção se demonstrar interesse
            if (!empty($_POST['criar_lead_prospeccao'])) {
                ProspeccaoLead::create([
                    'empresa_alvo' => 'Keepin',
                    'nome_cliente_fantasia' => $data['estabelecimento_nome'],
                    'contato_nome' => $data['contato_abordado'],
                    'contato_telefone' => $data['telefone'],
                    'etapa_funil' => 'Abordagem/Rua',
                    'valor_estimado' => ($data['interesse_placa'] ? 4000 : 0) + ($data['interesse_kpremote'] ? 480 : 0),
                    'spin_situacao' => "Visita de campo Keepin em {$data['segmento']}.",
                    'spin_problema' => $data['observacao'],
                    'vendedor_id' => $data['vendedor_id']
                ]);
            }

            header('Location: ' . BASE_URL . '/?page=prospeccao&empresa=Keepin');
            exit;
        }
    }

    public function delete_lead() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['lead_id'];
            $empresa = $_POST['empresa'] ?? 'Autoitec';
            ProspeccaoLead::delete($id);
            header('Location: ' . BASE_URL . '/?page=prospeccao&empresa=' . urlencode($empresa));
            exit;
        }
    }

    public function delete_visita() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['visita_id'];
            VisitaKeepin::delete($id);
            header('Location: ' . BASE_URL . '/?page=prospeccao&empresa=Keepin');
            exit;
        }
    }
}
