<?php
require 'database.php';
require 'models/Task.php';
try {
    print_r(Task::getByStatus('todo'));
} catch (Exception $e) {
    echo $e->getMessage();
}
