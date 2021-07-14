<?php
session_start();

if(isset($_SESSION['type']) && isset($_SESSION['user_username'])){
    $subscription = json_decode(file_get_contents('php://input'), true);

    if (!isset($subscription['endpoint'])) {
        echo 'bad';
        exit();
    }

    $method = $_SERVER['REQUEST_METHOD'];

    require './dbh.inc.php';

    switch ($method) {
        case 'POST':

            if (!isset($subscription['publicKey']) || !isset($subscription['authToken'])) {
                echo 'bad';
                exit();
            }

            $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
            $endpoint = mysqli_real_escape_string($conn, $subscription['endpoint']);
            $publicKey = mysqli_real_escape_string($conn, $subscription['publicKey']);
            $authToken = mysqli_real_escape_string($conn, $subscription['authToken']);

            if(mysqli_query($conn, "SELECT * FROM notif_subs WHERE subscription_endpoint='$endpoint' AND subscription_username='$username'")->num_rows < 1)
                mysqli_query($conn, "INSERT INTO notif_subs (subscription_username, subscription_endpoint, subscription_publickey, subscription_authtoken) VALUES ('$username', '$endpoint', '$publicKey', '$authToken')");
            echo 'ok';

            exit();
        break;
        case 'PUT':

            if (!isset($subscription['publicKey']) || !isset($subscription['authToken'])) {
                echo 'bad';
                exit();
            }

            $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
            $endpoint = mysqli_real_escape_string($conn, $subscription['endpoint']);
            $publicKey = mysqli_real_escape_string($conn, $subscription['publicKey']);
            $authToken = mysqli_real_escape_string($conn, $subscription['authToken']);

            if(mysqli_query($conn, "SELECT * FROM notif_subs WHERE subscription_endpoint='$endpoint' AND subscription_username='$username'")->num_rows > 0)
                mysqli_query($conn, "UPDATE notif_subs SET subscription_publickey='$publicKey', subscription_authtoken='$authToken' WHERE subscription_endpoint='$endpoint'");
            else
                mysqli_query($conn, "INSERT INTO notif_subs (subscription_username, subscription_endpoint, subscription_publickey, subscription_authtoken) VALUES ('$username', '$endpoint', '$publicKey', '$authToken')");
            
            echo 'ok';
            exit();
        break;
        case 'DELETE':
            $endpoint = mysqli_real_escape_string($conn, $subscription['endpoint']);

            mysqli_query($conn, "DELETE FROM notif_subs WHERE subscription_endpoint='$endpoint'");
            echo 'ok';
            exit();
        break;
        default:
            echo 'bad';
            exit();
        return;
    }

    echo 'ok';
    exit();
}
else {
    echo 'bad';
    exit();
}