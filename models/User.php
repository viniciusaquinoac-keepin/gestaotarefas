<?php
require_once __DIR__ . '/../database.php';

class User {
    public static function authenticate($email, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND active = 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id")
               ->execute(['id' => $user['id']]);
            
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, email, role, department, avatar_color, active, last_login FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
