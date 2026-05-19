<?php


function allBooks($conn) {
    $qry = "SELECT books.*, categories.name AS category_name 
            FROM books 
            LEFT JOIN categories ON books.category_id = categories.id
            ORDER BY books.id DESC";
            
    return mysqli_query($conn, $qry);
}


function searchBook($conn, $query, $filter) {
    
    $query = mysqli_real_escape_string($conn, $query);
    $filter = mysqli_real_escape_string($conn, $filter);
    
    if ($filter === 'category') {
        $qry = "SELECT books.*, categories.name AS category_name 
                FROM books 
                LEFT JOIN categories ON books.category_id = categories.id 
                WHERE categories.name LIKE '%$query%'";
    } else if ($filter === 'author') {
        $qry = "SELECT books.*, categories.name AS category_name 
                FROM books 
                LEFT JOIN categories ON books.category_id = categories.id 
                WHERE books.author LIKE '%$query%'";
    } else {
        $qry = "SELECT books.*, categories.name AS category_name 
                FROM books 
                LEFT JOIN categories ON books.category_id = categories.id 
                WHERE books.title LIKE '$query%'";
    }
    
    return mysqli_query($conn, $qry);
}


function checkStock($conn, $book_id) {
    $book_id = (int)$book_id;
    $result = mysqli_query($conn, "SELECT stock FROM books WHERE id = $book_id");
    return mysqli_fetch_assoc($result);
}


function getCartItems($conn, $user_id) {
    $user_id = (int)$user_id;

    $qry = "SELECT cart.id, cart.quantity, books.title, books.price 
            FROM cart 
            INNER JOIN books ON cart.book_id = books.id 
            WHERE cart.user_id = $user_id";

    return mysqli_query($conn, $qry);
}





function checkCartItem($conn, $user_id, $book_id) {
    $user_id = (int)$user_id;
    $book_id = (int)$book_id;
    $result = mysqli_query($conn, "SELECT id, quantity FROM cart WHERE user_id = $user_id AND book_id = $book_id");
    return mysqli_fetch_assoc($result);
}


function addToCart($conn, $user_id, $book_id, $existing) {
    $user_id = (int)$user_id;
    $book_id = (int)$book_id;

    if ($existing) {
        $newQty = $existing['quantity'] + 1;
        mysqli_query($conn, "UPDATE cart SET quantity = $newQty WHERE id = " . (int)$existing['id']);
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, book_id, quantity) VALUES ($user_id, $book_id, 1)");
    }
}


function getCartRow($conn, $cart_id, $user_id) {
    $cart_id = (int)$cart_id;
    $user_id = (int)$user_id;
    $result = mysqli_query($conn, "SELECT * FROM cart WHERE id = $cart_id AND user_id = $user_id");
    return mysqli_fetch_assoc($result);
}


function updateCartQuantity($conn, $cart_id, $action, $currentQty) {
    $cart_id = (int)$cart_id;

    if ($action === 'plus') {
        $newQty = $currentQty + 1;
        mysqli_query($conn, "UPDATE cart SET quantity = $newQty WHERE id = $cart_id");
    } else if ($action === 'minus') {
        if ($currentQty <= 1) {
            mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id");
        } else {
            $newQty = $currentQty - 1;
            mysqli_query($conn, "UPDATE cart SET quantity = $newQty WHERE id = $cart_id");
        }
    }
}


function removeCartItem($conn, $cart_id, $user_id) {
    $cart_id = (int)$cart_id;
    $user_id = (int)$user_id;
    mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
}

?>