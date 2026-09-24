<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    public function index() {
        requireAdmin(); // Apenas admin pode acessar
        
        $db = Database::getConnection();
        $users = $db->query("SELECT * FROM users ORDER BY name ASC")->fetchAll();

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/users/index.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    public function create() {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $department = $_POST['department'];
            $role = $_POST['role'];
            
            // Gerar cor aleatória pastel para o avatar
            $colors = ['#f87171', '#fb923c', '#fbbf24', '#a3e635', '#4ade80', '#34d399', '#2dd4bf', '#38bdf8', '#818cf8', '#a78bfa', '#e879f9', '#f472b6'];
            $color = $colors[array_rand($colors)];

            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO users (name, email, password, role, department, avatar_color)
                VALUES (:name, :email, :password, :role, :department, :avatar_color)
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'department' => $department,
                'avatar_color' => $color
            ]);

            header('Location: ' . BASE_URL . '/?page=users');
            exit;
        }
    }
}
