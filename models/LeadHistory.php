<?php
require_once __DIR__ . '/../database.php';

class LeadHistory {
    public static function add($leadId, $action, $oldValue, $newValue, $comment, $changedBy, $mentionedUserId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO lead_history (lead_id, action, old_value, new_value, comment, changed_by, mentioned_user_id)
            VALUES (:lead_id, :action, :old_value, :new_value, :comment, :changed_by, :mentioned_user_id)
        ");
        return $stmt->execute([
            'lead_id' => $leadId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'comment' => $comment,
            'changed_by' => $changedBy,
            'mentioned_user_id' => $mentionedUserId
        ]);
    }

    public static function getByLead($leadId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT th.*, u.name as user_name, mu.name as mentioned_user_name, mu.avatar_color as mentioned_avatar_color 
            FROM lead_history th
            LEFT JOIN users u ON th.changed_by = u.id
            LEFT JOIN users mu ON th.mentioned_user_id = mu.id
            WHERE th.lead_id = :lead_id
            ORDER BY th.created_at ASC
        ");
        $stmt->execute(['lead_id' => $leadId]);
        return $stmt->fetchAll();
    }
    
    public static function delete($historyId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM lead_history WHERE id = :id AND action = 'comment'");
        return $stmt->execute(['id' => $historyId]);
    }
}
