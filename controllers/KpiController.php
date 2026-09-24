<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/KpiTrimestral.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProrrogacaoTarefa.php';

class KpiController {
    public function index() {
        requireAuth();
        $currentUser = getCurrentUser();

        // Determinar trimestre selecionado ou usar o trimestre dinâmico ativo
        $trimestreAtivo = KpiTrimestral::getCurrentQuarter();
        $trimestre = $_GET['trimestre'] ?? $trimestreAtivo;

        // Buscar ranking dinâmico de todos os colaboradores para o trimestre
        $ranking = KpiTrimestral::getRanking($trimestre);

        // Obter KPI individual do usuário logado (ou do usuário selecionado na visão individual)
        $userIdVisao = !empty($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : $currentUser['id'];
        $userKpi = KpiTrimestral::calculateUserKpi($userIdVisao, $trimestre);

        // Buscar informações do usuário em foco
        $db = Database::getConnection();
        $stmtUser = $db->prepare("SELECT id, name, department, avatar_color FROM users WHERE id = :id");
        $stmtUser->execute(['id' => $userIdVisao]);
        $targetUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // Lista de trimestres disponíveis para navegação histórica
        $trimestresDisponiveis = [
            '2026-Q4' => '2026 - Q4 (Out/Nov/Dez - Ciclo Ativo)',
            '2026-Q3' => '2026 - Q3 (Jul/Ago/Set)',
            '2026-Q2' => '2026 - Q2 (Abr/Mai/Jun)',
            '2026-Q1' => '2026 - Q1 (Jan/Fev/Mar)'
        ];

        // Estatísticas do departamento de Engenharia / Automação (SLA e Abonos)
        $dateRange = KpiTrimestral::getQuarterDateRange($trimestre);
        $prorrogacoesStats = ProrrogacaoTarefa::getStatsByPeriod($dateRange['start_date'], $dateRange['end_date']);

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/kpis_ranking.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
