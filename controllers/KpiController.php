<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/KpiTrimestral.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProrrogacaoTarefa.php';

class KpiController {
    public function index() {
        requireAuth();
        $currentUser = getCurrentUser();

        // Determinar tipo de período (semanal, mensal, trimestral)
        $tipoPeriodo = $_GET['tipo_periodo'] ?? 'trimestral';
        if (!in_array($tipoPeriodo, ['semanal', 'mensal', 'trimestral'])) {
            $tipoPeriodo = 'trimestral';
        }

        // Determinar chave do período selecionado
        $periodoValor = $_GET['periodo_valor'] ?? ($_GET['trimestre'] ?? null);

        // Resolver intervalo de datas e label do período
        $periodoInfo = KpiTrimestral::resolvePeriodDateRange($tipoPeriodo, $periodoValor);
        $periodosDisponiveis = KpiTrimestral::getPeriodosDisponiveis($tipoPeriodo);
        $trimestreAtivo = KpiTrimestral::getCurrentQuarter();

        // Buscar ranking dinâmico de todos os colaboradores para o período selecionado
        $ranking = KpiTrimestral::getRanking($periodoInfo);

        // Obter KPI individual do usuário logado (ou do usuário selecionado na visão individual se for admin)
        if (isAdmin()) {
            $userIdVisao = !empty($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : $currentUser['id'];
        } else {
            $userIdVisao = (int)$currentUser['id'];
        }
        $userKpi = KpiTrimestral::calculateUserKpi($userIdVisao, $periodoInfo);

        // Buscar informações do usuário em foco
        $db = Database::getConnection();
        $stmtUser = $db->prepare("SELECT id, name, department, avatar_color FROM users WHERE id = :id");
        $stmtUser->execute(['id' => $userIdVisao]);
        $targetUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // Estatísticas do departamento de Engenharia / Automação (SLA e Abonos)
        $dateRange = [
            'start_date' => $periodoInfo['start_date'],
            'end_date' => $periodoInfo['end_date'],
            'start' => $periodoInfo['start'],
            'end' => $periodoInfo['end']
        ];
        $prorrogacoesStats = ProrrogacaoTarefa::getStatsByPeriod($periodoInfo['start_date'], $periodoInfo['end_date']);

        // Estatísticas consolidadas da Agenda Comercial da equipe no Período Selecionado
        $stmtAgendaEquipe = $db->prepare("
            SELECT 
                COUNT(*) as total_atividades,
                SUM(CASE WHEN tipo_atividade = 'Ligacao' AND status_resultado = 'Realizado' THEN 1 ELSE 0 END) as total_ligacoes,
                SUM(CASE WHEN tipo_atividade = 'Abordagem' AND status_resultado = 'Realizado' THEN 1 ELSE 0 END) as total_abordagens,
                SUM(CASE WHEN tipo_atividade = 'Apresentacao' AND status_resultado = 'Realizado' THEN 1 ELSE 0 END) as total_apresentacoes,
                SUM(CASE WHEN tipo_atividade = 'Proposta' AND status_resultado = 'Realizado' THEN 1 ELSE 0 END) as total_propostas,
                SUM(CASE WHEN tipo_atividade = 'Fechamento' AND status_resultado = 'Realizado' THEN 1 ELSE 0 END) as total_fechamentos,
                COALESCE(SUM(CASE WHEN tipo_atividade = 'Fechamento' AND status_resultado = 'Realizado' THEN valor_estimado ELSE 0 END), 0) as valor_fechado_total
            FROM agenda_comercial_semanal
            WHERE status_resultado = 'Realizado'
              AND data_agendada BETWEEN :start_date AND :end_date
        ");
        $stmtAgendaEquipe->execute([
            'start_date' => $periodoInfo['start_date'],
            'end_date' => $periodoInfo['end_date']
        ]);
        $agendaEquipeStats = $stmtAgendaEquipe->fetch(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/kpis_ranking.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
