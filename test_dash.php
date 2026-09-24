<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user'] = ['role'=>'admin', 'department'=>'TI', 'name'=>'Admin'];
require 'config.php';
require 'database.php';
require 'auth.php';
require 'controllers/DashboardController.php';
$c = new DashboardController();
$c->index();
