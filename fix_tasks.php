<?php
require 'config.php';
require 'database.php';
$db = Database::getConnection();
$db->exec("UPDATE tasks SET completed_at = updated_at WHERE status = 'done' AND completed_at IS NULL");
echo "ok";
