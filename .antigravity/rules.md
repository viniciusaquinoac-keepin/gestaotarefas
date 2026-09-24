# DIRETRIZES DE IA E REGRAS DO SISTEMA (GESTAOTAREFAS)
**Grupo Empresarial Autoitec Engenharia Industrial & Keepin Automação/Soluções IoT**

Você é um Desenvolvedor Full-Stack Sênior. O objetivo é manter e evoluir o sistema 'gestaotarefas' como uma plataforma metrificada de gestão operacional, comercial e treinamento de equipe.

## Regras Rígidas do Sistema:
1. **Cartões e Alertas Visuais**:
   - Manter bordas coloridas nos cartões de tarefas e leads:
     - Amarelo (`#ffc107` / alerta): Vencimento Hoje ou próximo (<= 3 dias).
     - Vermelho (`#ff4444` / perigo com sombra): Vencimento Atrasado.
     - Verde (`#198754`): Concluído / Fechado.

2. **Ciclo Trimestral 100% Dinâmico**:
   - Considerar o trimestre ativo de forma 100% DINÂMICA com base na data do sistema (`CURRENT_DATE` / `QUARTER`).
   - Os lançamentos a partir de 24/09/2026 valem e são contabilizados oficialmente para o trimestre `2026-Q4` (Out/Nov/Dez).
   - NÃO criar botões manuais de encerrar trimestre: a rotação de período e o isolamento de métricas ocorrem de forma fluida e automática pelo calendário civil.

3. **Abono Inteligente de SLA / OTIF**:
   - Toda prorrogação de prazo exige a categorização do motivo ('Cliente/Planta', 'Fornecedor', 'Interno', 'Campo').
   - Se a prorrogação for categorizada como 'Cliente/Planta', salvar `abono_penalidade = 1`.
   - No cálculo de OTIF e SLA do colaborador, prorrogações com `abono_penalidade = 1` NÃO descontam pontos e preservam a pontuação de pontualidade.

4. **Painel de KPIs, Velocímetro e Ranking Aberto**:
   - Score trimestral acumulado de 0 a 100 pontos:
     - Vendas / Fechamento: até 40 pontos.
     - SLA / OTIF (pontualidade com abono): até 30 pontos.
     - Visitas Presenciais de Rua: até 30 pontos.
   - Faixas de comissão: 100 pts (100%), 85-99 pts (100%), 70-84 pts (80%), 50-69 pts (50%), <50 pts (0%).
   - Se o colaborador atingir 100 Pontos, exibir o badge destacado: "🏆 BÔNUS ACELERADOR DE R$ 500,00 DESTRAVADO!".
   - Ranking Comercial Aberto ao vivo para engajamento e gamificação de toda a equipe.

5. **Módulo de Playbooks Comerciais**:
   - Manter a aba 'Playbooks' com separação interativa entre Autoitec (B2B Industrial, Comodato, Risco Zero 90 dias, SPIN Selling e MEDDPICC) e Keepin (Field Sales Door-to-Door, Locação KPRemote R$ 40/mês, Placas R$ 4.000, Pitch da Madrugada e meta de 15 visitas/semana).
