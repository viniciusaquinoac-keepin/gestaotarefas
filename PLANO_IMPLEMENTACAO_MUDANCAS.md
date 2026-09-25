# PLANO DE IMPLEMENTAÇÃO - SISTEMA METRIFICADO DE GESTÃO, COMERCIAL E PLAYBOOKS
**Grupo Empresarial Autoitec Engenharia Industrial & Keepin Automação/Soluções IoT**
*Versão:* 2.0 (Atualizado com Ciclo Dinâmico Q4 e Módulo de Playbooks)  
*Data:* Setembro/2026  
*Destino:* Pasta raiz do projeto (`/PLANO_IMPLEMENTACAO_MUDANCAS.md`)

---

## 1. Visão Geral e Diretrizes Estratégicas (Atualizado)

Este plano orienta a evolução do sistema de gestão (`gestaotarefas`) de uma estrutura reativa de Kanban para uma **plataforma metrificada de gestão operacional, comercial e de treinamento de equipe**.

### Diretrizes de Negócio e Atualizações Solicitadas:

1. **Ciclos Trimestrais Fixos Calendardizados (Cálculo 100% Dinâmico)**:
   - **Q1**: Janeiro / Fevereiro / Março
   - **Q2**: Abril / Maio / Junho
   - **Q3**: Julho / Agosto / Setembro
   - **Q4**: Outubro / Novembro / Dezembro
   - **Vigência Inicial**: As tarefas e lançamentos efetuados a partir de 24/09/2026 valerão e serão contabilizados oficialmente no **Trimestre Q4 (Out/Nov/Dez)** (`2026-Q4`).
   - **Eliminação de Botões Manuais**: A transição de trimestre e o arquivamento de histórico ocorrem de forma **100% automática e dinâmica** no código via funções de data (`CURRENT_DATE` / `QUARTER`), sem necessidade de botões manuais de encerramento.

2. **Segregação Estrita de KPIs por Usuário e Processo**:
   - **Engenharia / Automação (Autoitec Operacional)**: Foco em SLA de entregas de orçamentos (prazo customizado), cumprimento de marcos técnicos (OTIF), qualidade de execução e controle de CPV.
   - **Comercial / Vendas (Autoitec & Keepin)**: Foco em volume de prospecção, visitas presenciais in loco, qualificação de oportunidades e fechamento de novos contratos de assinatura/locação.

3. **Qualificação Comercial Nativa no Código**:
   - **Autoitec**: Mapeamento via **SPIN Selling** (Situação, Problema, Implicação, Necessidade) + **MEDDPICC** no funil B2B.
   - **Keepin**: **Field Sales (Door-to-Door)** com foco em supermercados, mercearias, açougues e padarias (Placas R$ 4.000,00 e locação KPRemote R$ 40,00/mês).

4. **Abono Inteligente de SLA**:
   - Prorrogações justificadas por culpa do **Cliente/Planta** registram `abono_penalidade = 1`, preservando integralmente o score de pontualidade do colaborador.

5. **Score Trimestral (0 a 100 Pts), Bônus Acelerador e Ranking Aberto**:
   - Score = 100 Pts: Destrava **100% da Comissão + R$ 500,00 de Bônus Acelerador Fixo**.
   - Ranking Aberto público para toda a equipe, promovendo gamificação e transparência.

6. **Nova Aba: Playbook Comercial Nativo no App (`views/playbook.php`)**:
   - Módulo completo, intuitivo e interativo incorporado diretamente ao menu do sistema, separando as metodologias da **Autoitec** e da **Keepin**.

---

## 2. Estrutura de Arquivos do Projeto

