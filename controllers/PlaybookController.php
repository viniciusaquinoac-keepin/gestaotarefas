<?php
require_once __DIR__ . '/../auth.php';

class PlaybookController {
    public function index() {
        requireAuth();
        $currentUser = getCurrentUser();
        
        $abaAtiva = $_GET['aba'] ?? 'autoitec';
        if (!in_array($abaAtiva, ['autoitec', 'keepin'])) {
            $abaAtiva = 'autoitec';
        }

        require_once __DIR__ . '/../views/layout/header.php';
        require_once __DIR__ . '/../views/playbook.php';
        require_once __DIR__ . '/../views/layout/footer.php';
    }
}
