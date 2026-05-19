<?php
//Checkout php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../model/checkout_model.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}


if (isset($_GET['chk_place_order'])) {
    $user_id        = (int)$_SESSION['user_id'];
    $address        = isset($_POST['address'])        ? trim($_POST['address'])        : '';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

    if (empty($address)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Address is required.']);
        exit;
    }

    if (empty($payment_method)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Payment method is required.']);
        exit;
    }

    $cartResult = getCartItemsForCheckout($conn, $user_id);

    $cartItems = [];
    while ($row = mysqli_fetch_assoc($cartResult)) {
        $cartItems[] = $row;
    }

    if (empty($cartItems)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit;
    }

    $total_amount = 0;
    foreach ($cartItems as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    $order_id = insertOrder($conn, $user_id, $total_amount, $payment_method);

    insertOrderItems($conn, $order_id, $cartItems);

    insertPayment($conn, $order_id, $total_amount, $payment_method);

    clearCart($conn, $user_id);

    updateUserAddress($conn, $user_id, $address);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    exit;
}


if (isset($_GET['chk_fetch_orders'])) {
    $user_id = (int)$_SESSION['user_id'];

    $result = getOrderHistory($conn, $user_id);
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
?>