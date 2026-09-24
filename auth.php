<?php
// Configura o tempo de vida da sessão para 30 dias (em segundos)
$sessionTimeout = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionTimeout);
ini_set('session.cookie_lifetime', $sessionTimeout);

$sessionPath = __DIR__ . '/sessions';
if (!file_exists($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
if (file_exists($sessionPath) && is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
}

session_set_cookie_params($sessionTimeout);
session_start();
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/index.php?page=auth&action=login");
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        die("Acesso negado. Apenas administradores podem acessar esta página.");
    }
}

function getGlobalFilterStart() {
    if (isset($_GET['filter_start'])) {
        $_SESSION['filter_start'] = $_GET['filter_start'];
    }
    if (!isset($_SESSION['filter_start'])) {
        $_SESSION['filter_start'] = date('Y-m-d', strtotime('-2 months'));
    }
    return $_SESSION['filter_start'];
}

function getGlobalFilterEnd() {
    if (isset($_GET['filter_end'])) {
        $_SESSION['filter_end'] = $_GET['filter_end'];
    }
    if (!isset($_SESSION['filter_end'])) {
        $_SESSION['filter_end'] = ''; // Vazio = sem limite superior
    }
    return $_SESSION['filter_end'];
}