```
gestaotarefas/
├── .antigravity/
│   └── rules.md                       <-- [CRIADO] Diretrizes do agente com ciclo dinâmico Q4
├── database/
│   └── update_schema.sql               <-- [EXECUTADO] Migração SQL das 4 novas tabelas
├── models/
│   ├── ProspeccaoLead.php             <-- [CRIADO] Model de Prospecção SPIN + MEDDPICC
│   ├── VisitaKeepin.php               <-- [CRIADO] Model de Check-in de Field Sales
│   ├── ProrrogacaoTarefa.php          <-- [CRIADO] Model de Histórico com Abono Inteligente
│   └── KpiTrimestral.php              <-- [CRIADO] Model de Cálculo Dinâmico e Ranking
├── controllers/
│   ├── ProspeccaoController.php       <-- [CRIADO] Controller do Funil e Visitas
│   ├── KpiController.php              <-- [CRIADO] Controller de KPIs e Velocímetro
│   └── PlaybookController.php         <-- [CRIADO] Controller dos Playbooks Comerciais
├── views/
│   ├── prospeccao.php                  <-- [CRIADO] Funil de Prospecção, SPIN, MEDDPICC e Visitas
│   ├── kpis_ranking.php                <-- [CRIADO] Tela de KPIs, Velocímetro e Ranking Dinâmico
│   ├── playbook.php                    <-- [CRIADO] Aba de Playbooks Comerciais Interativos
│   └── tasks/timeline.php              <-- [ATUALIZADO] Prorrogação com Abono Inteligente
└── PLANO_IMPLEMENTACAO_MUDANCAS.md     <-- [ESTE ARQUIVO] Guia mestre de execução local
```

---

## 3. Schemas do Banco de Dados (SQL Migration)

```sql
-- 1. Tabela de Prospecção e Leads com SPIN + MEDDPICC + Insucesso
CREATE TABLE IF NOT EXISTS prospeccao_leads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    empresa_alvo VARCHAR(50) NOT NULL, -- 'Autoitec' ou 'Keepin'
    nome_cliente_fantasia VARCHAR(150) NOT NULL,
    contato_nome VARCHAR(100),
    contato_telefone VARCHAR(30),
    contato_email VARCHAR(120),
    etapa_funil VARCHAR(50) NOT NULL DEFAULT 'Abordagem/Rua',
    valor_estimado REAL DEFAULT 0,
    spin_situacao TEXT,
    spin_problema TEXT,
    spin_implicacao TEXT,
    spin_necessidade TEXT,
    meddpicc_score INT DEFAULT 0,
    meddpicc_data TEXT,
    motivo_perda VARCHAR(50),
    motivo_perda_obs TEXT,
    data_recontato_futuro DATE,
    recontato_task_id INTEGER,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    vendedor_id INTEGER NOT NULL,
    FOREIGN KEY (vendedor_id) REFERENCES users(id)
);

-- 2. Tabela de Histórico de Prorrogações e Abonos de SLA
CREATE TABLE IF NOT EXISTS prorrogacoes_tarefas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tarefa_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_vencimento_anterior DATE NOT NULL,
    nova_data_vencimento DATE NOT NULL,
    categoria_motivo VARCHAR(50) NOT NULL, -- 'Cliente/Planta', 'Fornecedor', 'Interno', 'Campo'
    justificativa TEXT NOT NULL,
    abono_penalidade BOOLEAN DEFAULT 0,    -- 1 = Isento (Cliente/Planta), 0 = Impacta SLA/OTIF
    FOREIGN KEY (tarefa_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);

-- 3. Tabela de Registro de Visitas de Rua (Field Sales Keepin)
CREATE TABLE IF NOT EXISTS visitas_campo_keepin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendedor_id INTEGER NOT NULL,
    estabelecimento_nome VARCHAR(150) NOT NULL,
    contato_abordado VARCHAR(100),
    telefone VARCHAR(30),
    segmento VARCHAR(50) DEFAULT 'Supermercado',
    data_visita DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT,
    interesse_placa BOOLEAN DEFAULT 0,
    interesse_kpremote BOOLEAN DEFAULT 0,
    FOREIGN KEY (vendedor_id) REFERENCES users(id)
);

-- 4. Tabela de Consolidação de KPIs Trimestrais Dinâmicos e Ranking Aberto
CREATE TABLE IF NOT EXISTS kpis_trimestrais_usuario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    trimestre_ano VARCHAR(10) NOT NULL, -- Ex: '2026-Q4'
    pontos_vendas INT DEFAULT 0,         -- Máx 40 pts
    pontos_sla_otif INT DEFAULT 0,     -- Máx 30 pts
    pontos_visitas INT DEFAULT 0,      -- Máx 30 pts
    score_total INT DEFAULT 0,         -- Máx 100 pts
    percentual_comissao_devido DECIMAL(5,2) DEFAULT 0,
    elegivel_acelerador_500 BOOLEAN DEFAULT 0,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);
```

