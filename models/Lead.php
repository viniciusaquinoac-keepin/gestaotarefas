<?php
require_once __DIR__ . '/../database.php';

class Lead {
    public static function getAll() {
        $db = Database::getConnection();
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();
        
        $dateFilter = "WHERE (DATE(l.created_at) >= :start OR DATE(l.next_contact_date) >= :start)";
        if (!empty($end)) {
            $dateFilter = "WHERE ((DATE(l.created_at) >= :start AND DATE(l.created_at) <= :end) OR (DATE(l.next_contact_date) >= :start AND DATE(l.next_contact_date) <= :end))";
        }

        $stmt = $db->prepare("
            SELECT l.*, u.name as assigned_name, u.avatar_color,
                   (SELECT GROUP_CONCAT(DISTINCT mu.id || '::' || mu.name || '::' || mu.avatar_color) FROM lead_history lh JOIN users mu ON lh.mentioned_user_id = mu.id WHERE lh.lead_id = l.id AND lh.mentioned_user_id IS NOT NULL) as mentioned_users_data,
                   (SELECT COUNT(*) FROM lead_history lh2 WHERE lh2.lead_id = l.id AND lh2.action = 'postponed') as postpone_count
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id 
            $dateFilter
            ORDER BY l.id DESC
        ");
        
        $params = ['start' => $start];
        if (!empty($end)) $params['end'] = $end;
        
        $stmt->execute($params);
        $leads = $stmt->fetchAll();
        return self::processMentions($leads);
    }

    public static function getByStatus($status) {
        $db = Database::getConnection();
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();
        
        $dateFilter = "AND (DATE(l.created_at) >= :start OR DATE(l.next_contact_date) >= :start)";
        if (!empty($end)) {
            $dateFilter = "AND ((DATE(l.created_at) >= :start AND DATE(l.created_at) <= :end) OR (DATE(l.next_contact_date) >= :start AND DATE(l.next_contact_date) <= :end))";
        }

        $stmt = $db->prepare("
            SELECT l.*, u.name as assigned_name, u.avatar_color,
                   (SELECT GROUP_CONCAT(DISTINCT mu.id || '::' || mu.name || '::' || mu.avatar_color) FROM lead_history lh JOIN users mu ON lh.mentioned_user_id = mu.id WHERE lh.lead_id = l.id AND lh.mentioned_user_id IS NOT NULL) as mentioned_users_data,
                   (SELECT COUNT(*) FROM lead_history lh2 WHERE lh2.lead_id = l.id AND lh2.action = 'postponed') as postpone_count
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id 
            WHERE l.status = :status 
            $dateFilter
            ORDER BY l.id DESC
        ");
        
        $params = ['status' => $status, 'start' => $start];
        if (!empty($end)) $params['end'] = $end;
        
        $stmt->execute($params);
        $leads = $stmt->fetchAll();
        return self::processMentions($leads);
    }

    private static function processMentions($leads) {
        foreach ($leads as &$lead) {
            $lead['mentions'] = [];
            $lead['mentioned_user_id'] = '';
            if (!empty($lead['mentioned_users_data'])) {
                $mentionsStr = explode(',', $lead['mentioned_users_data']);
                $ids = [];
                foreach ($mentionsStr as $mStr) {
                    $parts = explode('::', $mStr);
                    if (count($parts) === 3) {
                        $lead['mentions'][] = [
                            'id' => $parts[0],
                            'name' => $parts[1],
                            'avatar_color' => $parts[2]
                        ];
                        $ids[] = $parts[0];
                    }
                }
                $lead['mentioned_user_id'] = implode(',', $ids);
            }
        }
        return $leads;
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO leads (name, company, phone, email, source, status, estimated_value, next_contact_date, notes, assigned_to, created_by)
            VALUES (:name, :company, :phone, :email, :source, 'new', :estimated_value, :next_contact_date, :notes, :assigned_to, :created_by)
        ");
        $stmt->execute([
            'name' => $data['name'],
            'company' => $data['company'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'source' => $data['source'],
            'estimated_value' => $data['estimated_value'] ?: 0,
            'next_contact_date' => $data['next_contact_date'] ?: null,
            'notes' => $data['notes'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'created_by' => $data['created_by']
        ]);
        return $db->lastInsertId();
    }

    public static function updateStatus($leadId, $newStatus, $userId = null) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT status FROM leads WHERE id = :id");
        $stmt->execute(['id' => $leadId]);
        $oldStatus = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE leads SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute([
            'status' => $newStatus,
            'id' => $leadId
        ]);

        if ($success && $oldStatus !== $newStatus && $userId) {
            require_once __DIR__ . '/LeadHistory.php';
            LeadHistory::add($leadId, 'status_change', $oldStatus, $newStatus, 'Mudou status no funil', $userId);
        }

        return $success;
    }
    public static function update($leadId, $data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE leads SET 
                name = :name, 
                company = :company, 
                phone = :phone, 
                email = :email, 
                source = :source, 
                estimated_value = :estimated_value, 
                next_contact_date = :next_contact_date, 
                notes = :notes, 
                assigned_to = :assigned_to, 
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'company' => $data['company'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'source' => $data['source'],
            'estimated_value' => $data['estimated_value'] ?: 0,
            'next_contact_date' => $data['next_contact_date'] ?: null,
            'notes' => $data['notes'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'id' => $leadId
        ]);
    }

    public static function postpone($leadId, $newDate, $comment, $userId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT next_contact_date FROM leads WHERE id = :id");
        $stmt->execute(['id' => $leadId]);
        $oldDate = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE leads SET next_contact_date = :next_contact_date, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute([
            'next_contact_date' => $newDate,
            'id' => $leadId
        ]);

        if ($success) {
            require_once __DIR__ . '/LeadHistory.php';
            LeadHistory::add($leadId, 'postponed', $oldDate, $newDate, $comment, $userId);
        }

        return $success;
    }

    public static function delete($leadId) {
        $db = Database::getConnection();
        $db->prepare("DELETE FROM lead_history WHERE lead_id = :id")->execute(['id' => $leadId]);
        $stmt = $db->prepare("DELETE FROM leads WHERE id = :id");
        return $stmt->execute(['id' => $leadId]);
    }
}
