<?php
require_once __DIR__ . '/../database.php';

class ProjectSetting {
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM project_settings");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    public static function get($key, $default = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT value FROM project_settings WHERE key = :key");
        $stmt->execute(['key' => $key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public static function set($key, $value, $description = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO project_settings (key, value, description) 
            VALUES (:key, :value, :description)
            ON CONFLICT(key) DO UPDATE SET value = :value, description = COALESCE(:description, description)
        ");
        return $stmt->execute([
            'key' => $key,
            'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
            'description' => $description
        ]);
    }

    public static function getDefaultPhases() {
        $raw = self::get('default_phases');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) return $decoded;
        }
        return [
            "Administrativo",
            "Projetos",
            "Programação",
            "Testes de Plataformas",
            "Treinamentos",
            "Montagens",
            "Comissionamento/Startup",
            "Operação Assistida"
        ];
    }

    public static function getDefaultCategories() {
        $raw = self::get('default_cost_categories');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) return $decoded;
        }
        return [
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
        ];
    }
}
