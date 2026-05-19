<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../model/login_model.php';


//control.php
if (isset($_POST['login'])) {
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    
    $user = getUserByEmailAndPass($conn, $email, $password);

    if (!$user) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }

    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['role']    = $user['role']; 

    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'role'    => strtolower($user['role'])
    ]);
    exit;
}
?>