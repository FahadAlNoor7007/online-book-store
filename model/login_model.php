<?php
//login.php
function getUserByEmailAndPass($conn, $email, $password) {
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1");
    return mysqli_fetch_assoc($result);
}

?>