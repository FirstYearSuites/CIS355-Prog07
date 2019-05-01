<?php
session_start();
require "database.php";
if ($_GET)
    $errorMessage = $_GET["errorMessage"];
else
    $errorMessage='';
if ($_POST){
    // Get the username and password from the post.
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password = MD5($password); //encrypts password using MD5 encryption
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // See if this username / password combination exists in the database.
    $sql = "SELECT * FROM customers WHERE clubName='$username' AND password_hash ='$password' LIMIT 1"; //query the database for user-entered username and password
    $q = $pdo -> prepare($sql);
    $q -> execute(array());
    $data = $q->fetch(PDO::FETCH_ASSOC);
    // returns data then we have found a successful query match
    if ($data) {
        $_SESSION["username"] = $username;
        // goes to the listing page
        header("Location: customer.php");
    } else // attempt login again, failed login
        header("Location: login.php?errorMessage=Invalid Username or Password!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset='UTF-8'>
    <script src=\"https://code.jquery.com/jquery-3.3.1.min.js\"
            integrity=\"sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=\"
            crossorigin=\"anonymous\"></script>
    <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' rel='stylesheet'>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js'></script>
    <style>label {width: 5em;}</style>
</head>

<div class="container">
    <h1>Log In</h1>
    <form class="form-horizontal" action="login.php" method="post">
        <input name="username" type="text" placeholder="me@email.com" required>
        <input name="password" type="password" placeholder="password" required>
        <button type="submit" class="btn btn-success">Sign In</button>
        <a href="createAccount.php" class="btn btn-info">Join</a>
        <a href="logout.php" class="btn btn-info">Logout</a>

        <?php
        // Displays an error message if there is one.
        if ($errorMessage) {
            echo "<p class=\"alert alert-danger\" role=\"alert\">$errorMessage</p>";
        }
        ?>
    </form>
</div>
</html>