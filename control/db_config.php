<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "online_book_store");

if (!$conn) {
    die(json_encode(["error" => mysqli_connect_error()]));
}
?>