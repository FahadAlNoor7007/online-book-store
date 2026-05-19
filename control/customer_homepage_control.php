<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../model/customer_homepage_model.php';


if (isset($_GET['chk_fetch'])) {
    $result = allBooks($conn);
    $arr = [];
    
    if ($result) {
        foreach ($result as $rows) {
            array_push($arr, $rows);
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit; 
}


if (isset($_GET['chk_search'])) {
    
    $query = isset($_GET['query']) ? $_GET['query'] : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'title';

    $result = searchBook($conn, $query, $filter);
    $arr = [];

    if ($result) {
        foreach ($result as $rows) {
            array_push($arr, $rows);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}


if (isset($_GET['chk_add_cart'])) {
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;

    if ($book_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid book.']);
        exit;
    }

    $book = checkStock($conn, $book_id);

    if (!$book || $book['stock'] <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Out of stock.']);
        exit;
    }

    $existing = checkCartItem($conn, $user_id, $book_id);
    addToCart($conn, $user_id, $book_id, $existing);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}



if (isset($_GET['chk_fetch_cart'])) {
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

    $result = getCartItems($conn, $user_id);
    $arr = [];

    if ($result) {
        while ($rows = mysqli_fetch_assoc($result)) {
            array_push($arr, $rows);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}







if (isset($_GET['chk_update_cart'])) {
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $action  = isset($_POST['action'])  ? $_POST['action'] : '';

    if ($cart_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }



    $cartRow = getCartRow($conn, $cart_id, $user_id);

    if (!$cartRow) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    updateCartQuantity($conn, $cart_id, $action, $cartRow['quantity']);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}








if (isset($_GET['chk_remove_cart'])) {
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

    if ($cart_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    removeCartItem($conn, $cart_id, $user_id);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

?>