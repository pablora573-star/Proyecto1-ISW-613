<?php
include('../common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    //check user in the database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (md5($password) === $row['password']){
          session_start();
          $_SESSION['username'] = $row['username'];
          $_SESSION['firstname'] = $row['name'];
          header("Location: /pages/dashboard.php");
          exit();
        } else {
          header("Location: /index.php");
        }
    } else {
        header("Location: /index.php");
    }
    mysqli_close($conn);

} else {
    header("Location: /index.php");
    exit();
}