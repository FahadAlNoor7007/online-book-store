<?php


function getCartItemsForCheckout($conn, $user_id) {
    $user_id = (int)$user_id;

    $qry = "SELECT cart.*, books.price 
            FROM cart 
            INNER JOIN books ON cart.book_id = books.id 
            WHERE cart.user_id = $user_id";

    return mysqli_query($conn, $qry);
}


function insertOrder($conn, $user_id, $total_amount, $payment_method) {
    $user_id        = (int)$user_id;
    $total_amount   = (float)$total_amount;
    $payment_method = mysqli_real_escape_string($conn, $payment_method);

    mysqli_query($conn, 
        "INSERT INTO orders (user_id, total_amount, status, payment_method) 
         VALUES ($user_id, $total_amount, 'pending', '$payment_method')"
    );

    return mysqli_insert_id($conn);
}


function insertOrderItems($conn, $order_id, $cartItems) {
    $order_id = (int)$order_id;

    foreach ($cartItems as $item) {
        $book_id    = (int)$item['book_id'];
        $quantity   = (int)$item['quantity'];
        $unit_price = (float)$item['price'];

        mysqli_query($conn, 
            "INSERT INTO order_items (order_id, book_id, quantity, unit_price) 
             VALUES ($order_id, $book_id, $quantity, $unit_price)"
        );
    }
}


function insertPayment($conn, $order_id, $total_amount, $payment_method) {
    $order_id       = (int)$order_id;
    $total_amount   = (float)$total_amount;
    $payment_method = mysqli_real_escape_string($conn, $payment_method);
    $transaction_id = 'TXN' . time() . $order_id;

    mysqli_query($conn, 
        "INSERT INTO payments (order_id, amount, payment_method, transaction_id) 
         VALUES ($order_id, $total_amount, '$payment_method', '$transaction_id')"
    );
}


function clearCart($conn, $user_id) {
    $user_id = (int)$user_id;
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
}


function updateUserAddress($conn, $user_id, $address) {
    $user_id = (int)$user_id;
    $address = mysqli_real_escape_string($conn, $address);
    mysqli_query($conn, "UPDATE users SET address = '$address' WHERE id = $user_id");
}



function getOrderHistory($conn, $user_id) {
    $user_id = (int)$user_id;

    $qry = "SELECT 
                orders.id,
                orders.total_amount,
                orders.status,
                orders.payment_method,
                orders.order_date,
                GROUP_CONCAT(books.title SEPARATOR ', ') AS book_titles
            FROM orders
            INNER JOIN order_items ON orders.id = order_items.order_id
            INNER JOIN books ON order_items.book_id = books.id
            WHERE orders.user_id = $user_id
            GROUP BY orders.id
            ORDER BY orders.order_date DESC";

    return mysqli_query($conn, $qry);
}

?>