---

## 4. Regras de Negócio, SLAs e Apuração do Score

### A. Matriz de Pesos por Categoria de Tarefa
* **Visita Presencial In Loco (Diagnóstico / Campo)**: `30 Pontos`
* **Apresentação / Defesa de Proposta Comercial**: `25 Pontos`
* **Elaboração de Proposta Técnica (SLA Flexível)**: `20 Pontos`
* **Contato Inicial / Agendamento de Prospecção**: `15 Pontos`

### B. Tabela de Apuração do Score e Bônus Acelerador
| Score Trimestral Acumulado | % da Comissão Paga | Bônus Acelerador Fixo | Status no Sistema |
| :--- | :--- | :--- | :--- |
| **100 Pontos (Score Máximo)** | **100% da Comissão** | **+ R$ 500,00 em Dinheiro** | `Superou Meta (Acelerador Ativado)` |
| **85 a 99 Pontos** | **100% da Comissão** | R$ 0,00 | `Meta Atingida (Elegível Integral)` |
| **70 a 84 Pontos** | **80% da Comissão** | R$ 0,00 | `Desempenho Parcial (-20% Penalizado)` |
| **50 a 69 Pontos** | **50% da Comissão** | R$ 0,00 | `Abaixo do Esperado (-50% Penalizado)` |
| **< 50 Pontos** | **0% da Comissão** | R$ 0,00 | `Sem Elegibilidade no Trimestre` |

---

## 5. Módulo Nativo: Playbook Comercial Completo (`views/playbook.php`)

### ABA 1: Playbook Autoitec Engenharia Industrial
* **Modelo de Negócio**: Automação por Assinatura & Comodato com **Programa Risco Zero (90 dias)**.
* **SPIN Selling na Prática**:
  - **[S] Situação**: Mapeamento da infraestrutura de CLPs e medição da fábrica.
  - **[P] Problema**: Investigação de paradas, desvios de dosagem e falta de rastreabilidade.
  - **[I] Implicação**: Quantificação do prejuízo financeiro (ex: R$ 30 mil/mês em desperdício de insumos).
  - **[N] Necessidade**: Apresentação dos benefícios da automação sem desembolso de CAPEX.
* **Scorecard MEDDPICC**: Checklist obrigatório de 8 pontos para liberação da proposta comercial.

### ABA 2: Playbook Keepin Tecnologia (IoT Varejo)
* **Modelo de Negócio**: **Field Sales (Door-to-Door)** para Supermercados, Mercearias, Açougues e Padarias.
* **Oferta**: Locação KPRemote (**R$ 40,00/mês**) e Venda de Placas de Automação (**R$ 4.000,00**).
* **Script de Abordagem de 10 Minutos (Gatilho da Madrugada)**:
  > *"Você já passou pelo prejuízo de chegar na loja em uma segunda-feira e descobrir que o compressor da câmara fria desligou no domingo, perdendo R$ 15 mil em carnes e laticínios? O KPRemote monitora 24h e envia alertas imediatos no seu WhatsApp."*
* **Ritmo de Campo**: Meta semanal de **15 visitas presenciais registradas com check-in** no sistema.

---

## 6. Checklist de Validação da Implementação

- [x] As 4 tabelas foram criadas com sucesso no banco de dados (`prospeccao_leads`, `prorrogacoes_tarefas`, `visitas_campo_keepin`, `kpis_trimestrais_usuario`).
- [x] O seletor Autoitec/Keepin na tela de prospecção exibe os formulários corretos (SPIN vs. Visitas Keepin).
- [x] Mover um lead para "Perdido/Insucesso" exige motivo obrigatório e agenda recontato futuro automático no Kanban.
- [x] Prorrogações categorizadas como "Cliente/Planta" salvam `abono_penalidade = 1` e preservam a pontuação do usuário.
- [x] O Ranking Comercial calcula dinamicamente o trimestre Q4 sem botões manuais de encerramento.
- [x] O velocímetro do Ranking calcula os 100 pontos e destaca o Bônus Acelerador de R$ 500,00.
- [x] A nova aba `views/playbook.php` renderiza perfeitamente os manuais interativos da Autoitec e Keepin com busca em tempo real.
- [x] Navegação e rotas integradas no sidebar (`views/layout/header.php` e `index.php`).
