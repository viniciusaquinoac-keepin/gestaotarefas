<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO('sqlite:' . DB_PATH);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec('PRAGMA foreign_keys = ON;');
                
                // --- AUTO MIGRATIONS (Executado apenas se a tabela não existir) ---
                self::$pdo->exec("CREATE TABLE IF NOT EXISTS suggestions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    description TEXT,
                    product TEXT,
                    status TEXT NOT NULL DEFAULT 'analysis',
                    created_by INTEGER NOT NULL,
                    task_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id),
                    FOREIGN KEY (task_id) REFERENCES tasks(id)
                )");

                // Auto migration for task_history.mentioned_user_id
                $stmt = self::$pdo->query("PRAGMA table_info(task_history)");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $hasMention = false;
                foreach ($columns as $col) {
                    if ($col['name'] === 'mentioned_user_id') {
                        $hasMention = true;
                        break;
                    }
                }
                // Auto migrations para módulo de Gestão de Obras
                self::$pdo->exec("CREATE TABLE IF NOT EXISTS projects (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    client TEXT,
                    description TEXT,
                    status TEXT NOT NULL DEFAULT 'in_progress',
                    target_material_budget REAL DEFAULT 0,
                    target_labor_budget REAL DEFAULT 0,
                    target_budget REAL DEFAULT 0,
                    hourly_rate REAL DEFAULT 0,
                    start_date DATE,
                    estimated_end_date DATE,
                    actual_end_date DATE,
                    total_months INTEGER DEFAULT 0,
                    lead_id INTEGER,
                    created_by INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id),
                    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL
                )");

                // Garantir que colunas adicionadas recentemente existam na tabela projects
                $stmtProjCols = self::$pdo->query("PRAGMA table_info(projects)");
                $projCols = $stmtProjCols->fetchAll(PDO::FETCH_ASSOC);
                $existingCols = array_column($projCols, 'name');

                $colsToAdd = [
                    'target_material_budget' => 'REAL DEFAULT 0',
                    'target_labor_budget' => 'REAL DEFAULT 0',
                    'target_budget' => 'REAL DEFAULT 0',
                    'hourly_rate' => 'REAL DEFAULT 0',
                    'total_months' => 'INTEGER DEFAULT 0',
                    'lead_id' => 'INTEGER'
                ];

                foreach ($colsToAdd as $colName => $colTypeDef) {
                    if (!in_array($colName, $existingCols)) {
                        try {
                            self::$pdo->exec("ALTER TABLE projects ADD COLUMN $colName $colTypeDef");
                        } catch (PDOException $e) {}
                    }
                }

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS project_phases (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    order_num INTEGER DEFAULT 0,
                    status TEXT NOT NULL DEFAULT 'not_started',
                    planned_start DATE,
                    planned_end DATE,
                    actual_start DATE,
                    actual_end DATE,
                    delay_reason TEXT,
                    sector TEXT,
                    observations TEXT,
                    is_client_visible INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS project_tasks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    phase_id INTEGER NOT NULL,
                    project_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'pending',
                    assigned_to INTEGER,
                    planned_start DATE,
                    planned_end DATE,
                    actual_start DATE,
                    actual_end DATE,
                    planned_days INTEGER DEFAULT 0,
                    actual_days INTEGER DEFAULT 0,
                    hours_estimated REAL DEFAULT 0,
                    hours_spent REAL DEFAULT 0,
                    delay_reason TEXT,
                    notes TEXT,
                    order_num INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (phase_id) REFERENCES project_phases(id) ON DELETE CASCADE,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS project_costs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL,
                    category TEXT NOT NULL,
                    description TEXT NOT NULL,
                    amount REAL NOT NULL,
                    cost_date DATE NOT NULL,
                    receipt_note TEXT,
                    created_by INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by) REFERENCES users(id)
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS project_purchases (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL,
                    phase_id INTEGER,
                    task_id INTEGER,
                    item_description TEXT NOT NULL,
                    supplier TEXT,
                    estimated_cost REAL DEFAULT 0,
                    actual_cost REAL DEFAULT 0,
                    status TEXT NOT NULL DEFAULT 'requested',
                    delay_reason TEXT,
                    request_date DATE,
                    expected_date DATE,
                    actual_date DATE,
                    requested_by INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                    FOREIGN KEY (phase_id) REFERENCES project_phases(id) ON DELETE SET NULL,
                    FOREIGN KEY (task_id) REFERENCES project_tasks(id) ON DELETE SET NULL,
                    FOREIGN KEY (requested_by) REFERENCES users(id)
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS project_timeline (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL,
                    action TEXT NOT NULL,
                    details TEXT,
                    old_value TEXT,
                    new_value TEXT,
                    changed_by INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                    FOREIGN KEY (changed_by) REFERENCES users(id)
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS project_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    key TEXT UNIQUE NOT NULL,
                    value TEXT NOT NULL,
                    description TEXT
                )");

                // Seed default settings if not exists
                $stmtSettings = self::$pdo->query("SELECT COUNT(*) FROM project_settings");
                if ($stmtSettings->fetchColumn() == 0) {
                    $defaultPhases = json_encode([
                        "Administrativo",
                        "Projetos",
                        "Programação",
                        "Testes de Plataformas",
                        "Treinamentos",
                        "Montagens",
                        "Comissionamento/Startup",
                        "Operação Assistida"
                    ], JSON_UNESCAPED_UNICODE);

                    $defaultCategories = json_encode([
                        "Material elétrico",
                        "Material hidráulico",
                        "Cabos e conectores",
                        "Componentes de automação",
                        "Painéis elétricos",
                        "Mão de obra terceirizada",
                        "Equipamentos",
                        "Transporte/Logística",
                        "Alimentação",
                        "EPIs",
                        "Outros"
                    ], JSON_UNESCAPED_UNICODE);

                    $stmtInsert = self::$pdo->prepare("INSERT INTO project_settings (key, value, description) VALUES (?, ?, ?)");
                    $stmtInsert->execute(['default_hourly_rate', '50.00', 'Valor por hora padrão para cálculo de custo de mão de obra']);
                    $stmtInsert->execute(['default_phases', $defaultPhases, 'Lista padrão de etapas de obra']);
                    $stmtInsert->execute(['default_cost_categories', $defaultCategories, 'Categorias padrão para lançamento de custos']);
                }

                // --- MÓDULO METRIFICADO AUTOITEC & KEEPIN ---
                self::$pdo->exec("CREATE TABLE IF NOT EXISTS prospeccao_leads (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    empresa_alvo VARCHAR(50) NOT NULL,
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
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS prorrogacoes_tarefas (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tarefa_id INTEGER NOT NULL,
                    usuario_id INTEGER NOT NULL,
                    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                    data_vencimento_anterior DATE NOT NULL,
                    nova_data_vencimento DATE NOT NULL,
                    categoria_motivo VARCHAR(50) NOT NULL,
                    justificativa TEXT NOT NULL,
                    abono_penalidade BOOLEAN DEFAULT 0,
                    FOREIGN KEY (tarefa_id) REFERENCES tasks(id) ON DELETE CASCADE,
                    FOREIGN KEY (usuario_id) REFERENCES users(id)
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS visitas_campo_keepin (
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
                )");

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS kpis_trimestrais_usuario (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    usuario_id INTEGER NOT NULL,
                    trimestre_ano VARCHAR(10) NOT NULL,
                    pontos_vendas INT DEFAULT 0,
                    pontos_sla_otif INT DEFAULT 0,
                    pontos_visitas INT DEFAULT 0,
                    score_total INT DEFAULT 0,
                    percentual_comissao_devido DECIMAL(5,2) DEFAULT 0,
                    elegivel_acelerador_500 BOOLEAN DEFAULT 0,
                    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (usuario_id) REFERENCES users(id)
                )");
            } catch (PDOException $e) {
                die("Erro de conexão com o banco de dados: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
