<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database.php';
require 'models/TaskHistory.php';

try {
    echo "Adding comment...\n";
    TaskHistory::add(1, 'comment', null, null, 'Test comment', 1, null);
    echo "Success!\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
