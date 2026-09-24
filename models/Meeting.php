<?php
require_once __DIR__ . '/../database.php';

class Meeting {
    public static function getAll() {
        $db = Database::getConnection();
        $start = getGlobalFilterStart();
        $end = getGlobalFilterEnd();
        
        $dateFilter = "WHERE (DATE(m.created_at) >= :start OR DATE(m.date) >= :start)";
        if (!empty($end)) {
            $dateFilter = "WHERE ((DATE(m.created_at) >= :start AND DATE(m.created_at) <= :end) OR (DATE(m.date) >= :start AND DATE(m.date) <= :end))";
        }

        $stmt = $db->prepare("
            SELECT m.*, u.name as created_name 
            FROM meetings m 
            LEFT JOIN users u ON m.created_by = u.id 
            $dateFilter
            ORDER BY m.date DESC, m.id DESC
        ");
        
        $params = ['start' => $start];
        if (!empty($end)) $params['end'] = $end;
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO meetings (title, date, notes, participants, created_by)
            VALUES (:title, :date, :notes, :participants, :created_by)
        ");
        $stmt->execute([
            'title' => $data['title'],
            'date' => $data['date'],
            'notes' => $data['notes'],
            'participants' => $data['participants'],
            'created_by' => $data['created_by']
        ]);
        return $db->lastInsertId();
    }
}
