<?php
require 'database.php';
$db = Database::getConnection();
try {
    $stmt = $db->query("PRAGMA table_info(task_history)");
    print_r($stmt->fetchAll());
} catch (Exception $e) {
    echo $e->getMessage();
}
