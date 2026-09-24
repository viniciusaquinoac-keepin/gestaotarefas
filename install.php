<?php
require_once __DIR__ . '/database.php';

try {
    $db = Database::getConnection();

    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            department TEXT NOT NULL,
            avatar_color TEXT DEFAULT '#6366f1',
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )",
        
        "CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            department TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'todo',
            priority TEXT NOT NULL DEFAULT 'medium',
            assigned_to INTEGER,
            created_by INTEGER NOT NULL,
            due_date DATE NOT NULL,
            original_due_date DATE NOT NULL,
            completed_at DATETIME,
            meeting_id INTEGER,
            position INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (assigned_to) REFERENCES users(id),
            FOREIGN KEY (created_by) REFERENCES users(id),
            FOREIGN KEY (meeting_id) REFERENCES meetings(id)
        )",

        "CREATE TABLE IF NOT EXISTS task_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            task_id INTEGER NOT NULL,
            action TEXT NOT NULL,
            old_value TEXT,
            new_value TEXT,
            comment TEXT,
            changed_by INTEGER NOT NULL,
            mentioned_user_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (task_id) REFERENCES tasks(id),
            FOREIGN KEY (changed_by) REFERENCES users(id),
            FOREIGN KEY (mentioned_user_id) REFERENCES users(id)
        )",

        "CREATE TABLE IF NOT EXISTS leads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            company TEXT,
            phone TEXT,
            email TEXT,
            source TEXT,
            status TEXT NOT NULL DEFAULT 'new',
            estimated_value REAL DEFAULT 0,
            next_contact_date DATE,
            notes TEXT,
            assigned_to INTEGER,
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (assigned_to) REFERENCES users(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )",

        "CREATE TABLE IF NOT EXISTS lead_interactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lead_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            description TEXT NOT NULL,
            contact_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            next_action TEXT,
            created_by INTEGER NOT NULL,
            FOREIGN KEY (lead_id) REFERENCES leads(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )",

        "CREATE TABLE IF NOT EXISTS lead_history (
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
        )",

        "CREATE TABLE IF NOT EXISTS meetings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            date DATE NOT NULL,
            notes TEXT,
            participants TEXT,
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )"
    ];

    foreach ($queries as $query) {
        $db->exec($query);
    }

    // Criar usuário admin padrão
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@keepin.com'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('123456', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO users (name, email, password, role, department) VALUES ('Administrador', 'admin@keepin.com', '$hash', 'admin', 'TI')");
        echo "Usuário admin criado (admin@keepin.com / 123456).<br>";
    }

    echo "Banco de dados inicializado com sucesso!";
} catch (Exception $e) {
    echo "Erro na instalação: " . $e->getMessage();
}
