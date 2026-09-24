<?php
require 'database.php';
try {
    $db = Database::getConnection();
    
    // Auto-migration anterior
    // $db->exec("ALTER TABLE task_history ADD COLUMN mentioned_user_id INTEGER");
    
    // Criar lead_history
    $result = $db->exec("CREATE TABLE IF NOT EXISTS lead_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_id INTEGER NOT NULL,
        action TEXT NOT NULL,
        old_value TEXT,
        new_value TEXT,
        comment TEXT,
        changed_by INTEGER NOT NULL,
        mentioned_user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (lead_id) REFERENCES leads(id),
        FOREIGN KEY (changed_by) REFERENCES users(id),
        FOREIGN KEY (mentioned_user_id) REFERENCES users(id)
    )");
    
    // Garantir colunas em projects
    $stmtProjCols = $db->query("PRAGMA table_info(projects)");
    if ($stmtProjCols) {
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
                try { $db->exec("ALTER TABLE projects ADD COLUMN $colName $colTypeDef"); } catch (Exception $ex) {}
            }
        }
    }

    // Executar migração do novo schema metrificado (4 tabelas)
    $schemaFile = __DIR__ . '/database/update_schema.sql';
    if (file_exists($schemaFile)) {
        $db->exec(file_get_contents($schemaFile));
    }

    echo "Tabelas atualizadas com sucesso (incluindo Módulo Metrificado: prospeccao_leads, prorrogacoes_tarefas, visitas_campo_keepin, kpis_trimestrais_usuario).";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
