<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function login() {
        if (isLoggedIn()) {
            header("Location: " . BASE_URL . "/?page=dashboard");
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::authenticate($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                header("Location: " . BASE_URL . "/?page=dashboard");
                exit;
            } else {
                $error = "Email ou senha incorretos.";
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "/?page=auth&action=login");
        exit;
    }
}
