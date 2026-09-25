-- ===============================================================================
-- MIGRAÇÃO DE BANCO DE DADOS - SISTEMA METRIFICADO AUTOITEC & KEEPIN
-- ===============================================================================

-- 1. Tabela de Prospecção e Leads com SPIN + MEDDPICC + Insucesso
CREATE TABLE IF NOT EXISTS prospeccao_leads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    empresa_alvo VARCHAR(50) NOT NULL, -- 'Autoitec' ou 'Keepin'
    nome_cliente_fantasia VARCHAR(150) NOT NULL,
    contato_nome VARCHAR(100),
    contato_telefone VARCHAR(30),
    contato_email VARCHAR(120),
    etapa_funil VARCHAR(50) NOT NULL DEFAULT 'Abordagem/Rua', -- 'Abordagem/Rua', 'Diagnostico', 'Proposta', 'Negociacao', 'Fechado', 'Perdido'
    valor_estimado REAL DEFAULT 0,
    
    -- Diagnóstico SPIN Selling
    spin_situacao TEXT,
    spin_problema TEXT,
    spin_implicacao TEXT,
    spin_necessidade TEXT,
    
    -- Qualificação MEDDPICC
    meddpicc_score INT DEFAULT 0, -- Score de 0 a 100%
    meddpicc_data TEXT,           -- Dados JSON detalhados dos critérios
    
    -- Gestão de Perda / Insucesso
    motivo_perda VARCHAR(50),     -- 'Preco/CAPEX', 'Concorrente', 'Decisor Nao Acessado', 'Sem Orcamento'
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
    segmento VARCHAR(50) DEFAULT 'Supermercado', -- 'Supermercado', 'Mercearia', 'Acougue', 'Padaria', 'Outro'
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
    trimestre_ano VARCHAR(10) NOT NULL,        -- Ex: '2026-Q4'
    pontos_vendas INT DEFAULT 0,                -- Máx 40 pts
    pontos_sla_otif INT DEFAULT 0,            -- Máx 30 pts
    pontos_visitas INT DEFAULT 0,             -- Máx 30 pts
    score_total INT DEFAULT 0,                -- Máx 100 pts
    percentual_comissao_devido DECIMAL(5,2) DEFAULT 0, -- 100.00, 80.00, 50.00, 0.00
    elegivel_acelerador_500 BOOLEAN DEFAULT 0, -- 1 para Score = 100
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);

-- 5. Tabela de Agenda Comercial Semanal de Atividades (Metas & Resumo Estilo Prudential)
CREATE TABLE IF NOT EXISTS agenda_comercial_semanal (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendedor_id INTEGER NOT NULL,
    lead_id INTEGER,
    atividade_anterior_id INTEGER,
    ciclo_origem_id INTEGER,
    data_primeiro_contato DATE,
    passo_sequencia INTEGER DEFAULT 1,
    cliente_nome VARCHAR(150) NOT NULL,
    contato_nome VARCHAR(100),
    contato_telefone VARCHAR(30),
    tipo_atividade VARCHAR(50) NOT NULL, -- 'Ligacao', 'Abordagem', 'Diagnostico', 'Apresentacao', 'Proposta', 'Fechamento', 'Outro'
    data_agendada DATE NOT NULL,
    horario_agendado VARCHAR(10) NOT NULL,
    status_resultado VARCHAR(30) DEFAULT 'Planejado', -- 'Planejado', 'Realizado', 'Reagendado', 'Cancelado'
    resultado_obs TEXT,
    valor_estimado REAL DEFAULT 0,
    empresa_alvo VARCHAR(50) DEFAULT 'Autoitec', -- 'Autoitec' ou 'Keepin'
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES users(id),
    FOREIGN KEY (lead_id) REFERENCES prospeccao_leads(id) ON DELETE SET NULL,
    FOREIGN KEY (atividade_anterior_id) REFERENCES agenda_comercial_semanal(id),
    FOREIGN KEY (ciclo_origem_id) REFERENCES agenda_comercial_semanal(id)
);
