<?php
require 'database.php';
require 'models/TaskHistory.php';

try {
    $db = Database::getConnection();
    echo "Inserting test comment...\n";
    TaskHistory::add(14, 'comment', null, null, 'Test comment', 1, null);
    $lastId = $db->lastInsertId();
    echo "Inserted ID: $lastId\n";
    
    echo "Deleting comment...\n";
    $result = TaskHistory::delete($lastId);
    echo "Delete result: " . ($result ? 'true' : 'false') . "\n";
    
    $stmt = $db->prepare("SELECT * FROM task_history WHERE id = ?");
    $stmt->execute([$lastId]);
    $row = $stmt->fetch();
    echo "Row exists? " . ($row ? 'yes' : 'no') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
